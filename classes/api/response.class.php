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
 * API Response handler - standardized JSON envelope
 */
class ApiResponse
{
    /**
     * Send a success response
     */
    public static function success($data = null, $meta = array(), $httpStatus = 200)
    {
        http_response_code($httpStatus);
        $response = array('success' => true);
        if ($data !== null) {
            $response['data'] = $data;
        }
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send a paginated success response
     */
    public static function paginated($data, $page, $perPage, $total)
    {
        $totalPages = ($perPage > 0) ? (int)ceil($total / $perPage) : 1;
        $meta = array(
            'page'        => (int)$page,
            'per_page'    => (int)$perPage,
            'total'       => (int)$total,
            'total_pages' => $totalPages,
        );
        self::success($data, $meta);
    }

    /**
     * Send an error response
     */
    public static function error($message, $code = 'ERROR', $httpStatus = 400)
    {
        http_response_code($httpStatus);
        $response = array(
            'success' => false,
            'error'   => array(
                'code'    => $code,
                'message' => $message,
                'status'  => $httpStatus,
            ),
        );
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send 201 Created response
     */
    public static function created($data)
    {
        self::success($data, array(), 201);
    }

    /**
     * Send 204 No Content response
     */
    public static function noContent()
    {
        http_response_code(204);
        exit;
    }
}
