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
 * Coupons API resource controller
 */
class ApiResource_Coupons extends ApiResource
{
    protected $_resourceName = 'coupons';

    private $_writableFields = array(
        'code', 'description', 'discount_price', 'discount_percent',
        'starts', 'expires', 'min_subtotal', 'allowed_uses', 'count',
        'product_id', 'manufacturer_id', 'category_id', 'shipping_id',
        'status', 'archived', 'shipping', 'subtotal',
        'coupon_per_customer', 'free_shipping', 'exclude_sale_items',
    );

    /**
     * GET /coupons
     */
    public function listing()
    {
        $this->_requireRead();
        $pagination = $this->_getPagination();

        $where = array();
        if (isset($_GET['status'])) {
            $where['status'] = (int)$_GET['status'];
        }

        $search = $this->_buildSearch(array('code', 'description'));
        $where = array_merge($where, $search);

        $sort = $this->_getSort(array('coupon_id', 'code', 'expires', 'status'), 'coupon_id');

        $total = $this->_db->count('CubeCart_coupons', 'coupon_id', $where);
        $coupons = $this->_db->select('CubeCart_coupons', false, $where, $sort, $pagination['per_page'], $pagination['page']);

        ApiResponse::paginated($coupons ?: array(), $pagination['page'], $pagination['per_page'], (int)$total);
    }

    /**
     * GET /coupons/{id}
     * Special: GET /coupons/validate/{code} handled via sub-resource
     */
    public function get($id)
    {
        $this->_requireRead();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid coupon ID', 'BAD_REQUEST', 400);
        }

        $result = $this->_db->select('CubeCart_coupons', false, array('coupon_id' => (int)$id));
        if (!$result) {
            ApiResponse::error('Coupon not found', 'NOT_FOUND', 404);
        }

        ApiResponse::success($result[0]);
    }

    /**
     * POST /coupons
     */
    public function create()
    {
        $this->_requireWrite();
        $data = $this->_getRequestBody();
        $this->_validateRequired($data, array('code'));

        $record = $this->_filterFields($data, $this->_writableFields);
        if (!isset($record['status'])) {
            $record['status'] = 1;
        }

        $couponId = $this->_db->insert('CubeCart_coupons', $record);
        if (!$couponId) {
            ApiResponse::error('Failed to create coupon', 'INTERNAL_ERROR', 500);
        }

        $result = $this->_db->select('CubeCart_coupons', false, array('coupon_id' => $couponId));
        ApiResponse::created($result[0]);
    }

    /**
     * PUT /coupons/{id}
     */
    public function update($id)
    {
        $this->_requireWrite();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid coupon ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_coupons', array('coupon_id'), array('coupon_id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Coupon not found', 'NOT_FOUND', 404);
        }

        $data   = $this->_getRequestBody();
        $record = $this->_filterFields($data, $this->_writableFields);
        if (empty($record)) {
            ApiResponse::error('No valid fields to update', 'BAD_REQUEST', 400);
        }

        $this->_db->update('CubeCart_coupons', $record, array('coupon_id' => (int)$id));

        $result = $this->_db->select('CubeCart_coupons', false, array('coupon_id' => (int)$id));
        ApiResponse::success($result[0]);
    }

    /**
     * DELETE /coupons/{id}
     */
    public function delete($id)
    {
        $this->_requireDelete();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid coupon ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_coupons', array('coupon_id'), array('coupon_id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Coupon not found', 'NOT_FOUND', 404);
        }

        $this->_db->delete('CubeCart_coupons', array('coupon_id' => (int)$id));
        ApiResponse::noContent();
    }

    /**
     * Sub-resource: GET /coupons/validate/{code}
     */
    public function subList($id, $subResource)
    {
        if ($id === 'validate' && !empty($subResource)) {
            $this->_requireRead();
            $this->_validateCode($subResource);
            return;
        }
        parent::subList($id, $subResource);
    }

    private function _validateCode($code)
    {
        $result = $this->_db->select('CubeCart_coupons', false, array('code' => $code));
        if (!$result) {
            ApiResponse::success(array('valid' => false, 'reason' => 'Coupon code not found'));
            return;
        }

        $coupon = $result[0];
        $reasons = array();

        if ($coupon['status'] != 1) {
            $reasons[] = 'Coupon is disabled';
        }
        if (!empty($coupon['expires']) && $coupon['expires'] !== '0000-00-00' && strtotime($coupon['expires']) < time()) {
            $reasons[] = 'Coupon has expired';
        }
        if ($coupon['allowed_uses'] > 0 && $coupon['count'] >= $coupon['allowed_uses']) {
            $reasons[] = 'Coupon usage limit reached';
        }

        $valid = empty($reasons);
        ApiResponse::success(array(
            'valid'   => $valid,
            'reason'  => $valid ? null : implode('; ', $reasons),
            'coupon'  => $coupon,
        ));
    }
}
