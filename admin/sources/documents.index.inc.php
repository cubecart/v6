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
Admin::getInstance()->permissions('documents', CC_PERM_READ, true);

global $lang;

if (isset($_POST['document']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    foreach ($GLOBALS['hooks']->load('admin.documents.save.pre_process') as $hook) {
        include $hook;
    }
    ## Check for existing translations
    if ($_GET['action']=='translate' && $duplicates = $GLOBALS['db']->select('CubeCart_documents', array('doc_id'), array('doc_lang' => $_POST['document']['doc_lang'], 'doc_parent_id' => (int)$_POST['document']['doc_parent_id']))) {
        $_POST['document']['doc_id'] = $duplicates[0]['doc_id'];
    }
    ## Do the database magic
    $rem_array = null;
    $_POST['document']['doc_content'] = $GLOBALS['RAW']['POST']['document']['doc_content'];
    if (isset($_POST['document']['doc_id']) && is_numeric($_POST['document']['doc_id'])) {
        if ($GLOBALS['db']->update('CubeCart_documents', $_POST['document'], array('doc_id' => $_POST['document']['doc_id']), true)) {
            if (empty($_POST['seo_path'])) {
                $GLOBALS['seo']->delete('doc', $_POST['document']['doc_id']);
            }
            $GLOBALS['seo']->setdbPath('doc', $_POST['document']['doc_id'], $_POST['seo_path'], true, true);
            $GLOBALS['main']->successMessage($lang['documents']['notify_document_update']);
            $rem_array = array('action');
        } else {
            $GLOBALS['main']->errorMessage($lang['documents']['error_document_update']);
        }
    } else {
        if ($GLOBALS['db']->insert('CubeCart_documents', $_POST['document'])) {
            $doc_id = $GLOBALS['db']->insertid();
            $GLOBALS['seo']->setdbPath('doc', $doc_id, $_POST['seo_path']);
            $GLOBALS['main']->successMessage($lang['documents']['notify_document_create']);
            $rem_array = array('action');
        } else {
            $GLOBALS['main']->errorMessage($lang['documents']['error_document_create']);
        }
    }
    foreach ($GLOBALS['hooks']->load('admin.documents.save.post_process') as $hook) {
        include $hook;
    }
    httpredir(currentPage($rem_array));
}

if (isset($_POST['privacy']) ||isset($_POST['terms']) || isset($_POST['home']) || isset($_POST['order']) || isset($_POST['status'])) {
    if (Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
        foreach ($GLOBALS['hooks']->load('admin.documents.status') as $hook) {
            include $hook;
        }
        $updated = false;
        $docs = array();
        if (isset($_POST['privacy']) && ctype_digit($_POST['privacy'])) { ## Set document as privacy
            $docs[] = array('key' => 'privacy', 'id' => $_POST['privacy']);
        }
        if (isset($_POST['terms']) && ctype_digit($_POST['terms'])) { ## Set document as terms & conditions
            $docs[] = array('key' => 'terms', 'id' => $_POST['terms']);
        }
        if (isset($_POST['home']) && ctype_digit($_POST['home'])) { ## Set doument as homepage
            $docs[] = array('key' => 'home', 'id' => $_POST['home']);
        }

        if (count($docs)>0) {
            foreach ($docs as $doc) {
                $document = $GLOBALS['db']->select('CubeCart_documents', array('doc_name'), array('doc_id' => $doc['id']));
                if ($GLOBALS['db']->update('CubeCart_documents', array('doc_'.$doc['key'] => 1), array('doc_id' => $doc['id'], 'doc_parent_id' => 0), true)) {
                    $GLOBALS['main']->successMessage($lang['documents']['notify_document_'.$doc['key']]);
                    $updated = true;
                }
                $GLOBALS['db']->update('CubeCart_documents', array('doc_'.$doc['key'] => 0), 'doc_id <> '.$doc['id']);
            }
        }

        ## Set document ordering
        if (isset($_POST['order']) && is_array($_POST['order'])) {
            $order_updated = false;
            foreach ($_POST['order'] as $doc_order => $doc_id) {
                if ($GLOBALS['db']->update('CubeCart_documents', array('doc_order' => (int)$doc_order), array('doc_id' => (int)$doc_id))) {
                    $order_updated = true;
                }
            }
            if ($order_updated) {
                $GLOBALS['main']->successMessage($lang['documents']['notify_document_arrange']);
            }
        }
        ## Set document statuses
        if (isset($_POST['status']) && is_array($_POST['status'])) {
            $status_updated = false;
            foreach ($_POST['status'] as $doc_id => $status) {
                if ($GLOBALS['db']->update('CubeCart_documents', array('doc_status' => (int)$status), array('doc_id' => (int)$doc_id))) {
                    $status_updated = true;
                }
            }
            if ($status_updated) {
                $GLOBALS['main']->successMessage($lang['documents']['notify_document_status']);
            }
        }
        ## If no changes have been made let administrator know
        if (!$updated && !$status_updated && !$order_updated) {
            $GLOBALS['main']->errorMessage($lang['common']['notify_no_changes']);
        }
        httpredir(currentPage());
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    foreach ($GLOBALS['hooks']->load('admin.documents.delete') as $hook) {
        include $hook;
    }
    if (Admin::getInstance()->permissions('documents', CC_PERM_DELETE)) {
        ## Load from db, and assign
        $document = $GLOBALS['db']->select('CubeCart_documents', array('doc_name'), array('doc_id' => $_GET['delete']));
        $GLOBALS['db']->delete('CubeCart_documents', array('doc_parent_id' => $_GET['delete']));
        $GLOBALS['db']->delete('CubeCart_documents', array('doc_id' => $_GET['delete']));
        $GLOBALS['seo']->delete('doc', $_GET['delete']);
        $GLOBALS['main']->successMessage($lang['documents']['notify_document_delete']);
    } else {
        $GLOBALS['main']->errorMessage($lang['documents']['error_document_delete']);
    }
    httpredir(currentPage(array('delete')));
}

###############################################
if (isset($_GET['action'])) {
    foreach ($GLOBALS['hooks']->load('admin.documents.pre_display') as $hook) {
        include $hook;
    }

    $GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
    $GLOBALS['main']->addTabControl($lang['documents']['tab_content'], 'article');
    $GLOBALS['main']->addTabControl($lang['settings']['tab_seo'], 'seo');
    if (strtolower($_GET['action']) == ('edit' || 'translate') && isset($_GET['doc_id']) && is_numeric($_GET['doc_id'])) {

        // Check to see if translation space is available
        if ($_GET['action'] == 'translate' && $GLOBALS['language']->fullyTranslated('document', $_GET['doc_id'])) {
            $GLOBALS['main']->errorMessage($lang['common']['all_translated']);
            httpredir('?_g=documents');
        }

        $GLOBALS['smarty']->assign('ADD_EDIT_DOCUMENT', $_GET['action'] == 'translate' ? $lang['documents']['document_translate'] : $lang['documents']['document_edit']);
        if (($document = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_id' => (int)$_GET['doc_id']))) !== false) {
            $data = $document[0];
            if (strtolower($_GET['action']) == 'translate') {
                $data['doc_parent_id'] = $document[0]['doc_parent_id'] = $document[0]['doc_id'];
                unset($data['doc_id']);
            } else {
                $data['link']['delete'] = currentPage(array('doc_id', 'action'), array('delete' => $data['doc_id'], 'token' => SESSION_TOKEN));
                $GLOBALS['smarty']->assign('DISPLAY_DELETE', true);
            }
            $GLOBALS['gui']->addBreadcrumb($data['doc_name'], currentPage());
        }
    } else {
        $GLOBALS['smarty']->assign('ADD_EDIT_DOCUMENT', $lang['documents']['document_create']);
        $data = array();
    }
    ## Generate language list
    if (($languages = $GLOBALS['language']->listLanguages()) !== false) {
        foreach ($languages as $option) {
            if ($_GET['action']=='translate' && $option['code'] == $GLOBALS['config']->get('config', 'default_language')) {
                continue;
            }

            $option['selected'] = ((isset($document[0]['doc_lang']) && $option['code'] == $document[0]['doc_lang']) || (!isset($document[0]['doc_lang']) && $option['code']==$GLOBALS['config']->get('config', 'default_language'))) ? ' selected="selected"' : '';
            $smarty_data['languages'][] = $option;
        }
        $GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['languages']);
    }

    $select_options = array('doc_url_openin' => array($lang['documents']['document_url_open_same'], $lang['documents']['document_url_open_new']));
    if (isset($select_options)) {
        foreach ($select_options as $field => $options) {
            if (!is_array($options) || empty($options)) {
                $options = array($lang['common']['no'], $lang['common']['yes']);
            }
            foreach ($options as $value => $title) {
                $selected = (isset($data[$field]) && $data[$field] == $value) ? ' selected="selected"' : '';
                $smarty_data['targets'][] = array('value' => $value, 'title' => $title, 'selected' => $selected);
            }
        }
        $GLOBALS['smarty']->assign('TARGETS', $smarty_data['targets']);
    }
    $data['seo_path'] = isset($data['doc_id']) ? $GLOBALS['seo']->getdbPath('doc', $data['doc_id']) : '';
    if (!isset($data['navigation_link'])) {
        $data['navigation_link'] = 1;
    }
    $GLOBALS['smarty']->assign('DOCUMENT', $data);
    foreach ($GLOBALS['hooks']->load('admin.documents.tabs') as $hook) {
        include $hook;
    }
    $GLOBALS['smarty']->assign('PLUGIN_TABS', $smarty_data['plugin_tabs']);
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
} else {
    $GLOBALS['main']->addTabControl($lang['common']['overview'], 'overview');
    $GLOBALS['main']->addTabControl($lang['documents']['document_create'], null, currentPage(array('doc_id'), array('action' => 'add')));
    $GLOBALS['main']->addTabControl($lang['orders']['invoice_editor'], '', '?_g=documents&node=invoice');
    ## List all documents
    if (($documents = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_parent_id' => 0), array('doc_order' => 'ASC'))) !== false) {
        foreach ($documents as $document) {
            ## Check for translations
            if (($translations = $GLOBALS['db']->select('CubeCart_documents', array('doc_lang', 'doc_id'), array('doc_parent_id' => $document['doc_id']), array('doc_lang' => 'ASC'))) !== false) {
                foreach ($translations as $translation) {
                    ## Display translation icons
                    $translation['link'] = array(
                        'edit' => currentPage(null, array('action' => 'edit', 'doc_id' => $translation['doc_id'])),
                    );
                    if (empty($translation['doc_lang'])) {
                        $translation['doc_lang'] = 'unknown';
                    }
                    $document['translations'][] = $translation;
                }
            }
            $document['link'] = array(
                'translate' => currentPage(null, array('action' => 'translate', 'doc_id' => $document['doc_id'])),
                'edit'  => currentPage(null, array('action' => 'edit', 'doc_id' => $document['doc_id'])),
                'delete' => currentPage(null, array('delete' => $document['doc_id'], 'token' => SESSION_TOKEN))
            );
            $document['flag']	= file_exists('language/flags/'.$document['doc_lang'].'.png') ? 'language/flags/'.$document['doc_lang'].'.png' : 'language/flags/unknown.png';
            $document['terms']  = ($document['doc_terms']) ? 'checked="checked"' : '';
            $document['homepage'] = ($document['doc_home']) ? 'checked="checked"' : '';
            $document['privacy'] = ($document['doc_privacy']) ? 'checked="checked"' : '';
            $smarty_data['documents'][] = $document;
        }
        $GLOBALS['smarty']->assign('DOCUMENTS', $smarty_data['documents']);
    }
    $GLOBALS['smarty']->assign('DISPLAY_DOCUMENT_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/documents.index.php');
