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
Admin::getInstance()->permissions('orders', CC_PERM_READ, true);
$order = Order::getInstance();
global $lang;

// Correct OID format
if (isset($_GET['order_id']) && !preg_match('#^[0-9]{6}-[0-9]{6}-[0-9]{4}$#i', $_GET['order_id'])) {
    if ($std_oid = $GLOBALS['db']->select('CubeCart_order_summary', 'cart_order_id', array($GLOBALS['config']->get('config', 'oid_col') => $_GET['order_id']))) {
        $_GET['order_id'] = $std_oid[0]['cart_order_id'];
    }
}

if (isset($_POST['month_purge']) && ctype_digit($_POST['month_purge'])) {
    if ($purge_oids = $GLOBALS['db']->select('CubeCart_order_summary', "cart_order_id", "`order_date` < ".strtotime("-".(string)$_POST['month_purge']." month"))) {
        foreach ($purge_oids as $purge_oid) {
            $_POST['multi-order'][] = $purge_oid['cart_order_id'];
        }
        $_POST['multi-action'] = 'delete';
    }
}

if (isset($_POST['search']) && !is_array($_POST['multi-order'])) {
    httpredir('?_g=orders&'.http_build_query($_POST));
}

if (isset($_GET['reset_id']) && $_GET['reset_id']>0 && Admin::getInstance()->permissions('orders', CC_PERM_EDIT)) {
    $download_expire = $GLOBALS['config']->get('config', 'download_expire');
    $new_time = ($download_expire > 0) ? time()+(int)$download_expire : 0;
    $GLOBALS['db']->update('CubeCart_downloads', array('downloads' => 0, 'expire' => $new_time), array('order_inv_id' => (int)$_GET['reset_id']));
    $GLOBALS['main']->successMessage($lang['orders']['notify_order_update']);
    httpredir(currentPage(array('reset_id')));
}

if (isset($_GET['delete_card']) && $_GET['delete_card'] && Admin::getInstance()->permissions('orders', CC_PERM_EDIT)) {
    if ($order->deleteCard($_GET['order_id'])) {
        $GLOBALS['main']->successMessage($lang['orders']['notify_card_delete']);
    } else {
        $GLOBALS['main']->errorMessage($lang['orders']['error_card_delete']);
    }
    httpredir(currentPage(array('delete_card')));
}

if (isset($_GET['delete']) && !empty($_GET['delete']) && Admin::getInstance()->permissions('orders', CC_PERM_DELETE)) {
    if ($order->deleteOrder($_GET['delete'])) {
        $GLOBALS['main']->successMessage($lang['orders']['notify_order_delete']);
    } else {
        $GLOBALS['main']->errorMessage($lang['orders']['error_order_delete']);
    }
    if (isset($_GET['source']) && $_GET['source']=='dashboard') {
        httpredir($glob['adminFile'], 'orders');
    } else {
        httpredir(currentPage(array('delete')));
    }
}

