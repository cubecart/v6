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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

Admin::getInstance()->permissions('settings', CC_PERM_READ, true);


$cookie_domain 	= $GLOBALS['config']->get('config', 'cookie_domain');
if (empty($cookie_domain)) {
    $domain = parse_url(CC_STORE_URL);
    $cookie_domain =  strpos($domain['host'], '.') ? '.'.str_replace('www.', '', $domain['host']) : '';
    $GLOBALS['config']->set('config', 'cookie_domain', $cookie_domain);
}

if (isset($_POST['config']) && Admin::getInstance()->permissions('settings', CC_PERM_FULL)) {
    $config_old = $GLOBALS['config']->get('config');
    if ($_POST['config']['oid_mode']=='i') {
        $order = Order::getInstance();
        $oid_data = $order->setOrderFormat($_POST['oid_prefix'], $_POST['oid_postfix'], $_POST['oid_zeros'], $_POST['oid_start'], true, (bool)$_POST['oid_force']);
        if (!$oid_data) {
            $GLOBALS['main']->errorMessage('Incremental orders numbers with formatting can\'t be enabled because the MySQL user doesn\'t have permission to &quot;CREATE TRIGGER&quot;. Please grant permissions or seek technical support.');
            $_POST['config']['oid_mode'] = 't';
        } else {
            $_POST['config'] = array_merge($_POST['config'], $oid_data);
            $fields_find = array('cart_order_id');
            $field_replace = $_POST['config']['oid_col'];
        }
    } else {
        $_POST['config'] = array_merge(
            $_POST['config'],
            array(
                'oid_prefix' => $config_old['oid_prefix'],
                'oid_postfix' => $config_old['oid_postfix'],
                'oid_zeros' => $config_old['oid_zeros'],
                'oid_zeros' => $config_old['oid_zeros'],
                'oid_start' => $config_old['oid_start'],
                'oid_col' => $config_old['oid_col']
            )
        );
        $_POST['config']['oid_col'] = 'cart_order_id';
        $fields_find = array('id', 'custom_oid');
        $field_replace = 'cart_order_id';
    }
    if(is_array($fields_find)) {
        foreach (array('subject', 'content_html', 'content_text') as $column) {
            foreach ($fields_find as $field) {
                $GLOBALS['db']->misc("UPDATE `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_email_content` SET `".$column."` = REPLACE(`".$column."`, 'DATA.".$field."', 'DATA.".$field_replace."')");
            }
        }
    }
    if (!empty($_FILES)) {
        ## Do we already have a logo enabled?
        $existing_logo = $GLOBALS['db']->select('CubeCart_logo', 'logo_id', array('status' => 1));

        ## New logos being uploaded
        foreach ($_FILES as $logo) {
            if (file_exists($logo['tmp_name']) && $logo['size'] > 0) {
                if (preg_match('/^.*\.(jpg|jpeg|png|gif|svg|webp)$/i', $logo['name'])) {
                    switch ((int)$logo['error']) {
                        case UPLOAD_ERR_OK:
                            ## Upload is okay, so move to the logo directory, and add a database reference
                            $filename = preg_replace('#[^\w\d\.\-]#', '_', $logo['name']);
                            $target  = CC_ROOT_DIR.'/images/logos/'.$filename;
                            move_uploaded_file($logo['tmp_name'], $target);
                            
                            $record  = array(
                                'filename' => $filename,
                                'status' => (count($_FILES)==1 && !$existing_logo) ? '1' : '0'
                            );

                            if (preg_match('/^.*\.(svg)$/i', $logo['name'])) {
                                $xml = simplexml_load_file($target);
                                $attr = $xml->attributes();
                                $record['mimetype'] = "image/svg+xml";
                                $record['width']  	= $attr->width;
                                $record['height'] 	= $attr->height;
                            } else {
                                $image  = getimagesize($target, $image_info);
                                $record['mimetype'] = $image['mime'];
                                $record['width']  	= $image[0];
                                $record['height'] 	= $image[1];
                            }
                            
                            $GLOBALS['db']->insert('CubeCart_logo', $record);
                            if (!$logo_update) { // prevents x amount of notifications for same thing
                                $GLOBALS['main']->successMessage($lang['settings']['notify_logo_upload']);
                            }
                            $logo_update = true;

                        break;
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                        case UPLOAD_ERR_PARTIAL:
                        case UPLOAD_ERR_NO_FILE:
                        case UPLOAD_ERR_NO_TMP_DIR:
                        case UPLOAD_ERR_CANT_WRITE:
                        case UPLOAD_ERR_EXTENSION:
                        default:
                        $GLOBALS['main']->errorMessage($lang['settings']['error_logo_upload']);
                            trigger_error('Upload Error! Logo not saved.');
                        break;
                    }
                } else {
                    $GLOBALS['main']->errorMessage($lang['settings']['error_logo_upload']);
                }
            }
        }
    }
    $skin_data = $GLOBALS['gui']->getSkinConfig('', $_POST['config']['skin_folder']);

    if (isset($skin_data->info->{'csrf'}) && (string)$skin_data->info->{'csrf'}=='true') {
        $_POST['config']['csrf'] = '1';
    } else {
        $_POST['config']['csrf'] = '0';
    }
    if(isset($_POST['config']['elasticsearch']) && $_POST['config']['elasticsearch']==1) {
        $es_test = new ElasticsearchHandler;
        if(!$es_test->connect(true)) {
            $_POST['config']['elasticsearch'] = '0';
            $GLOBALS['main']->errorMessage($lang['settings']['no_elasticsearch']);
        }
    }
    if(isset($_POST['config']['w3w_status']) && $_POST['config']['w3w_status']==1 && empty($_POST['config']['w3w'])) {
        $request = new Request('accountsapi.what3words.com', '/partner/v1/application?key=PKNNHD3FJC1D');
        $request->cache(false);
        $request->setMethod('POST');
        $request->customHeaders('Content-Type: application/json');
        $request->setData(json_encode(array('name' => $_POST['config']['store_name'], 'description' => CC_STORE_URL.' powered by CubeCart')));
        $request->setSSL();
        if($response = $request->send()) {
            $response = json_decode($response, true);
            $_POST['config']['w3w_status'] = '1';
            $_POST['config']['w3w'] = $response['api_key'];
        } else {
            $_POST['config']['w3w_status'] = '0';
            $_POST['config']['w3w'] = '';
        }
    } else if(isset($_POST['config']['w3w_status']) && $_POST['config']['w3w_status']==0) {
        $_POST['config']['w3w_status'] = '0';
        $_POST['config']['w3w'] = '';
    }

    ## Disable "mobile" skin if master skin is responsive
    if ($_POST['config']['disable_mobile_skin']==0 && isset($_POST['config']['skin_folder']) && !empty($_POST['config']['skin_folder'])) {
        if ((string)$skin_data->info->{'responsive'}=='true') {
            $_POST['config']['disable_mobile_skin'] = '1';
            $GLOBALS['main']->errorMessage($lang['settings']['error_mobile_vs_responsive']);
        }
    }
    
    if (!preg_match('#^([a-z\s_]+)/([a-z\s_]+)$|^UTC$#i', $_POST['config']['time_zone'])) {
        $_POST['config']['time_zone'] = '';
    }

    $dmu = (($_POST['config']['product_weight_unit']=='Lb') ? 'in' : 'cm');
    $GLOBALS['db']->misc("ALTER TABLE `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_inventory` CHANGE `dimension_unit` `dimension_unit` VARCHAR(2) NULL DEFAULT '$dmu'");

    if (isset($_POST['logo']) && is_array($_POST['logo'])) {
        foreach ($_POST['logo'] as $logo_id => $logo) {
            if ($logo['status']) {
                ## Disable all other logos for this skin/style combo
                $GLOBALS['db']->update('CubeCart_logo', array('status' => 0), array('skin' => $logo['skin'], 'style' => $logo['style']));
            }
            if ($GLOBALS['db']->update('CubeCart_logo', $logo, array('logo_id' => (int)$logo_id))) {
                $logo_update = true;
            }
        }
        $GLOBALS['gui']->rebuildLogos();
    }

    if($_POST['download_update_existing']=='1' && $_POST['config']['download_expire']!==$_POST['download_expire_old']) {
        if(in_array($_POST['config']['download_expire'], array('0',''))) {
            $GLOBALS['db']->update('CubeCart_downloads', array('expire' => 0));
        } else if($_POST['config']['download_expire']>0) {
            $new_expiry = ($_POST['download_expire_old']=='0') ? time()+$_POST['config']['download_expire'] : $_POST['config']['download_expire'];
            $old_expiry = $_POST['download_expire_old'];
            $query = 'UPDATE `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_downloads` SET `expire` = `expire` + '.(string)$new_expiry.' - '.(string)$old_expiry;
            $GLOBALS['db']->misc($query);
        }
    }

    $config_new = $_POST['config'];
    if($config_old['default_currency']!==$config_new['default_currency']) {
        $GLOBALS['main']->successMessage($lang['settings']['currency_changed']);
    }
    $config_new['enc_key'] =  $config_old['enc_key']; // Keep old encryption key
    $config_new['offline_content'] = $GLOBALS['RAW']['POST']['config']['offline_content'];
    $config_new['store_copyright'] = $GLOBALS['RAW']['POST']['config']['store_copyright'];
    $config_new['email_smtp_password'] = $GLOBALS['RAW']['POST']['config']['email_smtp_password'];

    $config_new['standard_url'] = preg_replace('#^https://#', 'http://', $config_new['standard_url']);
    if (substr($config_new['standard_url'], 0, 7) !=="http://") {
        $config_new['standard_url'] = 'http://'.$config_new['standard_url'];
    }
    if (!filter_var($config_new['standard_url'], FILTER_VALIDATE_URL)) {
        $config_new['standard_url'] = CC_STORE_URL;
    }
    // Added for backward compatibility as these old values may be used in extensions
    $config_new['ssl_url'] = preg_replace('#^http://#', 'https://', $config_new['standard_url']);
    $domain_parts = parse_url($config_new['standard_url']);
    $config_new['ssl_path'] = $domain_parts['path'].'/';

    if (empty($config_new['time_format'])) {
        $config_new['time_format'] = '%Y-%m-%d %H:%M';
    }

    ## Set default currency to have an exchange rate of 1
    $GLOBALS['db']->update('CubeCart_currency', array('value' => 1), array('code' => $_POST['config']['default_currency']));

    ## If language has changed (Upadted from https://github.com/cubecart/v6/issues/2162)
    $wasLang = $config_old['default_language'];
    $nowLang = $config_new['default_language'];
    if($wasLang !== $nowLang) {
        $make_child = 0;
        $docs = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_lang' => $wasLang, 'doc_parent_id' => 0));
        if($docs){
            foreach($docs as $doc){
                $children = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_parent_id' => $doc['doc_id']));
                if($children){
                    foreach($children as $child){
                        $to_have_new_parent[] = $child['doc_id'];
                        if($child['doc_lang'] == $nowLang){
                            $make_parent = $child['doc_id'];
                            $make_child = $doc['doc_id'];
                        }
                    }
                    if(!empty($make_parent)){
                        $GLOBALS['db']->update('CubeCart_documents', array('doc_parent_id' => $make_parent), array('doc_id' => $to_have_new_parent));
                        $GLOBALS['db']->update('CubeCart_documents', array('doc_parent_id' => 0),            array('doc_id' => $make_parent));
                        $GLOBALS['db']->update('CubeCart_documents', array('doc_parent_id' => $make_parent), array('doc_id' => $make_child));
                    }
                }
                unset($to_have_new_parent, $make_parent, $make_child);
            }
        }
    }

    $updated = ($GLOBALS['config']->set('config', '', $config_new)) ? true : false;

    if ((isset($updated) && $updated) || isset($logo_update)) {
        $GLOBALS['main']->successMessage($lang['settings']['notify_settings_update']);
    } else {
        $GLOBALS['main']->errorMessage($lang['settings']['error_settings_update']);
    }
    httpredir(currentPage());
}

