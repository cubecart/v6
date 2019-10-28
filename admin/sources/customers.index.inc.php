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
Admin::getInstance()->permissions('customers', CC_PERM_READ, true);

global $lang;

if (isset($_POST['search']) && !empty($_POST['search'])) {
    // Create search string
    if (isset($_POST['search']['customer_id']) && !empty($_POST['search']['customer_id']) && is_numeric($_POST['search']['customer_id'])) {
        httpredir(currentPage(null, array('action' => 'edit', 'customer_id' => (int)$_POST['search']['customer_id'])));
    } else {
        httpredir(currentPage(null, array('q' => (string)$_POST['search']['keywords'])));
    }
}

if (isset($_GET['group_id']) && isset($_GET['customer_id']) && Admin::getInstance()->permissions('customers', CC_PERM_DELETE)) {
    $group_id = (int)$_GET['group_id'];
    $customer_id = (int)$_GET['customer_id'];
    foreach ($GLOBALS['hooks']->load('admin.customer.group.delete') as $hook) {
        include $hook;
    }
    $GLOBALS['db']->delete('CubeCart_customer_membership', array('group_id' => $group_id, 'customer_id' => $customer_id));
    httpredir(currentPage(array('group_id')));
}

// If we are wanting an external report start new External class
if (isset($_POST['external_report']) && is_array($_POST['external_report'])) {
    $module_name = array_keys($_POST['external_report']);
    $external_class_path = 'modules/external/'.$module_name[0].'/external.class.php';
    if (file_exists($external_class_path)) {
        include $external_class_path;
        $external_report = new External($GLOBALS['config']->get($module_name[0]));
    }
    if (($customers_export = $GLOBALS['db']->select('CubeCart_customer', array('title', 'first_name', 'last_name', 'phone', 'mobile', 'customer_id', 'email'))) !== false) {
        // Get States Array
        $zones = $GLOBALS['db']->select('CubeCart_geo_zone', array('id', 'name'), array('status' => 1));
        if ($zones) {
            foreach ($zones as $zone) {
                $zone_name[$zone['id']] = $zone['name'];
            }
        }
        foreach ($customers_export as $customer) {
            // Find default address
            $address = $GLOBALS['db']->select('CubeCart_addressbook', array('company_name', 'line1', 'line2', 'town', 'state', 'postcode', 'country'), array('customer_id' => $customer['customer_id'], 'billing' => 1));
            // Get state name if it is numeric
            $address[0]['state'] = is_numeric($address[0]['state']) ? $zone_name[$address[0]['state']] : $address[0]['state'];
            $data = array_merge($address[0], $customer);
            $external_report->report_customer_data($data);
        }
    }
    $file_name = ucfirst($module_name[0]).' '.$lang['customer']['customer_export'].' '.date("Ymd").'.csv';
    $GLOBALS['debug']->supress(true);
    deliverFile(false, false, $external_report->_report_data, $file_name);
    exit;
}

