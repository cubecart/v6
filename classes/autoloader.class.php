<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
/**
 * Autoloader controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Autoloader
{

    /**
     * Contains all the paths to search for classes
     *
     * @var array of paths
     */
    private static $_paths = null;

    //=====[ Public ]=======================================

    /**
     * Append a path to the path array
     *
     * @param string $path
     */
    public static function appendPaths($path)
    {
        if (is_null(self::$_paths)) {
            self::$_paths = explode(CC_PS, ini_get('include_path'));
        }

        if (is_dir($path) && file_exists($path)) {
            self::$_paths[] = $path;
        }
    }

    /**
     * Autoload a class
     *
     * @param string $class
     * @return bool
     */
    public static function autoload($class)
    {
        
        //Don't double load
        if (class_exists($class)) {
            return true;
        }

        //If its a cache class use the cache method
        if ($class == 'Cache') {
            return self::autoload_cache();
        }

        //If its a DB class use the db method
        if ($class == 'Database') {
            return self::autoload_db();
        }

        //If its smarty we need to use the smarty loader
        if ($class == 'Smarty') {
            require_once CC_INCLUDES_DIR.'lib/smarty/Smarty.class.php';
            return true;
        }

        //Try classes first
        if (file_exists(CC_CLASSES_DIR.strtolower($class).'.class.php')) {
            include_once CC_CLASSES_DIR.strtolower($class).'.class.php';
            return true;
        }

        //Get the paths if needed
        if (!self::$_paths) {
            self::$_paths = explode(CC_PS, ini_get('include_path'));
        }

        //Loop through the include paths
        if (is_array(self::$_paths)) {
            foreach (self::$_paths as $path) {
                if (file_exists($path.'/'.strtolower($class).'.class.php')) {
                    include_once $path.'/'.strtolower($class).'.class.php';
                    return true;
                } elseif (file_exists($path.'/'.$class.'.php')) {
                    include_once $path.'/'.$class.'.php';
                    return true;
                }
            }
        }

        return true;
    }

    /**
     * Autoload the correct cache class
     *
     * @return bool
     */
    public static function autoload_cache()
    {
        global $glob;

        if (isset($glob['cache']) && !empty($glob['cache'])) {
            if (file_exists(CC_ROOT_DIR.'/classes/cache/'.$glob['cache'].'.class.php')) {
                include CC_ROOT_DIR.'/classes/cache/'.$glob['cache'].'.class.php';
                return true;
            }
        }

        //Default to file cache
        include CC_ROOT_DIR.'/classes/cache/file.class.php';
        return true;
    }

    /**
     * Autoload the correct DB class
     *
     * @return bool
     */
    public static function autoload_db()
    {
        global $glob;

        //If the configuration has a DB try to load that one first
        if (isset($glob['db']) && !empty($glob['db'])) {
            if (file_exists(CC_ROOT_DIR.'/classes/db/'.$glob['db'].'.class.php')) {
                include CC_ROOT_DIR.'/classes/db/'.$glob['db'].'.class.php';
                return true;
            }
        }

        //We will do mysqli if loaded
        if (function_exists('mysqli_connect')) {
            include CC_ROOT_DIR.'/classes/db/mysqli.class.php';
            return true;
        } else {
            include CC_ROOT_DIR.'/classes/db/mysql.class.php';
            return true;
        }

        return false;
    }

    

    /**
     * Register autoload function
     *
     * @param string/array $function or array(class, method)
     */
    public static function autoload_register($function = null)
    {
        if (!function_exists('spl_autoload_functions')) {
            trigger_error("!function_exists('spl_autoload_functions')", E_USER_ERROR);
        }

        //If there is not function we shouldn't be here
        if (!$function) {
            return ;
        }

        //If the function is really a class->method try to load that
        if (is_array($function)) {
            list($class, $method) = $function;
            if (!method_exists($class, $method)) {
                return ;
            }
        } elseif (!function_exists($function)) {
            return ;
        }

        //If the spl_autoload is implemented get the functions if not
        if (($callbacks = spl_autoload_functions()) === false) {
            //Register our function
            spl_autoload_register($function);
            return ;
        }
        //If there are no call backs we do not need to continue
        if (empty($callbacks)) {
            spl_autoload_register($function);
            return ;
        }

        //Lop through the call backs and unload them
        $key = array_keys($callbacks);
        $size = sizeof($key);
        for ($i = 0; $i < $size; ++$i) {
            spl_autoload_unregister($callbacks[$key[$i]]);
        }

        //Register our function
        spl_autoload_register($function);

        //Reload the previous functions
        for ($i = 0; $i < $size; ++$i) {
            spl_autoload_register($callbacks[$key[$i]]);
        }
    }

    /**
     * Reload all the paths from the include_path
     * Should not need to run unless you add more paths to the include_path
     */
    public static function reloadPaths()
    {
        self::$_paths = explode(CC_PS, ini_get('include_path'));
    }
}