if (isset($_GET['logo']) && isset($_GET['logo_id'])) {
    if (($logo = $GLOBALS['db']->select('CubeCart_logo', false, array('logo_id' => (int)$_GET['logo_id']))) !== false) {
        switch (strtolower($_GET['logo'])) {
        case 'delete':
            if (Admin::getInstance()->permissions('settings', CC_PERM_DELETE)) {
                $paths = array(
                    'images/logos/'.$logo[0]['filename'],
                    'images/logos/'.$logo[0]['skin'].'-'.$logo[0]['style'].'.php',
                    'images/logos/'.$logo[0]['skin'].'.php'
                );
                foreach ($paths as $path) {
                    if (file_exists($logo_path)) {
                        unlink($logo_path);
                    }
                }
                $GLOBALS['db']->delete('CubeCart_logo', array('logo_id' => $logo[0]['logo_id']));
                $GLOBALS['main']->successMessage('Logo removed');
            }
            break;
        }
    }
    
    $GLOBALS['gui']->rebuildLogos();
    httpredir(currentPage(array('logo', 'logo_id')), 'Logos');
}

###########################################

## Add content tabs
$GLOBALS['main']->addTabControl($lang['common']['general'], 'General');
$GLOBALS['main']->addTabControl($lang['settings']['tab_features'], 'Features');
$GLOBALS['main']->addTabControl($lang['settings']['tab_layout'], 'Layout');
$GLOBALS['main']->addTabControl($lang['settings']['tab_stock'], 'Stock');
$GLOBALS['main']->addTabControl($lang['settings']['tab_seo'], 'Search_Engines');
$GLOBALS['main']->addTabControl($lang['settings']['tab_ssl'], 'SSL');
$GLOBALS['main']->addTabControl($lang['settings']['tab_offline'], 'Offline');
$GLOBALS['main']->addTabControl($lang['settings']['tab_logos'], 'Logos');
$GLOBALS['main']->addTabControl($lang['settings']['tab_copyright'], 'Copyright');
$GLOBALS['main']->addTabControl($lang['settings']['tab_advanced'], 'Advanced_Settings');
$GLOBALS['main']->addTabControl($lang['settings']['tab_extra'], 'Extra', null, null, false, '_self', 99);

