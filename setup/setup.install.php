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
##### INSTALL #####
if (!isset($_SESSION['setup']['permissions'])) {
    $step = 4;
    $_SESSION['setup']['config_update'] = true;
    ## Stage 3: Permissions Check
    if (!file_exists($global_file)) {
        touch($global_file);
    }
    $targets = array(
        'backup/',
        'cache/',
        'cache/skin/',
        'files/',
        'images/',
        'images/cache/',
        'images/logos/',
        'images/source/',
        'includes/',
        'includes/extra/',
        'language/',
    );
    if (file_exists(CC_ROOT_DIR.'/includes/globals.inc.php')) {
        $targets[] = 'includes/global.inc.php';
    }
    if (file_exists(CC_ROOT_DIR.'/images/uploads')) {
        $targets[] = 'images/uploads/';
    }
    sort($targets);
    $permissions = true;
    foreach ($targets as $target) {
        $target = str_replace('/', '/', $target);
        $perm_status = true;
        if (!is_writable(CC_ROOT_DIR.'/'.$target)) {
            ## Attempt to chmod
            if (!chmod(CC_ROOT_DIR.'/'.$target, chmod_writable())) {
                $perm_status = false;
                $permissions = false;
                $errors[] = sprintf($strings['setup']['error_x_not_writable'], $target);
            }
        }
        $GLOBALS['smarty']->append('PERMISSIONS', array('name' => $target, 'status' => (bool)$perm_status));
    }
    if (!$permissions) {
        $proceed = false;
        $retry  = true;
    } else {
        // All permissions OK — skip the screen and advance straight to server details.
        // The user only sees this step when there's something to fix.
        $_SESSION['setup']['permissions'] = true;
        httpredir('index.php');
    }
    $GLOBALS['smarty']->assign('MODE_PERMS', true);
} else {
    // Stage 4: Server Details input
    $step = 5;
    if (!isset($_SESSION['setup']['global']) || !isset($_SESSION['setup']['progress'])) {
        if (isset($_POST['global']) && isset($_POST['admin'])) {
            // Empty dbhost defaults to localhost (matches the form placeholder).
            if (!isset($_POST['global']['dbhost']) || trim($_POST['global']['dbhost']) === '') {
                $_POST['global']['dbhost'] = 'localhost';
            }
            $GLOBALS['smarty']->assign('FORM', $_POST);
            // Validation
            $validated = true;
            $required = array('dbhost', 'dbusername', 'dbdatabase');
            foreach ($_POST['global'] as $key => $value) {
                if (in_array($key, $required) && empty($value)) {
                    $validated  = false;
                    unset($_POST[$key]);
                }
            }

            // Validate admin array
            $required = array('username', 'email', 'name', 'password');
            foreach ($_POST['admin'] as $key => $value) {
                if (in_array($key, $required) && empty($value)) {
                    $validated = false;
                    unset($_POST[$key]);
                }
            }
            $mysql_connect = false;
            // Connection Check
            if (function_exists('mysqli_connect')) {
                $dbport = !empty($_POST['global']['dbport']) ? $_POST['global']['dbport'] : ini_get('mysqli.default_port');
                $dbsocket = !empty($_POST['global']['dbsocket']) ? $_POST['global']['dbsocket'] : ini_get('mysqli.default_socket');
                
                try {
                    $connect_id = new mysqli($_POST['global']['dbhost'], $_POST['global']['dbusername'], $_POST['global']['dbpassword'], $_POST['global']['dbdatabase'], $dbport, $dbsocket);

                    mysqli_options($connect_id, MYSQLI_OPT_LOCAL_INFILE, true);

                    if ($connect_id->connect_error) {
                        $errors[] = $strings['setup']['error_db_incorrect_something'].' '.$connect_id->connect_error;
                        // Only clear the password — keep host/user/db/port/socket so the
                        // admin doesn't have to retype every field when only one is wrong.
                        unset($_POST['global']['dbpassword']);
                    } else {
                        $mysql_connect = true;
                        $connect_id->close();
                    }
                } catch (Exception $e) {
                    $errors[] = $strings['setup']['error_db_incorrect_something'].' '.$e->getMessage();
                    unset($_POST['global']['dbpassword']);
                }
            } else {
                $dbport = (isset($config['dbport']) && !empty($config['dbport'])) ? $config['dbport'] : ini_get('mysqli.default_port');

                if (!empty($dbport) && is_numeric($dbport)) {
                    $dbport = ':'.$dbport;
                }

                $connect = mysql_connect($_POST['global']['dbhost'].$dbport, $_POST['global']['dbusername'], $_POST['global']['dbpassword'], false);
                if ($connect) {
                    if (mysql_select_db($_POST['global']['dbdatabase'], $connect)) {
                        // Database is fine, so continue to next step
                        mysql_close($connect);
                        $mysql_connect = true;
                    } else {
                        // No such database
                        $errors['dbdatabase'] = $strings['setup']['error_db_doesnt_exist'].' '.mysql_error();
                        unset($_POST['global']['dbdatabase']);
                    }
                } else {
                    // Incorrect host/user/pass — only clear the password so the admin
                    // can fix the typo without retyping the whole connection string.
                    $errors[] = $strings['setup']['error_db_incorrect_something'];
                    unset($_POST['global']['dbpassword']);
                }
            }

            if ($validated && $mysql_connect) {
                $_SESSION['setup']['progress'] = true;
                $_SESSION['setup']['droptable'] = (isset($_POST['drop'])) ? true : false;

                $store_url     = preg_replace('#^http://#i', 'https://', CC_STORE_URL);
                $store_url     = rtrim(preg_replace('#/setup$#', '', $store_url), '/');
                $store_host    = parse_url($store_url, PHP_URL_HOST);
                $cookie_domain = ($store_host && strpos($store_host, '.') !== false)
                    ? '.'.preg_replace('#^www\.#i', '', $store_host)
                    : '';
                $global = array(
                    'installed'     => true,
                    'adminFolder'   => 'admin',
                    'adminFile'     => 'admin.php',
                    'cache'         => 'file',
                    'standard_url'  => $store_url,
                    'cookie_domain' => $cookie_domain,
                );
                $_SESSION['setup']['global'] = array_merge($_POST['global'], $global);
                $_SESSION['setup']['config'] = $_POST['config'];
                $salt = Password::getInstance()->createSalt();
                $_SESSION['setup']['admin']  = array_merge($_POST['admin'], array(
                        'order_notify' => 1,
                        'super_user' => 1,
                        'status'  => 1,
                        'salt'   => $salt,
                        'language'  => $_POST['config']['default_language'],
                        'password'  => Password::getInstance()->getSalted($_POST['admin']['password'], $salt),
                    ));
                httpredir('index.php');
            }
        }

        $currencies = array(
            'USD' => 'US Dollar',
            'GBP' => 'Pound Sterling',
            'EUR' => 'Euro',
            #####
            'AED' => 'United Arab Emirates Dirham',
            'AUD' => 'Australian Dollar',
            'BGN' => 'Bulgarian Lev',
            'BRL' => 'Brazilian Real',
            'CAD' => 'Canadian Dollar',
            'CHF' => 'Swiss Franc',
            'CNY' => 'Chinese Yuan',
            'CZK' => 'Czech Koruna',
            'DKK' => 'Danish Krone',
            'HKD' => 'Hong Kong Dollar',
            'HUF' => 'Hungarian Forint',
            'IDR' => 'Indonesian Rupiah',
            'ILS' => 'Israeli New Shekel',
            'INR' => 'Indian Rupee',
            'JPY' => 'Japanese Yen',
            'KRW' => 'South Korean Won',
            'MXN' => 'Mexican Peso',
            'MYR' => 'Malaysian Ringgit',
            'NOK' => 'Norwegian Krone',
            'NZD' => 'New Zealand Dollar',
            'PHP' => 'Philippine Peso',
            'PLN' => 'Polish Zloty',
            'RON' => 'Romanian Leu',
            'RUB' => 'Russian Ruble',
            'SAR' => 'Saudi Riyal',
            'SEK' => 'Swedish Krona',
            'SGD' => 'Singapore Dollar',
            'THB' => 'Thai Baht',
            'TRY' => 'Turkish Lira',
            'TWD' => 'New Taiwan Dollar',
            'UAH' => 'Ukrainian Hryvnia',
            'ZAR' => 'South African Rand'
        );
        $lang_default_currency = $language->getData('default_currency');
        foreach ($currencies as $code => $name) {
            if (isset($_POST['config']['default_currency'])) {
                $selected = ($_POST['config']['default_currency'] == $code) ? ' selected="selected"' : '';
            } elseif ($lang_default_currency) {
                $selected = ($lang_default_currency == $code) ? ' selected="selected"' : '';
            } else {
                $selected = '';
            }
            $list_currency[] = array('code' => $code, 'selected' => $selected, 'name' => (!empty($name))?$name:$code);
        }
        $GLOBALS['smarty']->assign('CURRENCIES', $list_currency);

        // If preset DB config exists, pass to template for hidden fields
        if (isset($existing_db) && is_array($existing_db)) {
            $GLOBALS['smarty']->assign('PRESET_DB', $existing_db);
        }
        if (isset($cc_email) && !empty($cc_email)) {
            $GLOBALS['smarty']->assign('PRESET_EMAIL', $cc_email);
        }
        if (isset($cc_name) && !empty($cc_name)) {
            $GLOBALS['smarty']->assign('PRESET_NAME', $cc_name);
        }
    } else {
        ## Stage 5: Actual installation
        ## Write config file
        $global_data = $_SESSION['setup']['global'];
        // Merge extra_config from cc_setup.php into global config
        if (isset($extra_config) && is_array($extra_config)) {
            $global_data = array_merge($global_data, $extra_config);
        }
        ksort($global_data);
        if (writeGlobalConfig($global_data, $global_file)) {
            ## Install database
            clearstatcache(true, $global_file);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($global_file, true);
            }
            include $global_file;
            $GLOBALS['config'] = $glob;
            $GLOBALS['db'] = Database::getInstance($GLOBALS['config']);

            $GLOBALS['db']->misc('ALTER DATABASE `'.$GLOBALS['config']['dbdatabase'].'` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');

            if ($_SESSION['setup']['droptable']) {
                $GLOBALS['db']->parseSchema(file_get_contents($setup_path.'db/install/table_drop.sql', false));
                unset($_SESSION['setup']['droptable']);
                # httpredir('index.php');
            }
            ## Create tables
            $GLOBALS['db']->parseSchema(file_get_contents(CC_ROOT_DIR.'/classes/db/schema/structure.sql', false));
            ## Insert basic data
            $GLOBALS['db']->parseSchema(file_get_contents($setup_path.'db/install/data.sql', false));
            ## Insert example product/category
            # if (isset($_SESSION['setup']['examples'])) {
            $GLOBALS['db']->parseSchema(file_get_contents($setup_path.'db/install/examples.sql', false));
            # }
            ## Insert Email Contents & Templates
            $GLOBALS['db']->parseSchema(file_get_contents($setup_path.'db/install/email.sql', false));
            
            $config_settings = array_merge(
                $default_config_settings,
                array(
                    'default_language'     => $_SESSION['setup']['config']['default_language'],
                    'default_currency'     => $_SESSION['setup']['config']['default_currency'],
                    'email_address'      => $_SESSION['setup']['admin']['email'],
                    'store_title'      => $_SESSION['setup']['config']['store_name'],
                    'store_name'      => $_SESSION['setup']['config']['store_name'],
                    'email_name'      => $_SESSION['setup']['config']['store_name'],
                    'allow_telemetry'  => !empty($_SESSION['setup']['config']['allow_telemetry']) ? '1' : '0'
                )
            );
            Config::getInstance($glob)->set('config', '', $config_settings, true);
            $GLOBALS['config'] = array_merge($GLOBALS['config'], $config_settings);
            // Create admin user
            $GLOBALS['db']->insert('CubeCart_admin_users', $_SESSION['setup']['admin']);
            // Mark release notes as seen so fresh installs don't redirect to them
            $admin_id = $GLOBALS['db']->insertid();
            Config::getInstance($glob)->set('release_notes', CC_VERSION.'_'.$admin_id, '1');

            // Set the current exchange rates. Pass echo=false so the JSON
            // payload is returned instead of printed to the response body —
            // any output here would prevent the httpredir() at the end of the
            // install handler from sending its Location header.
            $cron = new Cron();
            $cron->updateExchangeRates($_SESSION['setup']['config']['default_currency'], false);

            $default_docs = array(
                0 => array('doc_name' => str_replace('CubeCart', $_SESSION['setup']['config']['store_name'], $strings['setup']['default_doc_title_welcome']), 'doc_content' => $strings['setup']['default_doc_content_welcome'], 'doc_order' => 1, 'doc_lang' => $config['default_language'], 'doc_home' => 1, 'doc_terms' => 0, 'doc_privacy' => 0),
                1 => array('doc_name' => $strings['setup']['default_doc_title_about'], 'doc_content' => $strings['setup']['default_doc_content'], 'doc_order' => 2, 'doc_lang' => $config['default_language'], 'doc_home' => 0, 'doc_terms' => 0, 'doc_privacy' => 0),
                2 => array('doc_name' => $strings['setup']['default_doc_title_terms'], 'doc_content' => $strings['setup']['default_doc_content'], 'doc_order' => 3, 'doc_lang' => $config['default_language'], 'doc_home' => 0, 'doc_terms' => 1, 'doc_privacy' => 0),
                3 => array('doc_name' => $strings['setup']['default_doc_title_privacy'], 'doc_content' => $strings['setup']['default_doc_content'], 'doc_order' => 4, 'doc_lang' => $config['default_language'], 'doc_home' => 0, 'doc_terms' => 0, 'doc_privacy' => 1),
                4 => array('doc_name' => $strings['setup']['default_doc_title_returns'], 'doc_content' => $strings['setup']['default_doc_content'], 'doc_order' => 5, 'doc_lang' => $config['default_language'], 'doc_home' => 0, 'doc_terms' => 0, 'doc_privacy' => 0)
            );
            foreach ($default_docs as $default_doc) {
                $GLOBALS['db']->insert('CubeCart_documents', $default_doc);
            }

            /**
             * Install the default homepage banners.
             *
             * They ship in setup/images/website_images/ and are copied into the
             * STORE's own image library, because:
             *   - images/source/* is gitignored, so nothing can ship from there;
             *   - the previous defaults lived in skins/foundation/images/examples/,
             *     which breaks the homepage of every store on another skin and
             *     dies entirely if that skin is ever removed.
             * Registering them in CubeCart_filemanager means the merchant can
             * swap them in File Manager instead of editing HTML by hand.
             *
             * NOTE the trailing slash on filepath: Catalogue::imagePath()
             * concatenates filepath.filename directly, so 'website_images'
             * without it resolves to 'website_imageshero-1.jpg'.
             */
            $banner_src = CC_ROOT_DIR.'/setup/images/website_images/';
            $banner_dst = CC_ROOT_DIR.'/images/source/website_images/';
            if (is_dir($banner_src)) {
                if (!is_dir($banner_dst)) {
                    @mkdir($banner_dst, 0755, true);
                }
                foreach ((array)glob($banner_src.'*.jpg') as $banner) {
                    $basename = basename($banner);
                    if (!@copy($banner, $banner_dst.$basename)) {
                        continue;
                    }
                    $size = filesize($banner_dst.$basename);
                    $GLOBALS['db']->insert('CubeCart_filemanager', array(
                        'type'        => 1,
                        'disabled'    => 0,
                        'filepath'    => 'website_images/',
                        'filename'    => $basename,
                        'filesize'    => $size,
                        'mimetype'    => 'image/jpeg',
                        'md5hash'     => md5_file($banner_dst.$basename),
                        'title'       => '',
                        'description' => '',
                        'stream'      => 0,
                        'alt'         => '',
                    ));
                }
            }
            $contact_form_data = array('status' => 1, 'email' => $_SESSION['setup']['admin']['email'], 'description' => '');
            foreach ($contact_form_data as $cf_key => $cf_value) {
                $GLOBALS['db']->insert('CubeCart_config', array('name' => 'Contact_Form', 'config_key' => $cf_key, 'config_value' => (string)$cf_value));
            }

            // Install email templates based on all languages
            if (is_array($languages)) {
                foreach ($languages as $code => $lang) {
                    $language->importEmail('email_'.$code.'.xml');
                }
            }

            // Set version number
            $GLOBALS['db']->insert('CubeCart_history', array('version' => CC_VERSION, 'time' => time()));

            build_logos();

            cc_track_event('cubecart_install', array(
                'currency' => $_SESSION['setup']['config']['default_currency'],
                'language' => $_SESSION['setup']['config']['default_language'],
            ));

            $_SESSION['setup']['complete'] = true;
            httpredir('index.php');
        }
    }
    $GLOBALS['smarty']->assign('MODE_INSTALL', true);
}
