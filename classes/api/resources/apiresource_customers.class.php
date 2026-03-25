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
 * Customers API resource controller
 */
class ApiResource_Customers extends ApiResource
{
    protected $_resourceName = 'customers';

    /**
     * Fields that must NEVER be exposed via API
     */
    private $_sensitiveFields = array('password', 'salt', 'verify', 'new_password');

    private $_writableFields = array(
        'first_name', 'last_name', 'email', 'phone',
        'mobile', 'type', 'status', 'country',
        'language', 'currency', 'notes',
    );

    /**
     * GET /customers
     */
    public function listing()
    {
        $this->_requireRead();
        $pagination = $this->_getPagination();

        $where = array();
        if (isset($_GET['status'])) {
            $where['status'] = (int)$_GET['status'];
        }
        if (isset($_GET['type'])) {
            $where['type'] = (int)$_GET['type'];
        }
        if (isset($_GET['email'])) {
            $where['email'] = trim($_GET['email']);
        }

        $search = $this->_buildSearch(array('first_name', 'last_name', 'email'));
        $where = array_merge($where, $search);

        $sort = $this->_getSort(array('customer_id', 'first_name', 'last_name', 'email', 'registered'), 'customer_id');

        $total = $this->_db->count('CubeCart_customer', 'customer_id', $where);
        $customers = $this->_db->select('CubeCart_customer', false, $where, $sort, $pagination['per_page'], $pagination['page']);

        $data = array();
        if ($customers) {
            foreach ($customers as $c) {
                $data[] = $this->_formatCustomer($c);
            }
        }

        ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], (int)$total);
    }

    /**
     * GET /customers/{id}
     */
    public function get($id)
    {
        $this->_requireRead();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid customer ID', 'BAD_REQUEST', 400);
        }

        $result = $this->_db->select('CubeCart_customer', false, array('customer_id' => (int)$id));
        if (!$result) {
            ApiResponse::error('Customer not found', 'NOT_FOUND', 404);
        }

        $customer = $this->_formatCustomer($result[0]);

        // Include address count
        $customer['address_count'] = (int)$this->_db->count('CubeCart_addressbook', 'address_id', array('customer_id' => (int)$id));

        // Include order count
        $customer['order_count'] = (int)$this->_db->count('CubeCart_order_summary', 'cart_order_id', array('customer_id' => (int)$id));

        ApiResponse::success($this->_applySparseFields($customer));
    }

    /**
     * POST /customers
     */
    public function create()
    {
        $this->_requireWrite();
        $data = $this->_getRequestBody();
        $this->_validateRequired($data, array('first_name', 'last_name', 'email'));

        // Check duplicate email
        $existing = $this->_db->select('CubeCart_customer', array('customer_id'), array('email' => $data['email']));
        if ($existing) {
            ApiResponse::error('A customer with this email already exists', 'CONFLICT', 409);
        }

        $record = $this->_filterFields($data, $this->_writableFields);
        $record['registered'] = time();

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $record['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            $record['salt']     = '';
        }

        $customerId = $this->_db->insert('CubeCart_customer', $record);
        if (!$customerId) {
            ApiResponse::error('Failed to create customer', 'INTERNAL_ERROR', 500);
        }

        $result = $this->_db->select('CubeCart_customer', false, array('customer_id' => $customerId));
        ApiResponse::created($this->_formatCustomer($result[0]));
    }

    /**
     * PUT /customers/{id}
     */
    public function update($id)
    {
        $this->_requireWrite();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid customer ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_customer', array('customer_id'), array('customer_id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Customer not found', 'NOT_FOUND', 404);
        }

        $data   = $this->_getRequestBody();
        $record = $this->_filterFields($data, $this->_writableFields);

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $record['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            $record['salt']     = '';
        }

        if (empty($record)) {
            ApiResponse::error('No valid fields to update', 'BAD_REQUEST', 400);
        }

        $this->_db->update('CubeCart_customer', $record, array('customer_id' => (int)$id));

        $result = $this->_db->select('CubeCart_customer', false, array('customer_id' => (int)$id));
        ApiResponse::success($this->_formatCustomer($result[0]));
    }

    /**
     * DELETE /customers/{id} - anonymize (GDPR compliant)
     */
    public function delete($id)
    {
        $this->_requireDelete();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid customer ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_customer', array('customer_id'), array('customer_id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Customer not found', 'NOT_FOUND', 404);
        }

        // Anonymize rather than delete (GDPR)
        $anon = array(
            'first_name' => 'Deleted',
            'last_name'  => 'Customer',
            'email'      => 'deleted_' . (int)$id . '@anonymized.invalid',
            'phone'      => '',
            'mobile'     => '',
            'password'   => '',
            'salt'       => '',
            'status'     => 0,
        );
        $this->_db->update('CubeCart_customer', $anon, array('customer_id' => (int)$id));

        // Anonymize addresses
        $this->_db->delete('CubeCart_addressbook', array('customer_id' => (int)$id));

        ApiResponse::noContent();
    }

    /**
     * Sub-resources: addresses, orders
     */
    public function subList($id, $subResource)
    {
        $this->_requireRead();
        if (!is_numeric($id)) {
            ApiResponse::error('Invalid customer ID', 'BAD_REQUEST', 400);
        }

        switch ($subResource) {
            case 'addresses':
                $addrs = $this->_db->select('CubeCart_addressbook', false, array('customer_id' => (int)$id));
                ApiResponse::success($addrs ?: array());
                return;
            case 'orders':
                $pagination = $this->_getPagination();
                $total  = $this->_db->count('CubeCart_order_summary', 'cart_order_id', array('customer_id' => (int)$id));
                $orders = $this->_db->select('CubeCart_order_summary', false, array('customer_id' => (int)$id), array('order_date' => 'DESC'), $pagination['per_page'], $pagination['page']);
                $data = array();
                if ($orders) {
                    foreach ($orders as $o) {
                        unset($o['password']);
                        $data[] = $o;
                    }
                }
                ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], (int)$total);
                return;
        }
        parent::subList($id, $subResource);
    }

    public function subCreate($id, $subResource)
    {
        $this->_requireWrite();
        if (!is_numeric($id)) {
            ApiResponse::error('Invalid customer ID', 'BAD_REQUEST', 400);
        }

        switch ($subResource) {
            case 'addresses':
                $data = $this->_getRequestBody();
                $this->_validateRequired($data, array('line1', 'town', 'postcode', 'country'));
                $addrFields = array(
                    'first_name', 'last_name', 'company_name', 'description',
                    'line1', 'line2', 'town', 'state', 'postcode', 'country',
                    'billing', 'default',
                );
                $record = $this->_filterFields($data, $addrFields);
                $record['customer_id'] = (int)$id;
                $addrId = $this->_db->insert('CubeCart_addressbook', $record);
                $record['address_id'] = (int)$addrId;
                ApiResponse::created($record);
                return;
        }
        parent::subCreate($id, $subResource);
    }

    public function subUpdate($id, $subResource, $subId)
    {
        $this->_requireWrite();
        if (!is_numeric($id)) {
            ApiResponse::error('Invalid customer ID', 'BAD_REQUEST', 400);
        }

        switch ($subResource) {
            case 'addresses':
                if (!$subId || !is_numeric($subId)) {
                    ApiResponse::error('Address ID required', 'BAD_REQUEST', 400);
                }
                $data = $this->_getRequestBody();
                $addrFields = array(
                    'first_name', 'last_name', 'company_name', 'description',
                    'line1', 'line2', 'town', 'state', 'postcode', 'country',
                    'billing', 'default',
                );
                $record = $this->_filterFields($data, $addrFields);
                if (empty($record)) {
                    ApiResponse::error('No valid fields to update', 'BAD_REQUEST', 400);
                }
                $this->_db->update('CubeCart_addressbook', $record, array('address_id' => (int)$subId, 'customer_id' => (int)$id));
                $result = $this->_db->select('CubeCart_addressbook', false, array('address_id' => (int)$subId));
                ApiResponse::success($result ? $result[0] : array());
                return;
        }
        parent::subUpdate($id, $subResource, $subId);
    }

    // ======= Private helpers =======

    private function _formatCustomer($customer)
    {
        // Strip sensitive fields
        foreach ($this->_sensitiveFields as $f) {
            unset($customer[$f]);
        }

        $intFields = array('customer_id', 'status', 'type', 'country');
        foreach ($intFields as $f) {
            if (isset($customer[$f])) {
                $customer[$f] = (int)$customer[$f];
            }
        }
        return $customer;
    }
}