if ($GLOBALS['db']->select('CubeCart_order_summary', 'id', "`custom_oid` <> ''", false, 1, false, false)) {
    $GLOBALS['smarty']->assign('LOCK_ORDER_NUMBER', true);
}

## Get Front End skins
if (($skins = $GLOBALS['gui']->listSkins()) !== false) {
    $smarty_data['skins'] = $smarty_data['skins_mobile'] = $other_logo_array = array();

    foreach ($skins as $folder => $skin) {
        if ($skin['info']['mobile']) {
            $skin['info']['selected'] = ($skin['info']['name'] == $GLOBALS['config']->get('config', 'skin_folder_mobile')) ? ' selected="selected"' : '';
            $smarty_data['skins_mobile'][] = $skin['info'];
            ## List of styles
            if (isset($skin['styles']) && is_array($skin['styles'])) {
                foreach ($skin['styles'] as $style) {
                    $skin_style[$skin['info']['name']][$style['directory']] = $style['name'];
                }
            }
        } else {
            $skin['info']['selected'] = ($skin['info']['name'] == $GLOBALS['config']->get('config', 'skin_folder')) ? ' selected="selected"' : '';
            $smarty_data['skins'][] = $skin['info'];
            ## List of styles
            if (isset($skin['styles']) && is_array($skin['styles'])) {
                foreach ($skin['styles'] as $style) {
                    $skin_style[$skin['info']['name']][$style['directory']] = $style['name'];
                }
            }
        }
    }
    $GLOBALS['smarty']->assign('SKINS', $smarty_data['skins']);
    $GLOBALS['smarty']->assign('SKINS_MOBILE', $smarty_data['skins_mobile']);

    $other_logo_array = array(
        '0' => array('other_optgroup' => true, 'name' => 'invoices', 'display' => $lang['orders']['title_invoices']),
        '1' => array('name' => 'emails', 'display' => $lang['email']['title_email_templates'])
    );

    $GLOBALS['smarty']->assign('SKINS_ALL', array_merge($smarty_data['skins'], $smarty_data['skins_mobile'], $other_logo_array));

    if (isset($skin_style)) {
        $GLOBALS['smarty']->assign('JSON_STYLES', json_encode((array)$skin_style));
    }
}

