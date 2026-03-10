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
 * AJAX controller
 */
class Ajax
{

    //=====[ Public ]=======================================

    /**
     * Load the proper AJAX function/method
     */
    public static function load()
    {
        global $glob;

        $json = '';
        //Kill debug
        $GLOBALS['debug']->supress();


        //Try a hook first
        foreach ($GLOBALS['hooks']->load('class.ajax.load') as $hook) {
            include $hook;
        }
        if (!empty($json)) {
            return $json;
        }

        //Get the correct function/method
        $type = (isset($_GET['type'])) ? $_GET['type'] : '';
        $string = isset($_GET['q']) ? $_GET['q'] : '';
        $function = (isset($_GET['function']) && !empty($_GET['function'])) ? $_GET['function'] : 'search';
        switch ($function) {
            case 'rebuildElasticsearch':
                $return_data = self::rebuildElasticsearch();
            break;
            case 'filesize':
                $return_data = self::filesize((string)$_GET['path'], 0);
            break;
            case 'fmSearch':
                $return_data = self::fmSearch();
            break;
            case 'doneToggle':
                $return_data = self::doneToggle();
            break;
            case 'viewEmail':
                $return_data = self::viewEmail((int)$_GET['id'], (string)$_GET['mode']);
            break;
            case 'viewHookBackup':
                $return_data = self::viewHookBackup((int)$_GET['hook_id'], (string)$_GET['backup']);
            break;
            case 'SMTPTest':
                $return_data = self::SMTPTest();
            break;
            case 'previewOrderFormat':
                $return_data = self::previewOrderFormat();
            break;
            case 'template':
                $return_data = self::template($type, $string);
            break;
            case 'subscriber_log':
                $return_data = self::subscriberLog();
            break;
            case 'seopath':
                $return_data = self::seopath($_GET['type'], $_GET['item_id']);
            break;
            case 'search':
            default:
                $return_data = self::search($type, $string);
            break;
        }
        return $return_data;
    }

    public static function doneToggle() {
        if (!CC_IN_ADMIN) {
            return json_encode(array('success' => '0'));
        }
        $allowed_tables = array('404_log');
        if (!isset($_POST['table']) || !in_array($_POST['table'], $allowed_tables, true)) {
            return json_encode(array('success' => '0'));
        }
        if($_POST['status']=='warn') {
            $update = array('warn' => 0);
        } else {
            $status = $_POST['status'] == '1' ? 0 : 1;
            $update = array('done' => $status);
            if($status==0) {
                $update['warn'] = 0;
            }
        }
        if($GLOBALS['db']->update('CubeCart_'.$_POST['table'], $update, array('id' => (int)$_POST['id']))) {
            return json_encode(array('success' =>'1', 'id' => (int)$_POST['id'], 'status' => (string)$_POST['status']));
        } else {
            return json_encode(array('success' =>'0', 'id' => (int)$_POST['id'], 'status' => (string)$_POST['status']));
        }
    }

    /**
     * Rebuild ElasticSearch
     *
     * @return string
     */
    public static function rebuildElasticSearch() {
        if (!CC_IN_ADMIN) {
            return json_encode(false);
        }
        $es = new ElasticsearchHandler;
        $status  = $es->rebuild($_GET['page']);
        if (is_array($status)) {
            $data = $status;
        } else {
            $data = ($status) ? array('complete' => 'true', 'percent' => 100) : array('error' => 'true');
        }
        return json_encode($data);
    }

    /**
     * Get SEO path
     *
     * @param string $type
     * @param int $item_id
     * @return string
     */
    public static function seopath($type, $item_id)
    {
        if (!CC_IN_ADMIN) {
            return json_encode(false);
        }
        return json_encode($GLOBALS['seo']->getdbPath($type, $item_id));
    }

    /**
     * Get directory size
     *
     * @param string $path
     * @return data/false
     */
    public static function filesize($path, $total)
    {
        if (!CC_IN_ADMIN) {
            return json_encode(false);
        }
        $path = str_replace(array('..', "\0"), '', $path);
        return json_encode(dirsize(CC_ROOT_DIR.'/'.$path, $total));
    }