if (isset($_POST['cart_order_id']) && Admin::getInstance()->permissions('orders', CC_PERM_EDIT)) {
    $order_id = (!empty($_POST['cart_order_id'])) ? $_POST['cart_order_id'] : $order->createOrderId(true);
    // Hook
    foreach ($GLOBALS['hooks']->load('admin.order.index.pre_process') as $hook) {
        include $hook;
    }

    // Inventory Management
    // Remove products
    if (isset($_POST['inv_remove']) && is_array($_POST['inv_remove']) && Admin::getInstance()->permissions('orders', CC_PERM_DELETE)) {
        foreach ($_POST['inv_remove'] as $value) {
            $GLOBALS['db']->delete('CubeCart_order_inventory', array('cart_order_id' => $order_id, 'id' => (int)$value));
        }
    }
    // Add products
    if (isset($_POST['inv_add']) && is_array($_POST['inv_add'])) {
        foreach ($_POST['inv_add'] as $data) {
            $record = array(
                'product_id' => (int)$data['product_id'],
                'quantity'  => $data['product_quantity'],
                'price'   => $data['price'],
                'cart_order_id' => $order_id,
                'options_identifier' => $GLOBALS['catalogue']->defineOptionsIdentifier($data['productOptions']),
                'options_array' => serialize($data['productOptions']),
                'product_options' => $GLOBALS['order']->serializeOptions($data['productOptions'], $data['product_id'])
            );
            if (!empty($data['product_id']) && is_numeric($data['product_id'])) {
                // Get product data
                if (($product = $GLOBALS['db']->select('CubeCart_inventory', false, array('product_id' => $data['product_id']))) !== false) {
                    $record = array_merge($product[0], $record);
                }
            } else {
                $record['name']   = $data['product'];
            }

            $order_inv_id = $GLOBALS['db']->insert('CubeCart_order_inventory', $record);

            if (isset($record['digital']) && $record['digital']) {
                $GLOBALS['order']->createDownload((int)$data['product_id'], (int)$order_inv_id, (int)$_POST['customer']['customer_id'], $order_id);
            }

            unset($record);
        }
    }
    // Update Products
    if (isset($_POST['inv']) && is_array($_POST['inv'])) {
        foreach ($_POST['inv'] as $data) {
            $data['options_identifier'] = $GLOBALS['catalogue']->defineOptionsIdentifier($data['productOptions']);
            // Check for matrix entry that may affect product data
            $matrix = $GLOBALS['db']->select('CubeCart_option_matrix', false, array('product_id' => (int)$data['product_id'], 'options_identifier' => $data['options_identifier']));
            if ($matrix) {
                $data['product_code'] = (empty($matrix[0]['product_code']) ? $data['product_code'] : $matrix[0]['product_code']);
            }
            $data['options_array'] 		= serialize($data['productOptions']);
            $data['product_options'] 	= $GLOBALS['order']->serializeOptions($data['productOptions'], $data['product_id']);
            if (is_array($data['custom'])) {
                $data['custom'] = serialize($data['custom']);
            }
            $GLOBALS['db']->update('CubeCart_order_inventory', $data, array('cart_order_id' => $order_id, 'id' => (int)$data['id']));
        }
    }
    // Tax Management
    // Remove Taxes
    if (isset($_POST['tax_remove']) && is_array($_POST['tax_remove'])) {
        foreach ($_POST['tax_remove'] as $tax_id) {
            $GLOBALS['db']->delete('CubeCart_order_tax', array('id' => (int)$tax_id));
        }
    }
    // Add Taxes
    if (isset($_POST['tax_add']) && is_array($_POST['tax_add'])) {
        foreach ($_POST['tax_add'] as $data) {
            $record = array(
                'cart_order_id' => $order_id,
                'tax_id'  => (int)$data['tax_id'],
                'amount'  => $data['amount'],
            );
            $GLOBALS['db']->insert('CubeCart_order_tax', $record);
            unset($record);
        }
    }
    // Update Taxes
    if (isset($_POST['tax']) && is_array($_POST['tax'])) {
        foreach ($_POST['tax'] as $tax_id => $amount) {
            $GLOBALS['db']->update('CubeCart_order_tax', array('amount' => $amount), array('cart_order_id' => $order_id, 'id' => (int)$tax_id));
        }
    }
    // Order Summary data
    $record = array(
        'cart_order_id' => $order_id,
        'dashboard'  => (isset($_POST['dashboard'])) ? (int)$_POST['dashboard'] : false,
        'discount_type' => $_POST['summary']['discount_type'],
    );

    $customer_data = $_POST['customer'];
    if (isset($_POST['customer']['customer_id']) && !empty($_POST['customer']['customer_id'])) {
        if (($customer = $GLOBALS['db']->select('CubeCart_customer', array('customer_id', 'title', 'first_name', 'last_name'), array('customer_id' => (int)$_POST['customer']['customer_id']))) !== false) {
            $customer_data = array_merge($customer[0], $_POST['customer']);
        }
    }
    if ($_POST['summary']['discount_type']=='p') {
        $_POST['summary']['discount'] = $_POST['summary']['subtotal']*($_POST['summary']['discount']*0.01);
    }

    $record = array_merge($customer_data, $_POST['summary'], $record);

    // Add a new note, if there's any content
    if (!empty($_POST['note'])) {
        $note = array(
            'admin_id'  => Admin::getInstance()->get('admin_id'),
            'cart_order_id' => $order_id,
            'content'  => strip_tags($_POST['note']),
            'time'  => time()
        );
        if ($GLOBALS['db']->insert('CubeCart_order_notes', $note)) {
            $notes_added = true;
        }
    }

    if (empty($_POST['cart_order_id'])) {
        // Create order record
        $record['order_date'] = time();
        if ($GLOBALS['db']->insert('CubeCart_order_summary', $record)) {
            $GLOBALS['main']->successMessage($lang['orders']['notify_order_create']);
        } else {
            $GLOBALS['main']->errorMessage($lang['orders']['error_order_create']);
        }
        // Update order status, if set
        $order->orderStatus($_POST['order']['status'], $order_id, true);
    } else {
        if ($_POST['order']['status']==3 && empty($_POST['summary']['ship_date'])) {
            $record['ship_date'] = date('Y-m-d');
        }

        // Update/create summary
        $update_status = $GLOBALS['db']->update('CubeCart_order_summary', $record, array('cart_order_id' => $order_id), true, array('phone', 'mobile'));
        // Update order status, if set
        //$order_status = $order->orderStatus($_POST['order']['status'], $order_id, true);
        $order_status = $order->orderStatus($_POST['order']['status'], $order_id);

        if ($update_status || $order_status || $notes_added) {
            $GLOBALS['main']->successMessage($lang['orders']['notify_order_update']);
        } else {
            $GLOBALS['main']->errorMessage($lang['orders']['error_order_update']);
        }
    }

    // Hook
    foreach ($GLOBALS['hooks']->load('admin.order.index.post_process') as $hook) {
        include $hook;
    }

    if (isset($_POST['submit_cont'])) {
        httpredir(currentPage(array('source'), array('action' => 'edit', 'order_id' => $order_id)));
    } elseif (isset($_GET['source']) && $_GET['source']=='dashboard') {
        httpredir($glob['adminFile'], 'orders');
    } else {
        httpredir(currentPage(array('action', 'order_id')));
    }
}

if (isset($_GET['delete-note']) && isset($_GET['order_id'])) {
    $GLOBALS['db']->delete('CubeCart_order_notes', array('cart_order_id' => $_GET['order_id'], 'note_id' => $_GET['delete-note']));
    httpredir(currentPage(array('delete-note', 'print_hash')), 'notes');
}

$tax = Tax::getInstance();

foreach ($GLOBALS['hooks']->load('admin.order.index.pre_display') as $hook) {
    include $hook;
}