## Get admin skins
$path = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/'.'skins'.'/';
foreach (glob($path.'*', GLOB_MARK) as $folder) {
    if (is_dir($folder) && file_exists($folder.'images') && file_exists($folder.'styles') && file_exists($folder.'templates')) {
        $data['name']  = basename($folder);
        $data['selected']  = ($GLOBALS['config']->get('config', 'admin_skin') == $data['name']) ? 'selected="selected"' : '';
        $smarty_data['skins_admin'][] = $data;
    }
    $GLOBALS['smarty']->assign('SKINS_ADMIN', $smarty_data['skins_admin']);
}
## Get cache method
$GLOBALS['smarty']->assign('CACHE_METHOD', $GLOBALS['cache']->getCacheSystem());

## Get Logos
if (($logos = $GLOBALS['db']->select('CubeCart_logo')) !== false) {
    foreach ($logos as $logo) {
        $logo['delete'] = currentPage(null, array('logo' => 'delete', 'logo_id' => $logo['logo_id']));
        $smarty_data['logos'][] = $logo;
    }
    $GLOBALS['smarty']->assign('LOGOS', $smarty_data['logos']);
}
## Get Languages
if (($languages = $GLOBALS['language']->listLanguages()) !== false) {
    foreach ($languages as $code => $option) {
        $option['selected'] = ($code == $GLOBALS['config']->get('config', 'default_language')) ? ' selected="selected"' : '';
        $smarty_data['languages'][] = $option;
    }
    $GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['languages']);
}