    /**
     * Admin search function
     *
     * @param string $type
     * @param string $search_string
     * @return data/false
     */
    public static function search($type, $search_string)
    {
        $data = array();
        
        foreach ($GLOBALS['hooks']->load('class.ajax.search') as $hook) {
            include $hook;
        }

        if (!empty($type) && !empty($search_string)) {
            switch (strtolower($type)) {
            case 'user':
                if (($results = $GLOBALS['db']->select('CubeCart_customer', false, array('~'.$search_string => array('last_name', 'first_name', 'email')), false, false, false, false)) !== false) {
                    foreach ($results as $result) {
                        $data[] = array(
                            'value'  => $result['customer_id'],
                            'display' => $result['first_name'].' '.$result['last_name'],
                            'info'  => $result['email'],
                            'data'  => $result,
                        );
                    }
                }
                break;
            case 'address':
                if (($results = $GLOBALS['db']->select('CubeCart_addressbook', false, array('customer_id' => (int)$search_string), false, false, false, false)) !== false) {
                    foreach ($results as $result) {
                        $result['state'] = getStateFormat($result['state']);
                        $result['country'] = getCountryFormat($result['country']);
                        $result['description'] = empty($result['description']) ? $result['line1'].', '.$result['postcode'] : $result['description'];
                        $data[]    = $result;
                    }
                }
                break;
            case 'product':
                // Limited to a maximum of 15 results, in order to prevent it going mental
                if (($results = $GLOBALS['db']->select('CubeCart_inventory', false, array('~'.$search_string => array('name', 'product_code')), false, 15, false, false)) !== false) {
                    foreach ($results as $result) {
                        $lower_price = Tax::getInstance()->salePrice($result['price'], $result['sale_price'], false);
                        if ($lower_price && ($lower_price < $result['price'])) {
                            $result['price'] = $lower_price;
                        }
                        $data[] = array(
                            'value'  => $result['product_id'],
                            'display' => $result['name'],
                            'info'  => Tax::getInstance()->priceFormat($result['price']),
                            'data'  => $result,
                        );
                    }
                }
                break;
            case 'newsletter':
                $newsletter = Newsletter::getInstance();
                $status  = $newsletter->sendNewsletter($_GET['q'], $_GET['page']);
                if (is_array($status)) {
                    $data = $status;
                } else {
                    $data = ($status) ? array('complete' => 'true', 'percent' => 100) : array('error' => 'true');
                }
                break;
            case 'files':

                if (!isset($_GET['dir']) || $_GET['dir'] == '/') {
                    $dir = false;
                } else {
                    $dir = $_GET['dir'];
                }

                $filemanager = new FileManager(isset($_GET['group']) ? $_GET['group'] : '', $dir);

                // Directories
                $dirs = $filemanager->getDirectories();

                if (is_array($dirs)) {
                    $dir = $filemanager->formatPath($dir) ?? '';
                    if(isset($dirs[$dir]) && !empty($dirs[$dir])) {
                        foreach ($dirs[$dir] as $parent => $folder) {
                            $path = (!empty($dir)) ? '/' : '';
                            $json[] = array(
                                'type' => 'directory',
                                'path' => urldecode($dir.basename($folder).'/'),
                                'name' => basename($folder),
                            );
                        }
                    }
                }

                $assigned_images = array();
                $assigned_file	 = false;
                
                if (isset($_GET['product_id'])) {
                    $assigned_images = $filemanager->productImages($_GET['product_id']);
                    $assigned_file = $filemanager->productFile($_GET['product_id']);
                }
                if (isset($_GET['cat_id'])) {
                    $assigned_images = $filemanager->catImages($_GET['cat_id']);
                }
                if (isset($_GET['unique_image'])) {
                    $assigned_images = $filemanager->uniqueImage($_GET['unique_image']);
                }

                if (($files = $filemanager->listFiles()) !== false) {
                    $catalogue = $GLOBALS['catalogue']->getInstance();
                    foreach ($files as $result) {
                        if ($filemanager->getMode() == FileManager::FM_FILETYPE_IMG) {
                            $fetch = $catalogue->imagePath($result['file_id'], 'medium');
                            $path = $name = $fetch;
                        } else {
                            $path = $result['filepath'];
                            $name = $result['filename'];
                        }

                        $assigned = '0';
                        if (isset($assigned_images[$result['file_id']])) {
                            $assigned = $assigned_images[$result['file_id']];
                        } elseif ($assigned_file && $assigned_file == $result['file_id']) {
                            $assigned = '1';
                        }

                        $json[] = array(
                            'form_field' => $filemanager->form_fields,
                            'assigned' => $assigned,
                            'type'   => 'file',
                            'type_id'   => $result['type'],
                            'path'   => dirname($path).'/',
                            'file'   => basename($result['filename']),
                            'name'   => basename($name),
                            'id'   => $result['file_id'],
                            'description' => $result['description'],
                            'mime'   => $result['mimetype']
                        );
                    }
                }
                $data = (isset($json) && is_array($json)) ? $json : false;
                break;
            default:
                ## See https://github.com/cubecart/v6/issues/3191 
                foreach ($GLOBALS['hooks']->load('class.ajax.search.case_default') as $hook) {
                    include $hook;
                }
                break;
            }
            if (!$data) {
                $data = array();
            }
            return json_encode($data);
        }
        return false;
    }