if (isset($_POST['customer']) && is_array($_POST['customer']) && Admin::getInstance()->permissions('customers', CC_PERM_EDIT)) {
    $customer = $_POST['customer'];
    $customer_added = $customer_not_added = $customer_updated = false;
    // Reset password
    if (isset($customer['password']) && !empty($customer['password'])) {
        if ($customer['password'] === $customer['passconf']) {
            $salt = Password::getInstance()->createSalt();
            $customer['salt'] = $salt;
            $customer['new_password'] = 1;
            $customer['password'] = Password::getInstance()->getSalted($customer['password'], $salt);
        }
        //Or not
    } else {
        unset($customer['password']);
        unset($customer['passconf']);
    }

    // Format data nicely from mr barney brimstock to Mr Barney Brimstock
    $customer['title']  = ucwords($customer['title']);
    $customer['first_name'] = ucwords($customer['first_name']);
    $customer['last_name'] = ucwords($customer['last_name']);

    // Update/Add customer record
    if (isset($_POST['customer_id']) && is_numeric($_POST['customer_id'])) {
        foreach ($GLOBALS['hooks']->load('admin.customer.update') as $hook) {
            include $hook;
        }
        foreach ($customer as $key => $value) {
            $customer[$key] = filter_var($value, FILTER_SANITIZE_STRING);
        }
        $email_check = $GLOBALS['db']->select('CubeCart_customer', array('customer_id'), array('email' => $customer['email']));
        if($email_check && $email_check[0]['customer_id']!==$_POST['customer_id']) {
            $GLOBALS['main']->errorMessage($lang['account']['error_email_in_use']);
            $customer_updated = false;
        } elseif (($GLOBALS['db']->update('CubeCart_customer', $customer, array('customer_id' => $_POST['customer_id']))) !== false) {
            if((int)$customer['status']===0) $GLOBALS['db']->delete('CubeCart_sessions', array('customer_id' => (int)$_POST['customer_id']));
            $customer_updated = true;
        }
        $customer_id = $_POST['customer_id'];
    } else {
        // Validate
        $required = array('first_name', 'last_name', 'email');
        $customer['registered'] = time();
        foreach ($customer as $field => $value) {
            if ($field == 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $GLOBALS['main']->errorMessage($lang['common']['error_email_invalid']);
                $error = true;
            } elseif($GLOBALS['db']->select('CubeCart_customer', array('customer_id'), array('email' => $customer['email']))) {
                $GLOBALS['main']->errorMessage($lang['account']['error_email_in_use']);
                $error = true;
            }
            if (in_array($field, $required) && empty($value)) {
                $GLOBALS['main']->errorMessage($lang['account']['error_customer_create_empty_fields']);
                $error = true;
            }
        }

        foreach ($GLOBALS['hooks']->load('admin.customer.add') as $hook) {
            include $hook;
        }

        if (!isset($error)) {
            if (($GLOBALS['db']->insert('CubeCart_customer', $customer)) !== false) {
                $customer_added = true;
                $customer_id = $GLOBALS['db']->insertid();
            } else {
                $customer_not_added = true;
            }
        } else {
            $customer_not_added = true;
        }
    }

    if (isset($customer_id)) {

        // Update / Insert newsletter subscription
        $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', '`customer_id` = '.$customer_id.' OR `email` = \''.$customer['email'].'\'');
        if (isset($customer['subscription_status']) && $customer['subscription_status']) {
            $GLOBALS['db']->insert('CubeCart_newsletter_subscriber', array('customer_id' => $customer_id, 'status' => 1, 'email' => $customer['email']));
        }

        // Delete Group membership
        if (isset($_POST['membership_delete']) && is_array($_POST['membership_delete']) && Admin::getInstance()->permissions('customers', CC_PERM_DELETE)) {
            foreach ($_POST['membership_delete'] as $membership_id) {
                if (empty($membership_id)) {
                    continue;
                }
                foreach ($GLOBALS['hooks']->load('admin.customer.group.delete') as $hook) {
                    include $hook;
                }
                if (($GLOBALS['db']->delete('CubeCart_customer_membership', array('membership_id' => (int)$membership_id))) !== false) {
                    $customer_updated = true;
                }
            }
        }

        // Add Group membership
        if (isset($_POST['membership_add']) && is_array($_POST['membership_add'])) {
            foreach ($_POST['membership_add'] as $group_id) {
                if (empty($group_id)) {
                    continue;
                }
                $record = array('customer_id' => $customer_id, 'group_id' => (int)$group_id);
                foreach ($GLOBALS['hooks']->load('admin.customer.group.add') as $hook) {
                    include $hook;
                }
                if (($GLOBALS['db']->select('CubeCart_customer_membership', false, $record)) !== false) {
                    continue;
                }
                if (($GLOBALS['db']->insert('CubeCart_customer_membership', $record)) !== false) {
                    $customer_updated = true;
                }
            }
        }

        // Update/Add Address record(s)
        if (isset($_POST['address']) && is_array($_POST['address'])) {
            foreach ($_POST['address'] as $field => $content) {
                if (is_array($content)) {
                    foreach ($content as $offset => $value) {
                        $address[$offset][$field] = $value;
                    }
                } else {
                    $address[0][$field] = $content;
                }
            }

            $required_fields = array('first_name','last_name','line1','town','country','postcode');

            foreach ($address as $offset => $record) {
                foreach ($record as $record_key => $record_value) {
                    if (in_array($record_key, $required_fields) && empty($record_value)) {
                        $GLOBALS['main']->errorMessage($lang['common']['error_fields_required']);
                        continue 2;
                    }
                }

                // Format data nicely from mr barney brimstock to Mr Barney Brimstock & Post/Zip code to uppercase
                $record['title']  = ucwords($record['title']);
                $record['first_name'] = ucwords($record['first_name']);
                $record['last_name'] = ucwords($record['last_name']);
                $record['postcode']  = strtoupper($record['postcode']);

                // set all to non-default first so this becomes default!
                if ($record['default']=='1') {
                    $GLOBALS['db']->update('CubeCart_addressbook', array('default' => '0'), array('customer_id' => $customer_id, 'billing' => (string)$record['billing']));
                }
                if ($record['billing']=='1') {
                    $GLOBALS['db']->update('CubeCart_addressbook', array('billing' => '0'), array('customer_id' => $customer_id));
                }

                if (isset($record['address_id']) && !empty($record['address_id'])) {
                    if ($GLOBALS['db']->update('CubeCart_addressbook', $record, array('customer_id' => $customer_id, 'address_id' => $record['address_id']))) {
                        $customer_updated = true;
                    }
                } else {
                    if ($GLOBALS['db']->insert('CubeCart_addressbook', array_merge($record, array('customer_id' => (int)$customer_id)))) {
                        $customer_updated = true;
                    }
                }
            }
        }
    }

    foreach ($GLOBALS['hooks']->load('admin.customer.post_process') as $hook) {
        include $hook;
    }

    if ($customer_added) {
        $GLOBALS['main']->successMessage($lang['customer']['notify_customer_create']);
        $variable_rem_fields = array('action');
    } elseif ($customer_not_added) {
        $GLOBALS['main']->errorMessage($lang['customer']['error_customer_create']);
    } elseif ($customer_updated) {
        $GLOBALS['main']->successMessage($lang['customer']['notify_customer_update']);
        // Lose get vars to return to customer list
        $variable_rem_fields = array();
    } else {
        $GLOBALS['main']->errorMessage($lang['customer']['error_customer_update']);
    }

    $fixed_rem_fields = array('address_id');
    $rem_fields = is_array($variable_rem_fields) ? array_merge($fixed_rem_fields, $variable_rem_fields) : $fixed_rem_fields;
    httpredir(currentPage($rem_fields));
} else {
    if (isset($_POST['group_edit']) && Admin::getInstance()->permissions('customers', CC_PERM_EDIT)) {
        if (!empty($_POST['group_edit']) && is_array($_POST['group_edit'])) {
            foreach ($_POST['group_edit'] as $group_id => $data) {
                if (isset($data['name'])) {
                    $record['group_name'] = $data['name'];
                }
                if (isset($data['description'])) {
                    $record['group_description'] = $data['description'];
                }
                foreach ($GLOBALS['hooks']->load('admin.customer.group_edit') as $hook) {
                    include $hook;
                }
                if (isset($record)) {
                    $GLOBALS['db']->update('CubeCart_customer_group', $record, array('group_id' => (int)$group_id));
                    $GLOBALS['main']->successMessage($lang['customer']['notify_customer_groups']);
                    unset($record);
                }
            }
            $send_redirect = true;
        }
    }
    if (isset($_POST['group_add']) && Admin::getInstance()->permissions('customers', CC_PERM_EDIT)) {
        if (!empty($_POST['group_add']['group_name'])) {
            foreach ($GLOBALS['hooks']->load('admin.customer.group_add') as $hook) {
                include $hook;
            }
            if (($GLOBALS['db']->insert('CubeCart_customer_group', $_POST['group_add'])) !== false) {
                $GLOBALS['main']->successMessage($lang['customer']['notify_groups_create']);
            }
            $send_redirect = true;
        }
    }
    if (isset($_POST['group_delete']) && is_array($_POST['group_delete']) && Admin::getInstance()->permissions('customers', CC_PERM_DELETE)) {
        foreach ($_POST['group_delete'] as $group_id) {
            foreach ($GLOBALS['hooks']->load('admin.customer.group_delete') as $hook) {
                include $hook;
            }
            if (($GLOBALS['db']->delete('CubeCart_customer_group', array('group_id' => (int)$group_id))) !== false) {
                $GLOBALS['db']->delete('CubeCart_customer_membership', array('group_id' => (int)$group_id));
                $GLOBALS['db']->delete('CubeCart_pricing_quantity', array('group_id' => (int)$group_id));
                $GLOBALS['db']->delete('CubeCart_pricing_group', array('group_id' => (int)$group_id));
            }
        }
        $GLOBALS['main']->successMessage($lang['customer']['notify_groups_delete']);
        $send_redirect = true;
    }
    if (isset($_POST['status']) && is_array($_POST['status']) && Admin::getInstance()->permissions('customers', CC_PERM_EDIT)) {
        foreach ($_POST['status'] as $customer_id => $status) {
            // Delete any active session
            if((int)$status===0) $GLOBALS['db']->delete('CubeCart_sessions', array('customer_id' => (int)$customer_id));
            $result = $GLOBALS['db']->update('CubeCart_customer', array('status' => (int)$status), array('customer_id' => (int)$customer_id));
            if ($result) {
                $GLOBALS['main']->successMessage($lang['customer']['notify_customer_status']);
            }
        }
        $send_redirect = true;
    }
    if (isset($_GET['delete_addr']) && isset($_GET['customer_id']) && is_numeric($_GET['delete_addr'])) {
        if ($GLOBALS['db']->delete('CubeCart_addressbook', array('address_id' => (int)$_GET['delete_addr'], 'customer_id' => (int)$_GET['customer_id']))) {
            $GLOBALS['main']->successMessage($lang['customer']['notify_address_delete']);
        } else {
            $GLOBALS['main']->errorMessage($lang['customer']['error_address_delete']);
        }
        $send_redirect = true;
    }
    foreach ($GLOBALS['hooks']->load('admin.customer.customer_list_post') as $hook) {
        include $hook;
    }
    if (isset($send_redirect) && $send_redirect) {
        httpredir(currentPage(array('delete_addr')));
    }
}