## Get countries
if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', array('numcode', 'name'), false, array('name'=>'ASC'))) !== false) {
    $store_country = $GLOBALS['config']->get('config', 'store_country');
    foreach ($countries as $country) {
        $country['selected'] = ($country['numcode'] == $store_country) ? ' selected="selected"' : '';
        $smarty_data['countries'][] = $country;
    }
    $GLOBALS['smarty']->assign('COUNTRIES', $smarty_data['countries']);
    ## Get counties
    $GLOBALS['smarty']->assign('VAL_JSON_COUNTY', state_json());
}


## Get Currencies
if (($currencies = $GLOBALS['db']->select('CubeCart_currency', array('name', 'code'), array('active' => '1'), array('name' => 'ASC'))) !== false) {
    foreach ($currencies as $currency) {
        $currency['selected'] = ($currency['code'] == $GLOBALS['config']->get('config', 'default_currency')) ? ' selected="selected"' : '';
        $smarty_data['currencies'][] = $currency;
    }
    $GLOBALS['smarty']->assign('CURRENCIES', $smarty_data['currencies']);
}

## Get supported timezones from PHP
if (class_exists('DateTimeZone')) {
    $tzabbr = DateTimeZone::listAbbreviations();
    foreach ($tzabbr as $abbr => $array) {
        foreach ($array as $details) {
            if (!empty($details['timezone_id']) && preg_match('#^([a-z\s_]+)/([a-z\s_]+)$|^UTC$#i', $details['timezone_id'])) {
                $timezones[$details['timezone_id']] = $details['timezone_id'];
            }
        }
    }
    if (isset($timezones)) {
        natsort($timezones);
        $current_timezone = $GLOBALS['config']->get('config', 'time_zone');
        $default_timezone = ini_get('date.timezone');
        $current_timezone = empty($current_timezone) ? (empty($default_timezone) ? 'UTC' : $default_timezone) : $current_timezone;
        foreach ($timezones as $timezone) {
            $smarty_data['timezones'][] = array(
                'value'  => $timezone,
                'zone'  => $timezone,
                'selected' => ($timezone == $current_timezone) ? ' selected="selected"' : '',
            );
        }
        $GLOBALS['smarty']->assign('TIMEZONES', $smarty_data['timezones']);
    }
}

