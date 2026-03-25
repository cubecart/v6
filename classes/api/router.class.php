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
 * API Router - parses request method + URL path and dispatches to resource controllers
 */
class ApiRouter
{
    private $_method;
    private $_path;
    private $_segments = array();
    private $_auth;
    private $_rateLimiter;
    private $_startTime;

    /**
     * Valid API resources mapped to class names
     */
    private $_resources = array(
        'products'   => 'ApiResource_Products',
        'categories' => 'ApiResource_Categories',
        'orders'     => 'ApiResource_Orders',
        'customers'  => 'ApiResource_Customers',
        'coupons'    => 'ApiResource_Coupons',
        'shipping'   => 'ApiResource_Shipping',
        'tax'        => 'ApiResource_Tax',
        'settings'   => 'ApiResource_Settings',
        'reviews'    => 'ApiResource_Reviews',
        'files'      => 'ApiResource_Files',
    );

    public function __construct()
    {
        $this->_startTime = microtime(true);
        $this->_method = strtoupper($_SERVER['REQUEST_METHOD']);

        // Handle CORS preflight
        if ($this->_method === 'OPTIONS') {
            $this->_setCorsHeaders();
            http_response_code(204);
            exit;
        }

        // Parse the API path
        $path = isset($_GET['_api_path']) ? trim($_GET['_api_path'], '/') : '';
        $this->_segments = !empty($path) ? explode('/', $path) : array();
    }

    /**
     * Dispatch the request to the appropriate resource controller
     */
    public function dispatch()
    {
        $this->_setCorsHeaders();

        // Validate HTTP method
        $allowed = array('GET', 'POST', 'PUT', 'PATCH', 'DELETE');
        if (!in_array($this->_method, $allowed)) {
            ApiResponse::error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }

        // Check version prefix
        if (empty($this->_segments) || $this->_segments[0] !== 'v1') {
            ApiResponse::error('API version required. Use /api/v1/{resource}', 'VERSION_REQUIRED', 400);
        }

        // Authenticate
        $this->_auth = new ApiAuth();
        $this->_auth->authenticate();

        // Rate limit
        $this->_rateLimiter = new ApiRateLimiter($this->_auth->getRateLimit());
        $this->_rateLimiter->check($this->_auth->getKeyId());

        // Extract resource name
        $resource = isset($this->_segments[1]) ? strtolower($this->_segments[1]) : '';
        if (empty($resource) || !isset($this->_resources[$resource])) {
            $available = implode(', ', array_keys($this->_resources));
            ApiResponse::error("Unknown resource '$resource'. Available: $available", 'NOT_FOUND', 404);
        }

        // Load resource controller
        $className = $this->_resources[$resource];
        $classFile = CC_CLASSES_DIR . 'api/resources/' . strtolower($className) . '.class.php';
        if (!file_exists($classFile)) {
            ApiResponse::error("Resource '$resource' is not yet implemented", 'NOT_IMPLEMENTED', 501);
        }
        require_once $classFile;

        if (!class_exists($className)) {
            ApiResponse::error("Resource controller not found", 'INTERNAL_ERROR', 500);
        }

        $controller = new $className($this->_auth);

        // Parse ID and sub-resource
        $id          = isset($this->_segments[2]) ? $this->_segments[2] : null;
        $subResource = isset($this->_segments[3]) ? $this->_segments[3] : null;
        $subId       = isset($this->_segments[4]) ? $this->_segments[4] : null;

        // Register request logging as shutdown function so it runs even after exit()
        $router = $this;
        register_shutdown_function(function () use ($router, $resource, $id, $subResource) {
            $router->logRequest($resource, $id, $subResource);
        });

        // Dispatch based on method + path structure
        $this->_dispatchToController($controller, $id, $subResource, $subId);
    }

    /**
     * Route to the correct controller method
     */
    private function _dispatchToController($controller, $id, $subResource, $subId)
    {
        // Sub-resource routing
        if ($subResource !== null && $id !== null) {
            switch ($this->_method) {
                case 'GET':
                    if ($subId !== null) {
                        $controller->subGet($id, $subResource, $subId);
                    } else {
                        $controller->subList($id, $subResource);
                    }
                    return;
                case 'POST':
                    $controller->subCreate($id, $subResource);
                    return;
                case 'PUT':
                case 'PATCH':
                    if ($subId !== null) {
                        $controller->subUpdate($id, $subResource, $subId);
                    } else {
                        $controller->subUpdate($id, $subResource, null);
                    }
                    return;
                case 'DELETE':
                    $controller->subDelete($id, $subResource, $subId);
                    return;
            }
        }

        // Primary resource routing
        switch ($this->_method) {
            case 'GET':
                if ($id !== null) {
                    $controller->get($id);
                } else {
                    $controller->listing();
                }
                break;
            case 'POST':
                $controller->create();
                break;
            case 'PUT':
            case 'PATCH':
                if ($id === null) {
                    // Allow PUT to collection for batch updates (e.g. settings)
                    $controller->updateBatch();
                } else {
                    $controller->update($id);
                }
                break;
            case 'DELETE':
                if ($id === null) {
                    ApiResponse::error('Resource ID required for DELETE', 'BAD_REQUEST', 400);
                }
                $controller->delete($id);
                break;
        }
    }

    /**
     * Set CORS headers
     */
    private function _setCorsHeaders()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
        header('Access-Control-Max-Age: 86400');
    }

    /**
     * Log the API request (public for shutdown function access)
     */
    public function logRequest($resource, $id = null, $subResource = null)
    {
        $endpoint = '/api/v1/' . $resource;
        if ($id !== null) {
            $endpoint .= '/' . $id;
        }
        if ($subResource !== null) {
            $endpoint .= '/' . $subResource;
        }

        $responseMs = (int)((microtime(true) - $this->_startTime) * 1000);
        $db = Database::getInstance();
        $db->insert('CubeCart_api_log', array(
            'key_id'       => $this->_auth->getKeyId(),
            'method'       => $this->_method,
            'endpoint'     => substr($endpoint, 0, 255),
            'status_code'  => http_response_code(),
            'ip_address'   => $this->_auth->getClientIp(),
            'request_time' => time(),
            'response_ms'  => $responseMs,
        ));
    }
}