    /**
     * Preview Order Format
     *
     * @return data/false
     */
    public static function previewOrderFormat()
    {
        if (CC_IN_ADMIN) {
            if ($_GET['oid_mode']=='t') {
                $html_out = "<h3>Preview sample of next 5 Orders</h3>";
                for ($i = 1; $i <= 5; $i++) {
                    $html_out .= date('ymd-His-').rand(1000, 9999)."<br>";
                }
                $html_out .= "<p>Please note that the last four digits are random.</p>";
            } else {
                $html_out = "<h3>Preview of next 5 Orders</h3>";
                $next = $GLOBALS['db']->select('CubeCart_order_summary', 'MAX(`id`) as `max_oid`');
                $order = Order::getInstance();
                $next[0]['max_oid']++;
                for ($i = $next[0]['max_oid']; $i <= $next[0]['max_oid']+5; $i++) {
                    $html_out .= $order->setOrderFormat($_GET['oid_prefix'], $_GET['oid_postfix'], $_GET['oid_zeros'], $_GET['oid_start'], false, false, $i).'<br>';
                }
            }
            return "<div class=\"default_modal\">".$html_out."</div>";
        }
        return false;
    }

    /**
     * Search for image/digital download
     *
     * @return data/false
     */
    public static function fmSearch() {
        if (CC_IN_ADMIN) {
            $term = $_POST['term'];
            if(strlen($term)>=3) {
                $mode = $_POST['mode']=='digital' ? 'digital' : 'images';
                $action = $_POST['action']=='show' ? 'show' : 'location';
                $path = $_POST['mode']=='digital' ? CC_ROOT_DIR.'/files' : CC_ROOT_DIR.'/images/source';
                $files = fmSearchList($path, $term);
                $output = '';
                foreach($files as $file) {
                    if($mode == 'images') {
                        $preview = '<div><img src="'.str_replace(CC_ROOT_DIR.'/', CC_ROOT_REL, $file).'" /></div>';
                    } else {
                        $preview = '';
                    }
                    $file = str_replace($path.'/','',$file);
                    $file_escaped = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');
                    $subdir = urlencode(ltrim(dirname($file),'/'));
                    $anchor = md5(urlencode(basename($file)));
                    if($action=='show') {
                        $output .= "<li class=\"show\"><a href=\"?_g=filemanager&subdir=$subdir&mode=$mode&".time()."&file_id=file_$anchor\">$preview $file_escaped</a></li>";
                    } else {
                        $output .= "<li>$preview $file_escaped</li>";
                    }
                    
                }
                if(empty($output)) {
                    $output = "<p>".$GLOBALS['language']->common['error_no_results']."</p>";
                } else {
                    $output = "<ul class=\"fmsearch_$mode\">$output</ul>";
                }
            } else {
                $output = "<p>".$GLOBALS['language']->filemanager['min_two_char']."</p>";
            }
            return "<div class=\"mail_modal\"><h3>".sprintf($GLOBALS['language']->filemanager['search_result'], htmlspecialchars($_POST['term'], ENT_QUOTES, 'UTF-8'))."</h3>$output</div>";
        }
    }