## Default digital custom path
$GLOBALS['config']->get('config', 'dnLoadRootPath', rootHomePath());
$GLOBALS['config']->get('config', 'dnLoadCustomPath', ($GLOBALS['config']->isEmpty('config', 'dnLoadCustomPath')) ? 'files' : $GLOBALS['config']->get('config', 'dnLoadCustomPath'));

## Auto assign config settings to {VAL_[KEYNAME]}
for ($i = 1; $i <= 6; ++$i) {
    $a_n_s[(string)$i] = $lang['order_state']['name_' . (string)$i];
}

$select_options = array(
    'admin_notify_status'	=> $a_n_s,
    'basket_jump_to'  => null,
    'cache'					=> array('1' => $lang['common']['enabled'], '0' => $lang['common']['disabled']),
    'catalogue_expand_tree' => null,
    'skin_change'   => array($lang['common']['no'], $lang['settings']['all_skin_select'], $lang['settings']['admin_only_skin_select']),
    'debug'     => array($lang['common']['disabled'], $lang['common']['enabled']),
    'catalogue_hide_prices' => null,
    'email_method'			=> array('mail' => $lang['settings']['email_method_mail'], 'smtp' => $lang['settings']['email_method_smtp'], 'smtp_ssl' => $lang['settings']['email_method_smtp_ssl'].' ('.$lang['common']['recommended'].')', 'smtp_tls' => $lang['settings']['email_method_smtp_tls'].' ('.$lang['common']['recommended'].')', 'sendgrid' => 'SendGrid'),
    'offline'    => null,
    'basket_out_of_stock_purchase'  => null,
    'catalogue_popular_products_source' => array($lang['settings']['product_popular_views'], $lang['settings']['product_popular_sales']),
    'basket_tax_by_delivery'   => array($lang['address']['billing_address'], $lang['address']['delivery_address']),
    'proxy'     => null,
    'catalogue_sale_mode' => array($lang['common']['disabled'], $lang['settings']['sales_per_product'], $lang['settings']['sales_percentage']),
    'recaptcha' => array(0 => $lang['common']['off']." (".$lang['common']['not_recommended'].")", 2 => "reCaptcha v2 - Checkbox", 3 => "reCaptcha v2 - Invisible (".$lang['common']['recommended'].")"),
    'seo_metadata'   => array($lang['settings']['seo_meta_option_disable'], $lang['settings']['seo_meta_option_merge'], $lang['settings']['seo_meta_option_replace']),
    'basket_allow_non_invoice_address' => null,
    'catalogue_latest_products'   => null,
    'catalogue_show_empty' => null,
    'email_smtp'   => null,
    'ssl'     => null,
    'stock_level'   => null,
    'stock_change_time'  => array(1 => $lang['settings']['stock_reduce_process'], 0 => $lang['settings']['stock_reduce_complete'], 2 => $lang['settings']['stock_reduce_pending']),
    'stock_warn_type'  => array($lang['settings']['stock_warning_method_global'], $lang['settings']['stock_warning_method_product']),
    'product_weight_unit' => array('Lb' => $lang['settings']['weight_unit_lb'], 'Kg' => $lang['settings']['weight_unit_kg']),
    'time_format'   => '%Y-%m-%d %H:%M',
    'product_sort_direction' => array('ASC' => 'ASC', 'DESC' => 'DESC'),
    'product_clone'      => array('0' => $lang['common']['disabled'], '2' => $lang['settings']['product_clone_hide'], '1' => $lang['common']['enabled']),
    'product_clone_code'    => array('1' => $lang['settings']['product_clone_new_code'], '2' => $lang['settings']['product_clone_old_code']),
    'seo_add_cats'      => array('0' => $lang['common']['no'], '1' => $lang['settings']['seo_add_cats_top'], '2' => $lang['settings']['seo_add_cats_all']),
    'seo_cat_add_cats'      => array('1' => $lang['common']['yes'], '0' => $lang['common']['no']),
    'seo_ext'      => array('' => $lang['common']['none'].' ('.$lang['common']['recommended'].')', '.html' => '.html'),
    'oid_mode'      => array('t' => $lang['orders']['id_traditional'], 'i' => $lang['orders']['id_incremental']),
    'shipping_defaults' => array('0' => $lang['common']['cheapest'], '1' => $lang['settings']['cheapest_not_free'], '2' => $lang['settings']['most_expensive'])
);
$current_skin_path = CC_ROOT_DIR.'/skins/'.$GLOBALS['config']->get('config', 'skin_folder').'/templates/';
$gr_compatibility = array(
    'v2' => file_exists($current_skin_path.'content.recaptcha.head.php'),
    'invisible' => file_exists($current_skin_path.'element.recaptcha.invisible.php')
);
$GLOBALS['smarty']->assign('gr_compatibility', $gr_compatibility);
$GLOBALS['smarty']->assign('w3w_compatibility', file_exists($current_skin_path.'element.w3w.php'));

