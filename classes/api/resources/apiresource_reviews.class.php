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
 * Reviews API resource controller
 */
class ApiResource_Reviews extends ApiResource
{
    protected $_resourceName = 'reviews';

    /**
     * GET /reviews
     */
    public function listing()
    {
        $this->_requireRead();
        $pagination = $this->_getPagination();

        $where = array();
        if (isset($_GET['product_id'])) {
            $where['product_id'] = (int)$_GET['product_id'];
        }
        if (isset($_GET['approved'])) {
            $where['approved'] = (int)$_GET['approved'];
        }

        $sort = $this->_getSort(array('id', 'time', 'rating', 'product_id', 'approved'), 'time');
        if (!$sort) $sort = array('time' => 'DESC');

        $total = $this->_db->count('CubeCart_reviews', 'id', $where);
        $reviews = $this->_db->select('CubeCart_reviews', false, $where, $sort, $pagination['per_page'], $pagination['page']);

        $data = array();
        if ($reviews) {
            foreach ($reviews as $r) {
                $data[] = $this->_formatReview($r);
            }
        }

        ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], (int)$total);
    }

    /**
     * GET /reviews/{id}
     */
    public function get($id)
    {
        $this->_requireRead();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid review ID', 'BAD_REQUEST', 400);
        }

        $result = $this->_db->select('CubeCart_reviews', false, array('id' => (int)$id));
        if (!$result) {
            ApiResponse::error('Review not found', 'NOT_FOUND', 404);
        }

        ApiResponse::success($this->_formatReview($result[0]));
    }

    /**
     * PUT /reviews/{id} - approve or edit review
     */
    public function update($id)
    {
        $this->_requireWrite();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid review ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_reviews', false, array('id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Review not found', 'NOT_FOUND', 404);
        }

        $data = $this->_getRequestBody();
        $allowed = array('approved', 'rating', 'title', 'review', 'name', 'email');
        $record = $this->_filterFields($data, $allowed);

        if (empty($record)) {
            ApiResponse::error('No valid fields to update', 'BAD_REQUEST', 400);
        }

        $this->_db->update('CubeCart_reviews', $record, array('id' => (int)$id));

        $result = $this->_db->select('CubeCart_reviews', false, array('id' => (int)$id));
        ApiResponse::success($this->_formatReview($result[0]));
    }

    /**
     * DELETE /reviews/{id}
     */
    public function delete($id)
    {
        $this->_requireDelete();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid review ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_reviews', false, array('id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Review not found', 'NOT_FOUND', 404);
        }

        $this->_db->delete('CubeCart_reviews', array('id' => (int)$id));
        ApiResponse::noContent();
    }

    private function _formatReview($review)
    {
        $intFields = array('id', 'product_id', 'rating', 'approved');
        foreach ($intFields as $f) {
            if (isset($review[$f])) {
                $review[$f] = (int)$review[$f];
            }
        }
        return $review;
    }
}