if (isset($_GET['action'])) {
    // Register tabs
    $GLOBALS['main']->addTabControl($lang['orders']['tab_overview'], 'order_summary');
    $GLOBALS['main']->addTabControl($lang['orders']['tab_billing'], 'order_billing');
    $GLOBALS['main']->addTabControl($lang['orders']['tab_delivery'], 'order_delivery');
    $GLOBALS['main']->addTabControl($lang['orders']['tab_inventory'], 'order_inventory');

    $smarty_data = array();
    $smarty_data['plugin_tabs'] = array();

    if (isset($_GET['order_id'])) {
        $GLOBALS['main']->addTabControl($lang['orders']['tab_history'], 'order_history');
        /*! Order History */
        if (($order_history = $GLOBALS['db']->select('CubeCart_order_history', false, array('cart_order_id' => $_GET['order_id']), array('updated' => 'DESC'))) !== false) {
            foreach ($order_history as $event) {
                $event['updated']  = formatTime($event['updated']);
                $event['status']  = $lang['order_state']['name_'.$event['status']];
                $event['initiator'] = $lang['order_state']['initiator_'.$event['initiator']]; // New line for custom initiators
                $smarty_data['list_history'][] = $event;
            }
            $GLOBALS['smarty']->assign('LIST_HISTORY', $smarty_data['list_history']);
        }
    }

    // Get tax rates
    if (($tax_rates = $GLOBALS['db']->select('CubeCart_tax_rates', false, array('active' => 1), array('country_id' => 'ASC'))) !== false) {
        if (($tax_types = $GLOBALS['db']->select('CubeCart_tax_class')) !== false) {
            foreach ($tax_types as $tax_type) {
                $types[$tax_type['id']] = array('type_name' => $tax_type['tax_name']);
            }
        }
        if (($tax_details = $GLOBALS['db']->select('CubeCart_tax_details')) !== false) {
            foreach ($tax_details as $tax_detail) {
                $detail[(int)$tax_detail['id']] = $tax_detail;
            }
            $tax_by_country = array();
            foreach ($tax_rates as $tax_rate) {
                $data = array_merge($types[$tax_rate['type_id']], $detail[$tax_rate['details_id']], $tax_rate);
                $rates[$tax_rate['id']] = $data;
                $tax_by_country[$tax_rate['country_id']][] = $data;
            }
        }
        if (is_array($tax_by_country)) {
            foreach ($tax_by_country as $numcode => $taxes) {
                $county = ($taxes[0]['county_id']>0) ? getStateFormat($taxes[0]['county_id']) : 'All';
                $country = getCountryFormat($numcode).' ('.$county.')';
                $smarty_data['select_tax'][$country] = $taxes;
            }
            $GLOBALS['smarty']->assign('SELECT_TAX', $smarty_data['select_tax']);
        }
    }
    if (in_array($_GET['action'], array('add', 'edit'))) {
        // Load order summary
        if (isset($_GET['order_id']) && ($summary = $GLOBALS['db']->select('CubeCart_order_summary', false, array('cart_order_id' => $_GET['order_id']))) !== false) {
        
            // Seek weight from basket data for orders placed before 6.0.9
            if ((float)$summary[0]['weight'] == 0) {
                $basket_array = unserialize($summary[0]['basket']);
                $summary[0]['weight'] = $basket_array['weight'];
            }
            $GLOBALS['smarty']->assign('WEIGHT_UNIT', $GLOBALS['config']->get('config', 'product_weight_unit'));

            // Make some values frendlier
            $summary[0]['ship_method']   = str_replace('_', ' ', $summary[0]['ship_method']);
            $summary[0]['gateway']   = str_replace('_', ' ', $summary[0]['gateway']);
            $summary[0]['ship_date']   = ((int)(str_replace('-', '', $summary[0]['ship_date'])) > 0) ? $summary[0]['ship_date'] : "";

            // Processing/Pending orders are on the dashboard by default otherwise show defined value
            if ($summary[0]['discount_type']=='p') {
                $summary[0]['discount_form'] = number_format(($summary[0]['discount']/$summary[0]['subtotal'])*100, 2);
            } else {
                $summary[0]['discount_form'] = number_format($summary[0]['discount'], 2);
            }

            $GLOBALS['smarty']->assign('SUMMARY', $summary[0]);
            if ($summary[0]['status'] >= 3) {
                $GLOBALS['smarty']->assign('DISPLAY_DASHBOARD', true);
            }

            $GLOBALS['gui']->addBreadcrumb($GLOBALS['config']->get('config', 'oid_mode') == 'i' ? $summary[0][$GLOBALS['config']->get('config', 'oid_col')] : $summary[0]['cart_order_id'], currentPage(array('print_hash')));
            // Load order inventory
            if (($inventory = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $summary[0]['cart_order_id']))) !== false) {
                $subtotal = 0;
                foreach ($inventory as $product) {
                    $subtotal += ($product['price']*$product['quantity']);
                    $product['line'] = $product['price'];
                    $price_total = $product['price']*$product['quantity'];
                    $product['price_total'] = number_format($price_total, 2);

                    $product['line_formatted'] = Tax::getInstance()->priceFormat($product['price']);
                    $product['price_total_formatted'] = Tax::getInstance()->priceFormat($price_total);

                    $product['options'] = Catalogue::getInstance()->displayProductOptions($product['product_id'], unserialize($product['options_array']));

                    $product['options_array'] = false;
                    if (!empty($product['product_options']) && preg_match('/^a:[0-9]/', $product['product_options'])) {
                        $product['options_array'] = cc_unserialize($product['product_options']);
                        $product['options_text'] = implode('<br>', cc_unserialize($product['product_options']));
                    } elseif (!empty($product['product_options'])) {
                        $product['options_text'] = $product['product_options'];
                    }

                    $custom_data = array();
                    
                    if (!empty($product['custom'])) {
                        if ($custom_data = unserialize($product['custom'])) {
                            if (is_array($custom_data)) {
                                foreach ($custom_data as $key => $value) {
                                    if ($key == 'method') {
                                        switch ($value) {
                                            case 'e':
                                                $value = $GLOBALS['language']->common['email'];
                                            break;
                                            case 'm':
                                                $value = $GLOBALS['language']->common['postal'];
                                            break;
                                        }
                                    } elseif ($key == 'value') {
                                        $value = $GLOBALS['tax']->priceFormat($value);
                                    }
                                    $custom_data_string[$key] = $value;
                                }
                                $product['custom'] = $custom_data_string;
                            }
                        } else {
                            $product['custom'] = '';
                        }
                    }
                
                    $product['line_price_less_options'] = sprintf("%.2F", $product['line']-Catalogue::getInstance()->getOptionsLinePrice());

                    if ($product['digital']) {
                        $digital_data = $GLOBALS['db']->select('CubeCart_downloads', array('accesskey', 'downloads', 'expire'), array('cart_order_id' => $summary[0]['cart_order_id'], 'product_id' => $product['product_id'], 'customer_id' => $summary[0]['customer_id']));
                        if ($digital_data && !empty($digital_data[0]['accesskey'])) {
                            $product['accesskey'] = $digital_data[0]['accesskey'];
                            $product['downloads'] = $digital_data[0]['downloads'];
                            $product['expire'] = ($digital_data[0]['expire']==0) ? $GLOBALS['language']->common['never'] : formatTime($digital_data[0]['expire']);
                            $product['expired'] = ($digital_data[0]['downloads'] >= $GLOBALS['config']->get('config', 'download_count') || (time() > $digital_data[0]['expire'] && $digital_data[0]['expire']>0)) ? true : false;
                        } else {
                            $product['accesskey'] = false;
                        }
                    } else {
                        $product['accesskey'] = false;
                    }
                    
                    $smarty_data['products'][] = $product;
                }
                $GLOBALS['smarty']->assign('PRODUCTS', $smarty_data['products']);
                $GLOBALS['smarty']->assign('SUBTOTAL', number_format($subtotal, 2));
            }
            // Assign summary to overview
            $overview_summary = $summary[0];

            $overview_summary['percent'] = '';
            if ($overview_summary['discount_type'] == 'p') {
                $overview_summary['percent'] = number_format(($overview_summary['discount']/$overview_summary['subtotal'])*100, 2) . '%';
            } elseif ($overview_summary['discount_type'] == 'pp') {
                $overview_summary['percent'] = number_format(($overview_summary['discount']/($overview_summary['subtotal']+$overview_summary['discount']))*100, 2) . '%';
            }

            $overview_summary['name']  = (isset($summary[0]['name']) && !empty($summary[0]['name'])) ? $summary[0]['name'] : $summary[0]['first_name'].' '.$summary[0]['last_name'];
            $overview_summary['name_d']  = (isset($summary[0]['name_d']) && !empty($summary[0]['name_d'])) ? $summary[0]['name_d'] : $summary[0]['first_name_d'].' '.$summary[0]['last_name_d'];
            $overview_summary['ship_date']  = $overview_summary['ship_date'] ? formatDispatchDate($overview_summary['ship_date']) : "";
            $overview_summary['discount']  = $GLOBALS['tax']->priceFormat($overview_summary['discount']);
            $overview_summary['subtotal']  = $GLOBALS['tax']->priceFormat($overview_summary['subtotal']);
            $overview_summary['shipping']  = $GLOBALS['tax']->priceFormat($overview_summary['shipping']);
            $overview_summary['total_tax']  = $GLOBALS['tax']->priceFormat($overview_summary['total_tax']);
            $overview_summary['total']   = $GLOBALS['tax']->priceFormat($overview_summary['total']);
            $overview_summary['country_d'] = is_numeric($overview_summary['country_d']) ? getCountryFormat($overview_summary['country_d'], 'numcode', 'name') : $overview_summary['country_d'];
            $overview_summary['country'] = is_numeric($overview_summary['country']) ? getCountryFormat($overview_summary['country'], 'numcode', 'name') : $overview_summary['country'];
            $overview_summary['state_d'] = is_numeric($overview_summary['state_d']) ? getStateFormat($overview_summary['state_d']) : $overview_summary['state_d'];
            $overview_summary['state']  = is_numeric($overview_summary['state']) ? getStateFormat($overview_summary['state']) : $overview_summary['state'];
            $overview_summary_taxes   = $GLOBALS['db']->select('CubeCart_order_tax', array('tax_id', 'amount'), array('cart_order_id' => $_GET['order_id']));
            if ($overview_summary_taxes) {
                foreach ($overview_summary_taxes as $overview_tax) {
                    $tax_data = $GLOBALS['tax']->fetchTaxDetails($overview_tax['tax_id']);
                    $overview_summary_tax['tax_name'] = $tax_data['display'];
                    $overview_summary_tax['tax_amount'] = $GLOBALS['tax']->priceFormat($overview_tax['amount']);
                    $smarty_data['tax_summary'][] = $overview_summary_tax;
                }
                $GLOBALS['smarty']->assign('TAX_SUMMARY', $smarty_data['tax_summary']);
            }

            $overview_summary['order_date']  = formatTime($overview_summary['order_date'], false, true);

            $GLOBALS['smarty']->assign('OVERVIEW_SUMMARY', $overview_summary);
            // Show the customer comments
            if (!empty($overview_summary['customer_comments'])) {
                $GLOBALS['smarty']->assign('DISPLAY_COMMENTS', true);
            }
            unset($overview_summary);
            $GLOBALS['smarty']->assign('DISPLAY_OVERVIEW', true);

            // Load transaction details, if any
            if (($transactions = $GLOBALS['db']->select('CubeCart_transactions', false, array('order_id' => $summary[0]['cart_order_id']), array('time' => 'DESC'))) !== false) {
                $GLOBALS['main']->addTabControl($lang['orders']['title_transaction_logs'], 'order_transactions');
                foreach ($transactions as $transaction) {
                    foreach ($GLOBALS['hooks']->load('admin.order.index.transaction') as $hook) {
                        include $hook;
                    }
                    // Display transactions for this order
                    $transaction['status'] = empty($transaction['status']) ? $GLOBALS['lang']['common']['null'] : $transaction['status'];
                    $transaction['time'] = formatTime($transaction['time']);
                    $transaction['amount'] = Tax::getInstance()->priceFormat($transaction['amount']);
                    $smarty_data['list_transactions'][] = $transaction;
                    if (isset($transaction['actions'])) {
                        $GLOBALS['smarty']->assign('DISPLAY_ACTIONS', true);
                    }
                }
                $GLOBALS['smarty']->assign('TRANSACTIONS', $smarty_data['list_transactions']);
                $GLOBALS['smarty']->assign('DISPLAY_TRANSACTIONS', true);
            }
            // Load credit card details, if any
            if (!empty($summary[0]['offline_capture'])) {
                $GLOBALS['main']->addTabControl($lang['orders']['title_card_details'], 'credit_card');
                $decrypt = Encryption::getInstance();
                $decrypt->setup(false, $summary[0]['cart_order_id']);
                $card = unserialize($decrypt->decrypt(stripslashes($summary[0]['offline_capture'])));
                
                if (empty($card)) {
                    // Decrypt CC3/CC4 Card Details
                    $card = unserialize($decrypt->decryptDepreciated(base64_decode($summary[0]['offline_capture']), $summary[0]['cart_order_id']));
                }

                $card = (!empty($card)) ? $card : array('card_type' => '', 'card_number' => '', 'card_expire' => '', 'card_valid' => '', 'card_issue' => '', 'card_cvv' => '');
                foreach ($card as $key => $value) {
                    $smarty_data['card_data'][$key] = array(
                        'name' => $lang['orders']['card_'.$key],
                        'value' => (CC_SSL) ? $value : $lang['gateway']['card_view_ssl'],
                    );
                }
                $GLOBALS['smarty']->assign('CARD_DATA', $smarty_data['card_data']);

                $GLOBALS['smarty']->assign('CARD_DELETE', '?_g=orders&action=edit&order_id='.$summary[0]['cart_order_id']."&delete_card=1#credit_card");
                $GLOBALS['smarty']->assign('DISPLAY_CARD', true);
            }
            // Load addresses
            if (($addresses = $GLOBALS['db']->select('CubeCart_addressbook', false, array('customer_id' => $summary[0]['customer_id']))) !== false) {
                foreach ($addresses as $key => $address) {
                    $address['country_name'] = getCountryFormat($address['country']);
                    $address['description'] = empty($address['description']) ? $address['line1'].', '.$address['postcode'] : $address['description'];
                    $address['key']    = $key;
                    $smarty_data['list_address'][] = $address;
                }
                $GLOBALS['smarty']->assign('LIST_ADDRESS', $smarty_data['list_address']);
                $GLOBALS['smarty']->assign('ADDRESS_JSON', json_encode($addresses));
            }
            // Taxes
            if (($taxes = $GLOBALS['db']->select('CubeCart_order_tax', false, array('cart_order_id' => $summary[0]['cart_order_id']))) !== false) {
                foreach ($taxes as $tax) {
                    $tax['display']  = $rates[$tax['tax_id']]['display'];
                    $tax['type_name'] = $rates[$tax['tax_id']]['type_name'];
                    $smarty_data['list_taxes'][] = $tax;
                }
                $GLOBALS['smarty']->assign('LIST_TAXES', $smarty_data['list_taxes']);
            }
        } else {
            $_POST['summary'] = (isset($_POST['summary'])) ? $_POST['summary'] : array();
            $_POST['customer'] = (isset($_POST['customer'])) ? $_POST['customer'] : array();
            $summary[0] = array_merge($_POST['summary'], $_POST['customer']);
            $GLOBALS['smarty']->assign('SUMMARY', $summary[0]);
        }

        if (($admins = $GLOBALS['db']->select('CubeCart_admin_users', array('name', 'admin_id'))) !== false) {
            foreach ($admins as $admin) {
                $author[$admin['admin_id']] = $admin['name'];
            }
        }
        $_GET['order_id'] = (isset($_GET['order_id'])) ? $_GET['order_id'] : '';
        $notes = $GLOBALS['db']->select('CubeCart_order_notes', false, array('cart_order_id' => $_GET['order_id']), array('time' => 'ASC'));
        $no_notes = $notes ? count($notes) : false;
        $GLOBALS['main']->addTabControl($lang['common']['notes'], 'order_notes', null, null, $no_notes);
        if ($notes) {
            foreach ($notes as $note) {
                $note['time']  = formatTime($note['time']);
                $note['author']  = $author[$note['admin_id']];
                $note['delete']  = currentPage(array('print_hash'), array('delete-note' => $note['note_id'], 'token' => SESSION_TOKEN));
                $note['content'] = strip_tags($note['content']);
                $smarty_data['list_notes'][] = $note;
            }
            $GLOBALS['smarty']->assign('LIST_NOTES', $smarty_data['list_notes']);
        }
    }

    for ($i = 1; $i <= 6; ++$i) {
        $smarty_data['order_status'][] = array(
            'id'  => $i,
            'selected' => (isset($summary[0]) && isset($summary[0]['status']) && (int)$summary[0]['status'] === $i) ? ' selected="selected"' : '',
            'string' => $lang['order_state']['name_'.$i],
        );
    }
    $GLOBALS['smarty']->assign('LIST_ORDER_STATUS', $smarty_data['order_status']);
    if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', false, false, array('name' => 'ASC'))) !== false) {
        $store_country = $GLOBALS['config']->get('config', 'store_country');
        foreach ($countries as $country) {
            $country['is_billing'] = (isset($summary[0]) && isset($summary[0]['country']) && $country['numcode'] == $summary[0]['country']) ? ' selected="selected"' : '';
            $country['selected'] = (!isset($summary[0]) || !isset($summary[0]['country']) && $country['numcode'] == $store_country) ? ' selected="selected"' : '';
            $country['is_delivery'] = (isset($summary[0]) && isset($summary[0]['country_d']) && $country['numcode'] == $summary[0]['country_d']) ? ' selected="selected"' : '';
            $smarty_data['list_country'][] = $country;
        }
        $GLOBALS['smarty']->assign('LIST_COUNTRY', $smarty_data['list_country']);
        $GLOBALS['smarty']->assign('STATE_JSON', state_json());
    }
    // Hook
    foreach ($GLOBALS['hooks']->load('admin.order.index.display') as $hook) {
        include $hook;
    }
    $GLOBALS['smarty']->assign('PLUGIN_TABS', $smarty_data['plugin_tabs']);
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
} elseif (isset($_GET['print']) && !empty($_GET['print'])) {
    // Generate a printable page, and display it
    // Made somewhat trickier by the way the templating system works
    // so we'll generate the page, stick it in the cache folder, trigger the print, then delete the file
    foreach ($_GET['print'] as $order_id) {
        $order_list[] = "'".$order_id."'";
    }

    if (($summaries = $GLOBALS['db']->select('CubeCart_order_summary', false, array('cart_order_id' => $order_list))) !== false) {
        foreach ($summaries as $key => $summary) {
            $summary['raw'] = $summary;
            $GLOBALS['smarty']->assign('PAGE_TITLE', (count($_GET['print'])>1) ? $lang['orders']['title_invoices'] : sprintf($lang['orders']['title_invoice_x'], $summary['cart_order_id']));
            if (($inventory = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $summary['cart_order_id']))) !== false) {
                foreach ($inventory as $item) {
                    $item['raw'] = $item;
                    $item['item_price'] = Tax::getInstance()->priceFormat($item['price'], true);
                    $item['price'] = Tax::getInstance()->priceFormat(($item['price']*$item['quantity']), true);
                    if (!empty($item['product_options'])) {
                        $options = ($array = cc_unserialize($item['product_options'])) ? $array : explode("\n", $item['product_options']);
                        foreach ($options as $option) {
                            $value = trim($option);
                            if (empty($value)) {
                                continue;
                            }
                            $item['options'][] = $option;
                        }
                    }
                    $summary['items'][] = $item;
                }
            }
            // Taxes
            if (($taxes = $GLOBALS['db']->select('CubeCart_order_tax', false, array('cart_order_id' => $summary['cart_order_id']))) !== false) {
                $GLOBALS['tax']->loadTaxes($summary['country']);
                foreach ($taxes as $vat) {
                    $detail = Tax::getInstance()->fetchTaxDetails($vat['tax_id']);
                    $summary['taxes'][] = array('name' => $detail['name'], 'value' => Tax::getInstance()->priceFormat($vat['amount'], true));
                }
            } else {
                $summary['taxes'][] = array('name' => $lang['basket']['total_tax'], 'value' => Tax::getInstance()->priceFormat($summary['total_tax']));
            }
            // Price Formatting
            $summary['percent'] = '';
            if ($summary['discount_type'] == 'p') {
                $summary['percent'] = number_format(($summary['discount']/$summary['subtotal'])*100) . '%';
            } elseif ($summary['discount_type'] == 'pp') {
                $summary['percent'] = number_format(($summary['discount']/($summary['subtotal']+$summary['discount']))*100) . '%';
            }
            $format = array('discount', 'shipping', 'subtotal', 'total_tax', 'total');
            foreach ($format as $field) {
                if (isset($summary[$field])) {
                    $summary[$field] = Tax::getInstance()->priceFormat($summary[$field]);
                }
            }
            $summary['state_d'] = (is_numeric($summary['state_d'])) ? getStateFormat($summary['state_d']) : $summary['state_d'];
            $summary['state'] = (is_numeric($summary['state'])) ? getStateFormat($summary['state']) : $summary['state'];
            $summary['country'] = getCountryFormat($summary['country']);
            $summary['country_d'] = getCountryFormat($summary['country_d']);
            $summary['order_date'] = formatTime($summary['order_date'], false, true);
            $summary['ship_date'] = ((int)(str_replace('-', '', $summary['ship_date'])) > 0) ? formatDispatchDate($summary['ship_date']) : '';
            $summary['weight'] = (float)$summary['weight'];

            if (($notes = $GLOBALS['db']->select('CubeCart_order_notes', false, array('cart_order_id' => $summary['cart_order_id']))) !== false) {
                foreach ($notes as $key => $note) {
                    $summary['notes'][] = $note['content'].'<br />';
                }
            }

            $smarty_data['list_orders'][] = $summary;
            unset($summary, $address);
        }
        $GLOBALS['smarty']->assign('ORDER_LIST', $smarty_data['list_orders']);

        $store_logo = $GLOBALS['gui']->getLogo(true, 'invoices');
        $GLOBALS['smarty']->assign('STORE_LOGO', $store_logo);
        $GLOBALS['smarty']->assign(
                'STORE',
                array(
                    'name' => $GLOBALS['config']->get('config', 'store_name'),
                    'address' => $GLOBALS['config']->get('config', 'store_address'),
                    'county' => getStateFormat($GLOBALS['config']->get('config', 'store_zone')),
                    'country' => getCountryFormat($GLOBALS['config']->get('config', 'store_country')),
                    'postcode' => $GLOBALS['config']->get('config', 'store_postcode'))
            );

        foreach ($GLOBALS['hooks']->load('admin.order.index.print') as $hook) {
            include $hook;
        }

        // Parse
        $invoice_template = $GLOBALS['db']->select('CubeCart_invoice_template', array('hash', 'content'), false, 'id DESC', 1);
        if ($invoice_template && !empty($invoice_template[0]['content'])) {
            $fetch_source = 'string:'.$invoice_template[0]['content'];
        } else {
            $fetch_source = 'templates/orders.print.php';
        }
        $template = $GLOBALS['smarty']->fetch($fetch_source);

        $print_hash = md5(implode('{@}', $summaries[0]));

        $cleanup = '<?php unlink(__FILE__); ?>';
        $filename = 'print.'.$print_hash.'.php';

        if (file_put_contents(CC_FILES_DIR.$filename, $template.$cleanup)) {
            httpredir($GLOBALS['storeURL'].'/'.basename(CC_FILES_DIR).'/'.$filename);
        } else {
            $GLOBALS['main']->errorMessage($lang['orders']['error_print_generate']);
            httpredir(currentPage(array('print')));
        }
    } else {
        $GLOBALS['main']->errorMessage($lang['orders']['order_not_found']);
        httpredir(currentPage(array('print')));
    }
} else {
    if (isset($_POST['multi-order']) && !empty($_POST['multi-order'])) {
        // Update selected orders to given status
        $order = Order::getInstance();
        // An admin is working on this so lets NOT send out email notifications
        //$order->disableAdminEmail();

        $updated = false;
        $deleted = false;
        $add_array = array();

        foreach ($_POST['multi-order'] as $order_id) {
            // If multi action variable is numeric we need to update the order status
            if (!empty($_POST['multi-status'])) {
                // Update disptach date
                if ((int)$_POST['multi-status']==3) {
                    $GLOBALS['db']->update('CubeCart_order_summary', array('ship_date' => date('Y-m-d')), array('cart_order_id' => $order_id));
                }

                if ($order->orderStatus((int)$_POST['multi-status'], $order_id)) {
                    $updated = true;
                }
            }
            switch ($_POST['multi-action']) {
            case 'print':
                $add_array['print'][] = $order_id;
                break;
            case 'delete':
                if ($order->deleteOrder($order_id)) {
                    $deleted = true;
                }
                break;
            }
        }
        if ($_POST['multi-action'] == 'delete') {
            if ($deleted) {
                if (isset($_POST['month_purge']) && ctype_digit($_POST['month_purge'])>0) {
                    $GLOBALS['main']->successMessage(sprintf($lang['orders']['notify_orders_purged'], $_POST['month_purge']));
                } else {
                    $GLOBALS['main']->successMessage($lang['orders']['notify_orders_delete']);
                }
            } else {
                if (isset($_POST['month_purge']) && ctype_digit($_POST['month_purge'])>0) {
                    $GLOBALS['main']->errorMessage($lang['orders']['error_orders_purged']);
                } else {
                    $GLOBALS['main']->errorMessage($lang['orders']['error_orders_delete']);
                }
            }
        }
        if ($updated) {
            $GLOBALS['main']->successMessage($lang['orders']['notify_orders_status']);
        }
        if (isset($_GET['redirect']) && $_GET['redirect'] == 'dashboard' && $_POST['multi-action'] == '') {
            httpredir('?', 'orders');
        } else {
            httpredir(currentPage(array('print_hash', 'multi-action'), $add_array));
        }
    } elseif (isset($_GET['search'])) {

            // Search by date range
        if (isset($_GET['search']['date']) && is_array($_GET['search']['date']) && (!empty($_GET['search']['date']['from']) || !empty($_GET['search']['date']['to']))) {
            foreach ($_GET['search']['date'] as $key => $date) {
                $dates[$key] = (!empty($date)) ? strtotime($date) : null;
            }
            if ((!empty($dates['from']) && !empty($dates['to'])) && $dates['from'] == $dates['to']) {
                $where[] = "order_date = '".$dates['from']."'";
            } else {
                if (!empty($dates['from'])) {
                    $where[] = "order_date >= '".$dates['from']."'";
                }
                if (!empty($dates['to'])) {
                    $where[] = "order_date <= '".$dates['to']."'";
                }
            }
            if (isset($where) && is_array($where)) {
                $where = implode(' AND ', $where);
            }
        } else {
            // Order ID
            if (isset($_GET['search']['order_number']) && !empty($_GET['search']['order_number'])) {
                if ($GLOBALS['config']->get('config', 'oid_mode')=='i') {
                    $where[$GLOBALS['config']->get('config', 'oid_col')] = $_GET['search']['order_number'];
                } else {
                    $where['cart_order_id'] = '~'.$_GET['search']['order_number'];
                }
            }
            // Order Status
            if (isset($_GET['search']['status']) && is_numeric($_GET['search']['status'])) {
                $where['status'] = (int)$_GET['search']['status'];
            }
            // Customer ID
            if (isset($_GET['search']['search_customer_id']) && is_numeric($_GET['search']['search_customer_id'])) {
                $where['customer_id'] = (int)$_GET['search']['search_customer_id'];
            }
        }
    } else {
        $where = (isset($_GET['customer_id']) && is_numeric($_GET['customer_id'])) ? array('customer_id' => (int)$_GET['customer_id']) : false;
    }
    $where = (isset($where) && !empty($where)) ? $where : false;

    for ($i = 1;$i <= 6; ++$i) {
        $smarty_data['order_status'][] = array(
            'id'  => $i,
            'selected' => (isset($_GET['search']['status']) && $i == $_GET['search']['status']) ? ' selected="selected"' : '',
            'string' => $lang['order_state']['name_'.$i],
        );
    }
    $GLOBALS['smarty']->assign('LIST_ORDER_STATUS', $smarty_data['order_status']);

    $GLOBALS['main']->addTabControl($lang['orders']['tab_orders_overview'], 'orders', null, 'O');
    $GLOBALS['main']->addTabControl($lang['orders']['tab_orders_search'], 'search', null, 'S');
    $GLOBALS['main']->addTabControl($lang['orders']['tab_orders_create'], null, currentPage(array('print_hash'), array('action' => 'add')), 'N');
    $GLOBALS['main']->addTabControl($lang['search']['gdpr_tools'], 'gdpr');

    $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
    $per_page = 20;

    if ((!isset($_GET['sort']) || !is_array($_GET['sort'])) && !isset($_GET['action'])) {
        $_GET['sort'] = array('order_date' => 'DESC');
    }
    $current_page = currentPage(array('sort'));
    $thead_sort = array(
        'cart_order_id' => $GLOBALS['db']->column_sort('cart_order_id', $lang['orders']['order_number'], 'sort', $current_page, $_GET['sort']),
        'customer'   => $GLOBALS['db']->column_sort('customer', $lang['orders']['title_customer'], 'sort', $current_page, $_GET['sort']),
        'status'   => $GLOBALS['db']->column_sort('status', $lang['common']['status'], 'sort', $current_page, $_GET['sort']),
        'date'    => $GLOBALS['db']->column_sort('order_date', $lang['common']['date'], 'sort', $current_page, $_GET['sort']),
        'total'   => $GLOBALS['db']->column_sort('total', $lang['basket']['total'], 'sort', $current_page, $_GET['sort'])
    );

    foreach ($GLOBALS['hooks']->load('admin.order.index.table_head_sort') as $hook) {
        include $hook;
    }

    $GLOBALS['smarty']->assign('THEAD', $thead_sort);
    // Sort has to be a string in this instance as column 'customer' doesn't exist!!
    $key   = array_keys($_GET['sort']);
    $order_by  = '`'.$key[0].'` '.$_GET['sort'][$key[0]];
    $orders  = $GLOBALS['db']->select(sprintf('`%1$sCubeCart_order_summary` LEFT JOIN `%1$sCubeCart_customer` ON %1$sCubeCart_order_summary.customer_id = %1$sCubeCart_customer.customer_id', $GLOBALS['config']->get('config', 'dbprefix')), sprintf('%1$sCubeCart_order_summary.*, %1$sCubeCart_customer.type, CONCAT(%1$sCubeCart_order_summary.last_name, %1$sCubeCart_order_summary.first_name) AS `customer`, %1$sCubeCart_order_summary.status', $GLOBALS['config']->get('config', 'dbprefix')), $where, $order_by, $per_page, $page);

    if ($orders) {
        $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination(false, $per_page, $page, 9));

        if (isset($_GET['customer_id'])) {
            $GLOBALS['main']->successMessage(sprintf($lang['orders']['notify_orders_by'], $orders[0]['first_name'], $orders[0]['last_name']));
        }
        if (isset($_GET['search'])) {
            $GLOBALS['main']->successMessage($lang['orders']['notify_search_result']);
        }

        foreach ($orders as $order) {
            $order['name']   = (isset($order['name']) && !empty($order['name'])) ? $order['name'] : sprintf('%s %s %s', $order['title'], $order['first_name'], $order['last_name']);
            $order['icon']   = ($order['type']==2 || empty($order['customer_id'])) ? 'user_ghost' : 'user_registered';// deprecated since 6.1.5
            $order['type'] = (empty($order['customer_id'])) ? 2 : $order['type'];
            $order['cust_type'] = array("1" => 'title_key_registered', "2" => 'title_key_unregistered');
            $order['link_edit']  = currentPage(array('print_hash'), array('action' => 'edit', 'order_id' => $order['cart_order_id']));
            $order['link_customer'] = ($order['customer_id']) ? "?_g=customers&action=edit&customer_id=".$order['customer_id'] : "#";
            $order['link_delete'] = currentPage(array('print_hash'), array('delete' => $order['cart_order_id'], 'token' => SESSION_TOKEN));
            // Link needs to be an array with one key
            $order['link_print'] = currentPage(array('print_hash'), array('print[0]' => $order['cart_order_id']));
            $order['status_class']  = 'order_status_'.$order['status'];
            $order['status']  = $lang['order_state']['name_'.$order['status']];
            $order['date']   = formatTime($order['order_date']);
            $order['prod_total'] = Tax::getInstance()->priceFormat($order['total']);

            $smarty_data['list_orders'][] = $order;
        }
        $GLOBALS['smarty']->assign('ORDER_LIST', $smarty_data['list_orders']);
    } elseif (isset($_GET['search']) && !empty($_POST['month_purge'])) {
        # No orders found
        $GLOBALS['main']->errorMessage($lang['orders']['error_search_result']);
    }
    $GLOBALS['smarty']->assign('DISPLAY_ORDER_LIST', true);
    // Hook
    foreach ($GLOBALS['hooks']->load('admin.order.index.list') as $hook) {
        include $hook;
    }
}
$template_file = (isset($_GET['print']) && !empty($_GET['print'])) ? 'orders.print' : 'orders.index';
$page_content = $GLOBALS['smarty']->fetch('templates/'.$template_file.'.php');