if ($inventory_columns = $GLOBALS['db']->misc('SHOW FULL COLUMNS FROM '.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory')) {
    $excluded = array('use_stock_level');
    $select_options[]['product_sort_column'] = array();
    foreach ($inventory_columns as $inventory_column) {
        if (!in_array($inventory_column['Field'], $excluded)) {
            $inventory_column['Comment'] = ($inventory_column['Field']=='price') ? $lang['common']['price'] : $inventory_column['Comment'];
            $select_options['product_sort_column'][$inventory_column['Field']] = (empty($inventory_column['Comment'])) ? $inventory_column['Field'] : $inventory_column['Comment'];
        }
    }
    asort($select_options['product_sort_column']);
}

$smarty_data['config'] = $GLOBALS['config']->get('config');

$GLOBALS['smarty']->assign('CONFIG', $smarty_data['config']);

if (isset($select_options)) {
    foreach ($select_options as $field => $options) {
        if (!is_array($options) || empty($options)) {
            $options = array($lang['common']['no'], $lang['common']['yes']);
        }
        foreach ($options as $value => $title) {
            $selected = ($GLOBALS['config']->has('config', $field) && $GLOBALS['config']->get('config', $field) == $value) ? ' selected="selected"' : '';
            $smarty_data['options'][] = array('value' => $value, 'title' => $title, 'selected' => $selected);
        }
        $GLOBALS['smarty']->assign('OPT_'.strtoupper($field), $smarty_data['options']);
        unset($smarty_data['options']);
    }
}
$GLOBALS['smarty']->assign('HOOK_TAB_CONTENT', $GLOBALS['hook_tab_content']);
$page_content = $GLOBALS['smarty']->fetch('templates/settings.index.php');