######################################
$per_page = 20;

if (isset($_GET['action']) && Admin::getInstance()->permissions('customers', CC_PERM_EDIT)) {
    if ($_GET['action'] == 'signinas' && isset($_GET['customer_id']) && $_GET['customer_id']) {
        $GLOBALS['session']->delete('', 'basket');
        $GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => $_GET['customer_id']), array('session_id' => $GLOBALS['session']->getId()));
        httpredir('index.php');
        exit;
    }

    if ($_GET['action'] == 'delete' && isset($_GET['customer_id']) && Admin::getInstance()->permissions('customers', CC_PERM_DELETE)) {
        if (($customer = $GLOBALS['db']->select('CubeCart_customer', array('customer_id'), array('customer_id' => (int)$_GET['customer_id']))) !== false) {
            if (!$GLOBALS['db']->select('CubeCart_order_summary', array('cart_order_id'), array('customer_id' => $customer[0]['customer_id']))) {
                if (($GLOBALS['db']->delete('CubeCart_customer', array('customer_id' => $customer[0]['customer_id']))) !== false) {
                    $GLOBALS['db']->delete('CubeCart_addressbook', array('customer_id' => $customer[0]['customer_id']));
                    $GLOBALS['db']->delete('CubeCart_customer_membership', array('customer_id' => $customer[0]['customer_id']));
                    $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('customer_id' => $customer[0]['customer_id']));
                    foreach ($GLOBALS['hooks']->load('admin.customer.delete') as $hook) {
                        include $hook;
                    }
                    $GLOBALS['main']->successMessage($lang['customer']['notify_customer_delete']);
                } else {
                    $GLOBALS['main']->errorMessage($lang['customer']['error_customer_delete']);
                }
            } else {
                $GLOBALS['main']->errorMessage($lang['customer']['error_customer_delete_orders']);
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['customer']['error_customer_found']);
        }
        httpredir(currentPage(array('action', 'customer_id')));
    }

    $GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
    $GLOBALS['main']->addTabControl($lang['customer']['title_address'], 'address');

    if ($_GET['action'] == 'edit' && isset($_GET['customer_id']) && is_numeric($_GET['customer_id'])) {
        if (($customer = $GLOBALS['db']->select('CubeCart_customer', false, array('customer_id' => (int)$_GET['customer_id']))) !== false) {
            $no_orders = $GLOBALS['db']->count('CubeCart_order_summary', false, array('customer_id' => (int)$_GET['customer_id']));
            $GLOBALS['main']->addTabControl($lang['settings']['title_orders'], '', '?_g=orders&customer_id='.(int)$_GET['customer_id'], null, $no_orders);
            $GLOBALS['main']->addTabControl($lang['customer']['cookie_consent'], 'consent');
            $cookie_consent = $GLOBALS['db']->select('CubeCart_cookie_consent', false, array('customer_id' => (int)$_GET['customer_id']));
            $GLOBALS['smarty']->assign('COOKIE_CONSENT', $cookie_consent);

            $customer = $customer[0];

            $GLOBALS['main']->addTabControl('<i class="fa fa-sign-in"></i> '.sprintf($lang['customer']['signinas'], $customer['first_name'], $customer['last_name']), '', currentPage('', array('action' => 'signinas', 'customer_id' => $customer['customer_id'])), null, false, '_blank');

            $customer_id = (int)$customer['customer_id'];
            $GLOBALS['smarty']->assign('ADD_EDIT_CUSTOMER', $lang['customer']['title_customer_edit']);

            $GLOBALS['gui']->addBreadcrumb(sprintf('%s %s', $customer['first_name'], $customer['last_name']), currentPage(array('address_id')));

            if (isset($_GET['address_id']) && is_numeric($_GET['address_id'])) {
                if (($address = $GLOBALS['db']->select('CubeCart_addressbook', false, array('customer_id' => $customer_id, 'address_id' => (int)$_GET['address_id']))) !== false) {
                    $GLOBALS['gui']->addBreadcrumb($address[0]['description'], currentPage());
                    if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', array('id', 'numcode', 'name'), false, array('name' => 'ASC'))) !== false) {
                        $smarty_data = array();
                        foreach ($countries as $country) {
                            $array = array(
                                'selected' => ($country['numcode'] == $address[0]['country']) ? 'selected="selected"' : '',
                                'id'  => $country['numcode'],
                                'name'  => $country['name'],
                            );
                            $smarty_data['countries'][] = $array;
                        }
                        $GLOBALS['smarty']->assign('COUNTRIESL', $smarty_data['countries']);
                        $GLOBALS['smarty']->assign('JSON_STATE', state_json());
                    }
                    $GLOBALS['smarty']->assign('ADDRESS', $address[0]);
                }
                $GLOBALS['smarty']->assign('DISPLAY_ADDRESS_EDIT', true);
            } else {
                // Get Addresses
                if (($addresses = $GLOBALS['db']->select('CubeCart_addressbook', false, array('customer_id' => $customer_id))) !== false) {
                    foreach ($addresses as $address) {
                        $address['country_name'] = getCountryFormat($address['country']);
                        $address['state_name']  = getStateFormat($address['state']);
                        $address['edit']   = currentPage(null, array('address_id' => $address['address_id']));
                        $address['delete']   = currentPage(null, array('delete_addr' => $address['address_id'],'token' => SESSION_TOKEN));
                        $address['json']   = json_encode($address);
                        $smarty_data['list_address'][] = $address;
                    }
                    $GLOBALS['smarty']->assign('ADDRESS_LIST', $smarty_data['list_address']);
                }
                $GLOBALS['smarty']->assign('DISPLAY_ADDRESS_LIST', true);
            }
            // Get group memberships
            if (($memberships = $GLOBALS['db']->select('CubeCart_customer_membership', false, array('customer_id' => $customer_id))) !== false) {
                foreach ($GLOBALS['hooks']->load('admin.customer.group.get') as $hook) {
                    include $hook;
                }
                foreach ($memberships as $membership) {
                    $membership_list[$membership['membership_id']] = $membership;
                }
            }
            $customer['subscription_status'] = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', false, array('email' => $customer['email']));
        }
    } else {
        $GLOBALS['smarty']->assign('ADD_EDIT_CUSTOMER', $lang['customer']['title_customer_add']);
        $customer = (isset($_POST['customer']) && is_array($_POST['customer'])) ? $_POST['customer'] : array('subscription_status' => false);
        // address interface
        $GLOBALS['smarty']->assign('DISPLAY_ADDRESS_LIST', true);
    }

    if (($groups = $GLOBALS['db']->select('CubeCart_customer_group', false, false, array('group_name' => 'ASC'))) !== false) {
        $GLOBALS['smarty']->assign('ALL_CUSTOMER_GROUPS', $groups);
        foreach ($groups as $group) {
            $group_list[$group['group_id']] = $group;
        }
        if (isset($membership_list)) {
            foreach ($membership_list as $membership) {
                $membership['delete'] = currentPage(array('page'), array('group_id' => $membership['group_id']));
                $data = array_merge($membership, $group_list[$membership['group_id']]);
                $smarty_data['list_groups'][] = $data;
            }
            $GLOBALS['smarty']->assign('CUSTOMER_GROUPS', $smarty_data['list_groups']);
        }
        $GLOBALS['main']->addTabControl($lang['customer']['title_groups'], 'groups');
        $GLOBALS['smarty']->assign('DISPLAY_CUSTOMER_GROUPS', true);
    }
    
    $GLOBALS['smarty']->assign('CUSTOMER', $customer);
    foreach ($GLOBALS['hooks']->load('admin.customer.tabs') as $hook) {
        include $hook;
    }
    $GLOBALS['smarty']->assign('PLUGIN_TABS', $smarty_data['plugin_tabs']);
    $GLOBALS['smarty']->assign('DISPLAY_CUSTOMER_FORM', true);
} else {
    $GLOBALS['main']->addTabControl($lang['customer']['title_customer'], 'customer-list');
    $GLOBALS['main']->addTabControl($lang['customer']['title_groups'], 'customer-groups');
    $GLOBALS['main']->addTabControl($lang['customer']['title_customer_add'], null, currentPage(null, array('action' => 'add')));
    $GLOBALS['main']->addTabControl($lang['search']['title_search_customers'], 'sidebar');
    $GLOBALS['main']->addTabControl($lang['search']['gdpr_tools'], null, '?_g=customers&node=gdpr');

    $where = isset($_GET['q']) && !empty($_GET['q']) ? array('~'.(string)trim($_GET['q']) => array('email', "CONCAT(first_name ,' ',last_name)", "CONCAT(last_name ,' ',first_name)")) : false;

    $page = (isset($_GET['page'])) ? $_GET['page'] : 1;

    // Sorting
    if (!isset($_GET['sort']) || !is_array($_GET['sort'])) {
        $_GET['sort'] = array('registered' => 'DESC');
    }
    $current_page = currentPage(array('sort'));
    $thead_sort = array(
        'status'   => $GLOBALS['db']->column_sort('status', $lang['common']['status'], 'sort', $current_page, $_GET['sort']),
        'customer'   => $GLOBALS['db']->column_sort('customer', $lang['customer']['title_customer'], 'sort', $current_page, $_GET['sort']),
        'registered' => $GLOBALS['db']->column_sort('registered', $lang['customer']['date_registered'], 'sort', $current_page, $_GET['sort']),
        'type'   => $GLOBALS['db']->column_sort('type', $lang['customer']['customer_type'], 'sort', $current_page, $_GET['sort']),
        'email'   => $GLOBALS['db']->column_sort('email', $lang['common']['email'], 'sort', $current_page, $_GET['sort']),
        'no_orders'  => $GLOBALS['db']->column_sort('order_count', $lang['customer']['order_count'], 'sort', $current_page, $_GET['sort']),
    );

    foreach ($GLOBALS['hooks']->load('admin.customer.table_head_sort') as $hook) {
        include $hook;
    }

    $GLOBALS['smarty']->assign('THEAD', $thead_sort);
    $key = array_keys($_GET['sort']);
    $order_by = '`'.$key[0].'` '.$_GET['sort'][$key[0]];

    if (($customer_count = $GLOBALS['db']->select('CubeCart_customer', array('customer_id'), $where)) !== false) {
        $count = count($customer_count);
        $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($count, $per_page, $page));
    }

    if (($customers = $GLOBALS['db']->select('CubeCart_customer', '*, CONCAT(`last_name`, `first_name`) AS `customer`', $where, $order_by, $per_page, $page)) !== false) {
        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $GLOBALS['main']->successMessage(sprintf($lang['customer']['notify_customer_matching_x'], $_GET['q']));
        }
        foreach ($customers as $customer) {
            $orders = $GLOBALS['db']->select('CubeCart_order_summary', array('cart_order_id'), array('customer_id' => $customer['customer_id']));
            $customer['order_count'] = ($orders) ? count($orders) : 0;
            $customer['registered'] = formatTime($customer['registered']);
            $customer['signinas_url'] = currentPage(array('page'), array('action' => 'signinas', 'customer_id' => $customer['customer_id']));
            $customer['signinas_name'] = sprintf($lang['customer']['signinas'], $customer['first_name'], $customer['last_name']);
            $customer['edit'] = currentPage(array('page'), array('action' => 'edit', 'customer_id' => $customer['customer_id']));
            $customer['delete'] = currentPage(false, array('action' => 'delete', 'customer_id' => $customer['customer_id'],'token' => SESSION_TOKEN));
            $group_membership = $GLOBALS['db']->misc('SELECT `group_name` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_customer_membership` AS M INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_customer_group` AS G WHERE G.`group_id` = M.`group_id` AND M.`customer_id` = '.$customer['customer_id'].';');
            if (is_array($group_membership)) {
                foreach ($group_membership as $membership) {
                    $member_groups[] = $membership['group_name'];
                }
                $customer['groups'] = isset($member_groups) ? implode(',', $member_groups) : '';
            }
            foreach ($GLOBALS['hooks']->load('admin.customer.list') as $hook) {
                include $hook;
            }
            unset($group_membership, $member_groups);
            $customer_list[] = $customer;
        }
        $GLOBALS['smarty']->assign('CUSTOMERS', $customer_list);
    } elseif (isset($_GET['q'])) {
        $GLOBALS['main']->errorMessage(sprintf($lang['customer']['error_customer_matching_x'], $_GET['q']));
    }
    // Get external module export code
    // Start classes for external reports
    if (($module = $GLOBALS['db']->select('CubeCart_modules', 'folder', array('module' => 'external', 'status' => '1'))) !== false) {
        foreach ($module as $module_data) {
            if (file_exists(CC_ROOT_DIR.'/modules/external/'.$module_data['folder'])) {
                $module_data['description'] = ucfirst($module_data['folder']);
                $smarty_data['customers_export_list'][] = $module_data;
            }
        }
        $GLOBALS['smarty']->assign('CUSTOMER_EXPORT_LIST', $smarty_data['customers_export_list']);
    }
    // Get list of Customer Groups
    if (($groups = $GLOBALS['db']->select('CubeCart_customer_group', false, false, array('group_name' => 'ASC'))) !== false) {
        foreach ($GLOBALS['hooks']->load('admin.customer.group_list') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('CUSTOMER_GROUPS', $groups);
    }
    $GLOBALS['smarty']->assign('DISPLAY_LIST', true);
}

if (!isset($_GET['address_id'])): // avoid states double content by address edit

    if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', array('id', 'numcode', 'name'), false, array('name' => 'ASC'))) !== false) {
        $store_country = $GLOBALS['config']->get('config', 'store_country');
        foreach ($countries as $country) {
            $smarty_data['countries'][] = array(
                'selected' => ($country['numcode'] == $store_country) ? 'selected="selected"' : '',
                'id'  => $country['numcode'],
                'name'  => $country['name'],
            );
        }
        $GLOBALS['smarty']->assign('COUNTRIES', $smarty_data['countries']);
        $GLOBALS['smarty']->assign('JSON_STATE', state_json());
    }
endif;
$page_content = $GLOBALS['smarty']->fetch('templates/customers.index.php');