    /**
     * Test SMTP
     *
     * @return data/false
     */
    public static function SMTPTest()
    {
        if (CC_IN_ADMIN) {
            $methods = array('mail' => $GLOBALS['language']->settings['email_method_mail'], 'smtp' => $GLOBALS['language']->settings['email_method_smtp'], 'smtp_ssl' => $GLOBALS['language']->settings['email_method_smtp_ssl'], 'smtp_tls' => $GLOBALS['language']->settings['email_method_smtp_tls']);
            // Allow plugins to register email method names
            $email_methods = array();
            foreach ($GLOBALS['hooks']->load('admin.settings.email.methods') as $hook) {
                include $hook;
            }
            $methods = array_merge($methods, $email_methods);
            $email_test_method = $GLOBALS['RAW']['POST']['email_method'];
            $email_test_method_name = isset($methods[$email_test_method]) ? $methods[$email_test_method] : $email_test_method;

            $email_test_subject = "Testing ".$email_test_method_name;
            $email_test_body = "Testing email sent by &quot;".$email_test_method_name."&quot; from CubeCart v".CC_VERSION." at ".CC_STORE_URL.".<br><br>If you are reading this message then you can be sure that email from your store is working.";
            $email_test_altbody = strip_tags($email_test_body);
            $email_test_success = false;
            $email_test_handled = false;
            $email_test_html = '';

            // Allow plugins to handle the test
            foreach ($GLOBALS['hooks']->load('admin.settings.email.test') as $hook) {
                include $hook;
            }
            if ($email_test_handled) {
                return "<div class=\"default_modal\">".str_replace('{{RESULT}}', $email_test_success ? 'Success' : 'Fail', $email_test_html)."</div>";
            } else if ($GLOBALS['RAW']['POST']['email_method']!=="mail") {
                @ob_start();
                $test_mailer = new Mailer();
                $test_mailer->SMTPDebug = 2;
                $test_mailer->Debugoutput = "html";
                $test_mailer->ClearAddresses();
                $test_mailer->From   = $GLOBALS['RAW']['POST']['email_address'];
                $test_mailer->FromName  = html_entity_decode($GLOBALS['RAW']['POST']['email_name'], ENT_QUOTES);
                $test_mailer->Host = $GLOBALS['RAW']['POST']['email_smtp_host'];
                $test_mailer->Port = $GLOBALS['RAW']['POST']['email_smtp_port'];
                if ($GLOBALS['RAW']['POST']['email_method']=='smtp_ssl') {
                    $test_mailer->SMTPSecure = 'ssl';
                } elseif ($GLOBALS['RAW']['POST']['email_method']=='smtp_tls') {
                    $test_mailer->SMTPSecure = 'tls';
                }
                if ($GLOBALS['RAW']['POST']['email_smtp']=='1') {
                    $test_mailer->SMTPAuth = true;
                    $test_mailer->Username= $GLOBALS['RAW']['POST']['email_smtp_user'];
                    $test_mailer->Password = $GLOBALS['RAW']['POST']['email_smtp_password'];
                }
                $test_mailer->AddAddress($GLOBALS['RAW']['POST']['email_address']);
                $test_mailer->Subject = $email_test_subject;
                $test_mailer->Body = $email_test_body;
                $test_mailer->AltBody = $email_test_altbody;
                // Send email
                $test_success = $test_mailer->Send();
                $test_mailer_test_results = @ob_get_contents();
                @ob_end_clean();

                $json = "<h3>Testing ".$email_test_method_name." - {{RESULT}}</h3>";

                if (!empty($test_mailer_test_results)) {
                    $test_mailer_test_results_data = array(
                        'request_url' => 'mailto:'.$GLOBALS['RAW']['POST']['email_address'],
                        'request' => 'Subject: Testing CubeCart',
                        'result' => $test_mailer_test_results,
                        'error' => ($test_success) ? null : "Mailer Failed" ,
                    );
                    $GLOBALS['db']->insert('CubeCart_request_log', $test_mailer_test_results_data);
                    $json .= $test_mailer_test_results;
                } else {
                    $json .= "Test failed to execute. ".$test_mailer->ErrorInfo;
                }
                return "<div class=\"default_modal\">".str_replace('{{RESULT}}', $test_success ? 'Success' : 'Fail', $json)."</div>";
            } else {
                $test_mailer = new Mailer();
                $test_mailer->ClearAddresses();
                $test_mailer->AddAddress($GLOBALS['RAW']['POST']['email_address']);
                $test_mailer->Subject = $email_test_subject;
                $test_mailer->Body = $email_test_body;
                $test_mailer->AltBody = $email_test_altbody;
                $test_mailer->Send();

                return "<div class=\"default_modal\"><h3>Testing ".$email_test_method_name."</h3><p>It isn't possible  to get a definitive test result for the &quot;PHP mail() Function&quot; method.</p><p>We have attempted to send a test email to &quot;".$GLOBALS['RAW']['POST']['email_address']."&quot; with the subject of &quot;".$email_test_subject."&quot; Please note that it can take ten minutes or even longer for a busy mail server to deliver email. Don't forget to check your spam folder!</p><p>This method can fail if the server hasn't been configured properly and may refuse to send mail from &quot;untrusted&quot; sources such as Hotmail, Yahoo, AOL etc&hellip;. We recommend using an email address from a domain hosted on this server such as sales@".parse_url(CC_STORE_URL, PHP_URL_HOST)." for example and this may need to be setup form within your web hosting account.</p></div>";
            }
        }
        return false;
    }

