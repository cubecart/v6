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
 * Tax API resource controller
 */
class ApiResource_Tax extends ApiResource
{
    protected $_resourceName = 'tax';

    /**
     * GET /tax - overview of tax classes and rates
     */
    public function listing()
    {
        $this->_requireRead();

        $data = array(
            'classes' => $this->_getClasses(),
            'rates'   => $this->_getRates(),
        );

        ApiResponse::success($data);
    }

    /**
     * GET /tax/{id}
     * Routes: classes, rates, or specific rate ID
     */
    public function get($id)
    {
        $this->_requireRead();

        switch ($id) {
            case 'classes':
                ApiResponse::success($this->_getClasses());
                return;
            case 'rates':
                ApiResponse::success($this->_getRates());
                return;
        }

        // Treat as rate ID
        if (is_numeric($id)) {
            $result = $this->_db->select('CubeCart_tax_rates', false, array('id' => (int)$id));
            if (!$result) {
                ApiResponse::error('Tax rate not found', 'NOT_FOUND', 404);
            }
            // Get details
            $details = $this->_db->select('CubeCart_tax_details', false, array('id' => (int)$id));
            $rate = $result[0];
            $rate['details'] = $details ?: array();
            ApiResponse::success($rate);
            return;
        }

        ApiResponse::error('Invalid tax resource. Use /tax/classes or /tax/rates', 'BAD_REQUEST', 400);
    }

    /**
     * POST /tax - create rate
     */
    public function create()
    {
        $this->_requireWrite();
        $data = $this->_getRequestBody();
        $this->_validateRequired($data, array('name'));

        $rateFields = array('name', 'tax_id', 'type_id', 'status');
        $record = $this->_filterFields($data, $rateFields);

        $rateId = $this->_db->insert('CubeCart_tax_rates', $record);
        if (!$rateId) {
            ApiResponse::error('Failed to create tax rate', 'INTERNAL_ERROR', 500);
        }

        // Insert details if provided
        if (isset($data['details']) && is_array($data['details'])) {
            foreach ($data['details'] as $detail) {
                $detail['id'] = $rateId;
                $this->_db->insert('CubeCart_tax_details', $detail);
            }
        }

        $result = $this->_db->select('CubeCart_tax_rates', false, array('id' => $rateId));
        ApiResponse::created($result[0]);
    }

    /**
     * PUT /tax/{id}
     */
    public function update($id)
    {
        $this->_requireWrite();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid tax rate ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_tax_rates', false, array('id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Tax rate not found', 'NOT_FOUND', 404);
        }

        $data = $this->_getRequestBody();
        $rateFields = array('name', 'tax_id', 'type_id', 'status');
        $record = $this->_filterFields($data, $rateFields);

        if (!empty($record)) {
            $this->_db->update('CubeCart_tax_rates', $record, array('id' => (int)$id));
        }

        // Update details if provided
        if (isset($data['details']) && is_array($data['details'])) {
            $this->_db->delete('CubeCart_tax_details', array('id' => (int)$id));
            foreach ($data['details'] as $detail) {
                $detail['id'] = (int)$id;
                $this->_db->insert('CubeCart_tax_details', $detail);
            }
        }

        $result = $this->_db->select('CubeCart_tax_rates', false, array('id' => (int)$id));
        ApiResponse::success($result[0]);
    }

    /**
     * DELETE /tax/{id}
     */
    public function delete($id)
    {
        $this->_requireDelete();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid tax rate ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_tax_rates', false, array('id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Tax rate not found', 'NOT_FOUND', 404);
        }

        $this->_db->delete('CubeCart_tax_details', array('id' => (int)$id));
        $this->_db->delete('CubeCart_tax_rates', array('id' => (int)$id));
        ApiResponse::noContent();
    }

    /**
     * Sub-resource: POST /tax/calculate
     */
    public function subCreate($id, $subResource)
    {
        if ($id === 'calculate' || $subResource === 'calculate') {
            $this->_requireRead();
            $data = $this->_getRequestBody();
            $this->_validateRequired($data, array('price', 'tax_type'));

            $price   = (float)$data['price'];
            $taxType = (int)$data['tax_type'];

            $tax = Tax::getInstance();
            $result = $tax->productTax($price, $taxType);

            ApiResponse::success(array(
                'price_ex_tax'  => $price,
                'tax_amount'    => isset($result['amount']) ? (float)$result['amount'] : 0,
                'price_inc_tax' => $price + (isset($result['amount']) ? (float)$result['amount'] : 0),
                'tax_name'      => isset($result['name']) ? $result['name'] : '',
            ));
            return;
        }
        parent::subCreate($id, $subResource);
    }

    // ======= Private helpers =======

    private function _getClasses()
    {
        $classes = $this->_db->select('CubeCart_tax_class', false, false, array('tax_name' => 'ASC'));
        return $classes ?: array();
    }

    private function _getRates()
    {
        $rates = $this->_db->select('CubeCart_tax_rates', false, false, array('name' => 'ASC'));
        return $rates ?: array();
    }
}
