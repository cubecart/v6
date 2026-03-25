<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Orders API resource controller
 */
class ApiResource_Orders extends ApiResource
{
    protected $_resourceName = 'orders';

    /**
     * Order status constants (mirrored from order.class.php)
     */
    const ORDER_PENDING   = 1;
    const ORDER_PROCESS   = 2;
    const ORDER_COMPLETE  = 3;
    const ORDER_DECLINED  = 4;
    const ORDER_FAILED    = 5;
    const ORDER_REFUNDED  = 6;
    const ORDER_CANCELLED = 7;

    private $_sortableFields = array(
        'cart_order_id', 'order_date', 'status', 'total', 'customer_id', 'first_name', 'last_name',
    );

    /**
     * GET /orders
     */
    public function listing()
    {
        $this->_requireRead();
        $pagination = $this->_getPagination();

        $where = array();
        if (isset($_GET['status'])) {
            $where['status'] = (int)$_GET['status'];
        }
        if (isset($_GET['customer_id'])) {
            $where['customer_id'] = (int)$_GET['customer_id'];
        }

        // Date range filters - build as raw WHERE string parts
        $rawWhere = array();
        if (isset($_GET['date_from'])) {
            $ts = (int)strtotime($_GET['date_from']);
            if ($ts) $rawWhere[] = "`order_date` >= " . $ts;
        }
        if (isset($_GET['date_to'])) {
            $ts = (int)strtotime($_GET['date_to']);
            if ($ts) $rawWhere[] = "`order_date` <= " . $ts;
        }

        // Search by order ID or name
        $search = $this->_buildSearch(array('cart_order_id', 'first_name', 'last_name', 'email'));
        $where = array_merge($where, $search);

        // Merge raw date conditions into WHERE
        if (!empty($rawWhere)) {
            $where = !empty($where) ? $where : array();
            $where[] = implode(' AND ', $rawWhere);
        }

        $sort  = $this->_getSort($this->_sortableFields, 'order_date');
        if (!$sort) $sort = array('order_date' => 'DESC');

        $total = $this->_db->count('CubeCart_order_summary', 'cart_order_id', $where ?: false);
        $orders = $this->_db->select('CubeCart_order_summary', false, $where ?: false, $sort, $pagination['per_page'], $pagination['page']);

        $data = array();
        if ($orders) {
            foreach ($orders as $order) {
                $data[] = $this->_formatOrder($order);
            }
        }

        ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], (int)$total);
    }

    /**
     * GET /orders/{id}
     */
    public function get($id)
    {
        $this->_requireRead();

        $order = $this->_findOrder($id);
        $formatted = $this->_formatOrder($order, true);

        // Include line items
        $items = $this->_db->select('CubeCart_order_inventory', false, array('cart_order_id' => $order['cart_order_id']));
        $formatted['items'] = $items ?: array();

        // Include notes
        $notes = $this->_db->select('CubeCart_order_notes', false, array('cart_order_id' => $order['cart_order_id']), array('time' => 'DESC'));
        $formatted['notes'] = $notes ?: array();

        // Include tax breakdown
        $taxes = $this->_db->select('CubeCart_order_tax', false, array('cart_order_id' => $order['cart_order_id']));
        $formatted['taxes'] = $taxes ?: array();

        ApiResponse::success($this->_applySparseFields($formatted));
    }

    /**
     * POST /orders - create a new order
     */
    public function create()
    {
        $this->_requireWrite();
        $data = $this->_getRequestBody();
        $this->_validateRequired($data, array('first_name', 'last_name', 'email'));

        // Generate order ID
        $orderPrefix = $GLOBALS['config']->get('config', 'order_prefix') ?: '';
        $orderId = $orderPrefix . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        $record = array(
            'cart_order_id' => $orderId,
            'customer_id'   => isset($data['customer_id']) ? (int)$data['customer_id'] : 0,
            'status'        => isset($data['status']) ? (int)$data['status'] : self::ORDER_PENDING,
            'order_date'    => time(),
            'ip_address'    => $this->_auth->getClientIp(),
        );

        // Address and customer fields (delivery uses _d suffix in DB)
        $addressFields = array(
            'first_name', 'last_name', 'email', 'phone', 'mobile',
            'company_name', 'line1', 'line2', 'town', 'state', 'postcode', 'country',
            'first_name_d', 'last_name_d', 'company_name_d',
            'line1_d', 'line2_d', 'town_d', 'state_d', 'postcode_d', 'country_d',
        );
        foreach ($addressFields as $f) {
            if (isset($data[$f])) {
                $record[$f] = $data[$f];
            }
        }

        // Financial fields
        $moneyFields = array('subtotal', 'discount', 'shipping', 'total_tax', 'total', 'gateway');
        foreach ($moneyFields as $f) {
            if (isset($data[$f])) {
                $record[$f] = $data[$f];
            }
        }

        $this->_db->insert('CubeCart_order_summary', $record);

        // Insert line items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $item['cart_order_id'] = $orderId;
                $this->_db->insert('CubeCart_order_inventory', $item);
            }
        }

        // Return created order
        $result = $this->_db->select('CubeCart_order_summary', false, array('cart_order_id' => $orderId));
        ApiResponse::created($this->_formatOrder($result[0]));
    }

    /**
     * PUT /orders/{id}
     */
    public function update($id)
    {
        $this->_requireWrite();
        $order = $this->_findOrder($id);
        $data  = $this->_getRequestBody();

        $allowed = array(
            'status', 'ship_method', 'ship_product', 'ship_tracking',
            'subtotal', 'discount', 'shipping', 'total_tax', 'total',
            'notes', 'gateway',
        );
        $record = $this->_filterFields($data, $allowed);
        if (empty($record)) {
            ApiResponse::error('No valid fields to update', 'BAD_REQUEST', 400);
        }

        $this->_db->update('CubeCart_order_summary', $record, array('cart_order_id' => $order['cart_order_id']));

        $result = $this->_db->select('CubeCart_order_summary', false, array('cart_order_id' => $order['cart_order_id']));
        ApiResponse::success($this->_formatOrder($result[0]));
    }

    /**
     * Sub-resources: status, items, notes
     */
    public function subList($id, $subResource)
    {
        $this->_requireRead();
        $order = $this->_findOrder($id);

        switch ($subResource) {
            case 'items':
                $items = $this->_db->select('CubeCart_order_inventory', false, array('cart_order_id' => $order['cart_order_id']));
                ApiResponse::success($items ?: array());
                return;
            case 'notes':
                $notes = $this->_db->select('CubeCart_order_notes', false, array('cart_order_id' => $order['cart_order_id']), array('time' => 'DESC'));
                ApiResponse::success($notes ?: array());
                return;
        }
        parent::subList($id, $subResource);
    }

    public function subCreate($id, $subResource)
    {
        $this->_requireWrite();
        $order = $this->_findOrder($id);

        switch ($subResource) {
            case 'notes':
                $data = $this->_getRequestBody();
                $this->_validateRequired($data, array('content'));
                $noteId = $this->_db->insert('CubeCart_order_notes', array(
                    'cart_order_id' => $order['cart_order_id'],
                    'admin_id'      => $this->_auth->getAdminId(),
                    'content'       => $data['content'],
                    'time'          => time(),
                ));
                ApiResponse::created(array(
                    'note_id'       => (int)$noteId,
                    'cart_order_id' => $order['cart_order_id'],
                    'content'       => $data['content'],
                ));
                return;
        }
        parent::subCreate($id, $subResource);
    }

    public function subUpdate($id, $subResource, $subId)
    {
        $this->_requireWrite();
        $order = $this->_findOrder($id);

        switch ($subResource) {
            case 'status':
                $data = $this->_getRequestBody();
                $this->_validateRequired($data, array('status'));
                $newStatus = (int)$data['status'];
                if ($newStatus < 1 || $newStatus > 7) {
                    ApiResponse::error('Invalid status. Must be 1-7.', 'VALIDATION_ERROR', 422);
                }
                $this->_db->update('CubeCart_order_summary', array('status' => $newStatus), array('cart_order_id' => $order['cart_order_id']));

                // Add history note
                $this->_db->insert('CubeCart_order_notes', array(
                    'cart_order_id' => $order['cart_order_id'],
                    'admin_id'      => $this->_auth->getAdminId(),
                    'content'       => 'Status changed to ' . $newStatus . ' via API',
                    'time'          => time(),
                ));

                $result = $this->_db->select('CubeCart_order_summary', false, array('cart_order_id' => $order['cart_order_id']));
                ApiResponse::success($this->_formatOrder($result[0]));
                return;
        }
        parent::subUpdate($id, $subResource, $subId);
    }

    // ======= Private helpers =======

    /**
     * Find order by numeric ID or cart_order_id string
     */
    private function _findOrder($id)
    {
        if (is_numeric($id)) {
            $result = $this->_db->select('CubeCart_order_summary', false, array('id' => (int)$id));
        } else {
            $result = $this->_db->select('CubeCart_order_summary', false, array('cart_order_id' => $id));
        }

        if (!$result) {
            ApiResponse::error('Order not found', 'NOT_FOUND', 404);
        }
        return $result[0];
    }

    /**
     * Format order for API output
     */
    private function _formatOrder($order, $full = false)
    {
        $intFields = array('id', 'customer_id', 'status');
        foreach ($intFields as $f) {
            if (isset($order[$f])) {
                $order[$f] = (int)$order[$f];
            }
        }
        $floatFields = array('subtotal', 'discount', 'shipping', 'total_tax', 'total');
        foreach ($floatFields as $f) {
            if (isset($order[$f])) {
                $order[$f] = (float)$order[$f];
            }
        }

        // Add human-readable status label
        $statusLabels = array(
            1 => 'Pending',
            2 => 'Processing',
            3 => 'Complete',
            4 => 'Declined',
            5 => 'Failed',
            6 => 'Refunded',
            7 => 'Cancelled',
        );
        $order['status_label'] = isset($statusLabels[$order['status']]) ? $statusLabels[$order['status']] : 'Unknown';

        return $order;
    }
}