    /**
     * Subscriber consent log
     *
     * @return data/false
     */
    public static function subscriberLog()
    {
        if (CC_IN_ADMIN) {
            if (filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
                $html_out = "<h3>Log for ".htmlspecialchars($_GET['email'], ENT_QUOTES, 'UTF-8')."</h3>";
                if ($logs = $GLOBALS['db']->select('CubeCart_newsletter_subscriber_log', false, array('email' => $_GET['email']))) {
                    foreach ($logs as $log) {
                        $html_out .= '<strong>'.$log['date'].' - '.$log['ip_address'].'</strong><br>'.$log['log'].'<br>';
                    }
                    $html_out .= '<p style="text-align:center"><a href="?_g=customers&node=subscribers&delete_log='.urlencode($_GET['email']).'" class="delete">Delete Log</a></p>';
                } else {
                    $html_out .= "<p>No logs found.</p>";
                }
            } else {
                $html_out = "<p>Invalid email</p>";
            }
            return "<div class=\"default_modal\">".$html_out."</div>";
        }
        return false;
    }

    /**
     * Dynamic template load
     *
     * @param string $type
     * @param string $search_string
     * @return data/false
     */
    public static function template($type, $search_string)
    {
        switch (strtolower($type)) {
            case 'prod_options':
                $options['options'] = Catalogue::getInstance()->displayProductOptions($search_string);
                $GLOBALS['smarty']->assign('product', $options);
                die($GLOBALS['smarty']->fetch('templates/element.product_options.php'));
            break;
        }
        return false;
    }

    /**
     * View Email
     *
     * @param int $id
     * @return data/false
     */
    public static function viewEmail($id, $mode)
    {
        $column = ($mode == 'content_text') ? 'content_text' : 'content_html';

        if (CC_IN_ADMIN) {
            $test_mailer = $GLOBALS['db']->select('CubeCart_email_log', array($column), array('id' => $id));
            if ($mode == 'content_text') {
                return '<div style="font-family: \'Courier New\', Courier">'.nl2br(htmlentities($test_mailer[0][$column], ENT_QUOTES)).'</div>';
            } else {
                return $test_mailer[0][$column];
            }
        }
        return false;
    }

    /**
     * View hook file backup content
     *
     * @param int $hook_id
     * @param string $backup
     * @return string/false
     */
    public static function viewHookBackup($hook_id, $backup)
    {
        if (!CC_IN_ADMIN || !Admin::getInstance()->is()) {
            return false;
        }
        if (($hook = $GLOBALS['db']->select('CubeCart_hooks', false, array('hook_id' => $hook_id))) !== false) {
            $hook_data = $hook[0];
            $filepath = (!empty($hook_data['filepath'])) ? $hook_data['filepath'] : 'hooks/'.$hook_data['trigger'].'.php';
            $filepath = preg_replace(array('#[^a-z0-9.\\\\_/-]#i', '#\.{1,2}/#'), '', $filepath);
            $hook_full_path = CC_ROOT_DIR.'/modules/plugins/'.$hook_data['plugin'].'/'.$filepath;

            // Validate backup parameter
            if (preg_match('/^v[\d.]+\.default$/', $backup)) {
                $backup_file = $hook_full_path.'.'.$backup.'.bak';
            } elseif (ctype_digit($backup)) {
                $backup_file = $hook_full_path.'.'.$backup.'.bak';
            } else {
                return false;
            }

            if (file_exists($backup_file)) {
                $code = file_get_contents($backup_file);
                return '<div style="font-size:13px; padding:10px; margin:0; overflow:auto;">'.highlight_string($code, true).'</div>';
            }
        }
        return false;
    }
}
