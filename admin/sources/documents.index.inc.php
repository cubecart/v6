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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('documents', CC_PERM_READ, true);


if (isset($_POST['document']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    foreach ($GLOBALS['hooks']->load('admin.documents.save.pre_process') as $hook) {
        include $hook;
    }
    $action = isset($_GET['action']) ? strtolower($_GET['action']) : '';
    ## Check for existing translations
    if ($action === 'translate' && $duplicates = $GLOBALS['db']->select('CubeCart_documents', array('doc_id'), array('doc_lang' => $_POST['document']['doc_lang'], 'doc_parent_id' => (int)$_POST['document']['doc_parent_id']))) {
        $_POST['document']['doc_id'] = $duplicates[0]['doc_id'];
    }
    ## Do the database magic
    $rem_array = array();
    $_POST['document']['doc_content'] = $GLOBALS['RAW']['POST']['document']['doc_content'];
    if (isset($_POST['document']['doc_id']) && is_numeric($_POST['document']['doc_id'])) {
        $doc_id = (int)$_POST['document']['doc_id'];
        $GLOBALS['db']->update('CubeCart_documents', $_POST['document'], array('doc_id' => $doc_id), true);
        $doc_changed = $GLOBALS['db']->affected() > 0;
        if(empty($_POST['seo_path'])) {
            $GLOBALS['seo']->unsetdbPath('doc', $doc_id);
        }
        $GLOBALS['seo']->setdbPath('doc', $doc_id, $_POST['seo_path'], true, true);
        if ($doc_changed) {
            $GLOBALS['db']->update('CubeCart_documents', array('updated' => date('Y-m-d H:i:s')), array('doc_id' => $doc_id));
            $GLOBALS['main']->successMessage($lang['documents']['notify_document_update']);
            $rem_array = array('action','doc_id');
        } else {
            $GLOBALS['main']->errorMessage($lang['documents']['error_document_update']);
        }
    } else {
        $_POST['document']['date_added'] = date('Y-m-d H:i:s');
        if ($doc_id = $GLOBALS['db']->insert('CubeCart_documents', $_POST['document'])) {
            $GLOBALS['seo']->setdbPath('doc', $doc_id, $_POST['seo_path']);
            $GLOBALS['main']->successMessage($lang['documents']['notify_document_create']);
            $rem_array = array('action','doc_id');
        } else {
            $GLOBALS['main']->errorMessage($lang['documents']['error_document_create']);
        }
    }
    foreach ($GLOBALS['hooks']->load('admin.documents.save.post_process') as $hook) {
        include $hook;
    }
    httpredir(currentPage($rem_array));
}

if (isset($_POST['terms']) || isset($_POST['home']) || isset($_POST['order']) || isset($_POST['status'])) {
    if (Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
        foreach ($GLOBALS['hooks']->load('admin.documents.status') as $hook) {
            include $hook;
        }
        $updated = false;
        $docs = array();
        if (isset($_POST['terms']) && ctype_digit($_POST['terms'])) { ## Set document as terms & conditions
            $docs[] = array('key' => 'terms', 'id' => $_POST['terms']);
        }
        if (isset($_POST['home']) && ctype_digit($_POST['home'])) { ## Set doument as homepage
            $docs[] = array('key' => 'home', 'id' => $_POST['home']);
        }

        if (count($docs)>0) {
            foreach ($docs as $doc) {
                $target_id = (int)$doc['id'];
                $GLOBALS['db']->update('CubeCart_documents', array('doc_'.$doc['key'] => 1), array('doc_id' => $target_id, 'doc_parent_id' => 0), true);
                // Clear the flag on every other parent doc (translations live under
                // doc_parent_id > 0 and shouldn't be touched by this list-level toggle).
                $GLOBALS['db']->update('CubeCart_documents', array('doc_'.$doc['key'] => 0), 'doc_parent_id = 0 AND doc_id <> '.$target_id);
                if ($GLOBALS['db']->affected() > 0) {
                    $GLOBALS['main']->successMessage($lang['documents']['notify_document_'.$doc['key']]);
                    $updated = true;
                }
            }
        }

        ## Set document ordering
        if (isset($_POST['order']) && is_array($_POST['order'])) {
            $order_updated = false;
            foreach ($_POST['order'] as $doc_order => $doc_id) {
                $GLOBALS['db']->update('CubeCart_documents', array('doc_order' => (int)$doc_order), array('doc_id' => (int)$doc_id));
                if ($GLOBALS['db']->affected() > 0) {
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
                $clamped = ((int)$status === 1) ? 1 : 0;
                $GLOBALS['db']->update('CubeCart_documents', array('doc_status' => $clamped), array('doc_id' => (int)$doc_id));
                if ($GLOBALS['db']->affected() > 0) {
                    $status_updated = true;
                }
            }
            if ($status_updated) {
                $GLOBALS['main']->successMessage($lang['documents']['notify_document_status']);
            }
        }
        ## If no changes have been made let administrator know
        if (!$updated && !$status_updated && !$order_updated) {
            $GLOBALS['main']->errorMessage($lang['common']['error_no_changes']);
        }
        httpredir(currentPage());
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete']) && isset($_GET['token']) && $_GET['token'] === SESSION_TOKEN) {
    foreach ($GLOBALS['hooks']->load('admin.documents.delete') as $hook) {
        include $hook;
    }
    if (Admin::getInstance()->permissions('documents', CC_PERM_DELETE)) {
        $delete_id = (int)$_GET['delete'];
        $GLOBALS['db']->delete('CubeCart_documents', array('doc_parent_id' => $delete_id));
        $GLOBALS['db']->delete('CubeCart_documents', array('doc_id' => $delete_id));
        $GLOBALS['seo']->delete('doc', $delete_id);
        $GLOBALS['main']->successMessage($lang['documents']['notify_document_delete']);
    } else {
        $GLOBALS['main']->errorMessage($lang['documents']['error_document_delete']);
    }
    httpredir(currentPage(array('delete', 'token')));
}

###############################################
if (isset($_GET['action'])) {
    $smarty_data = array();
    foreach ($GLOBALS['hooks']->load('admin.documents.pre_display') as $hook) {
        include $hook;
    }

    $GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
    $GLOBALS['main']->addTabControl($lang['documents']['tab_content'], 'article');
    $GLOBALS['main']->addTabControl($lang['settings']['tab_seo'], 'seo');
    $action = strtolower($_GET['action']);
    $doc_id = (isset($_GET['doc_id']) && is_numeric($_GET['doc_id'])) ? (int)$_GET['doc_id'] : 0;
    $GLOBALS['smarty']->assign("REDIRECTS", $GLOBALS['seo']->getRedirects('doc', $doc_id));
    if (in_array($action, array('edit', 'translate'), true) && $doc_id > 0) {

        // Check to see if translation space is available
        if ($action === 'translate' && $GLOBALS['language']->fullyTranslated('document', $doc_id)) {
            $GLOBALS['main']->errorMessage($lang['common']['all_translated']);
            httpredir('?_g=documents');
        }

        $GLOBALS['smarty']->assign('ADD_EDIT_DOCUMENT', $action === 'translate' ? $lang['documents']['document_translate'] : $lang['documents']['document_edit']);
        if (($document = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_id' => $doc_id))) !== false) {
            $data = $document[0];
            if ($action === 'translate') {
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
    ## Generate language list. On translate, mark codes already in use as disabled
    ## so the merchant can see them but can't pick a duplicate. The doc being
    ## translated has its own row in CubeCart_documents (parent + children share the
    ## same parent_id once a translation row is added).
    $used = array();
    if ($action === 'translate' && $doc_id > 0) {
        $parent_id = isset($document[0]['doc_parent_id']) && (int)$document[0]['doc_parent_id'] > 0
            ? (int)$document[0]['doc_parent_id']
            : $doc_id;
        $siblings = $GLOBALS['db']->select(
            'CubeCart_documents',
            array('doc_id', 'doc_lang'),
            '`doc_id` = '.$parent_id.' OR `doc_parent_id` = '.$parent_id
        );
        if ($siblings) {
            foreach ($siblings as $row) {
                // Don't mark the doc currently being edited as used.
                if ((int)$row['doc_id'] === $doc_id) continue;
                if (!empty($row['doc_lang'])) {
                    $used[$row['doc_lang']] = true;
                }
            }
        }
    }
    if (($languages = $GLOBALS['language']->listLanguages()) !== false) {
        foreach ($languages as $option) {
            if ($action === 'translate' && $option['code'] == $GLOBALS['config']->get('config', 'default_language')) {
                continue;
            }

            $option['selected'] = ((isset($document[0]['doc_lang']) && $option['code'] == $document[0]['doc_lang']) || (!isset($document[0]['doc_lang']) && $option['code']==$GLOBALS['config']->get('config', 'default_language'))) ? ' selected="selected"' : '';
            $option['disabled'] = isset($used[$option['code']]) ? ' disabled="disabled"' : '';
            $smarty_data['languages'][] = $option;
        }
        $GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['languages']);
    }

    $select_options = array('doc_url_openin' => array($lang['documents']['document_url_open_same'], $lang['documents']['document_url_open_new']));
    $smarty_data['targets'] = array();
    foreach ($select_options as $field => $options) {
        foreach ($options as $value => $title) {
            $selected = (isset($data[$field]) && $data[$field] == $value) ? ' selected="selected"' : '';
            $smarty_data['targets'][] = array('value' => $value, 'title' => $title, 'selected' => $selected);
        }
    }
    $GLOBALS['smarty']->assign('TARGETS', $smarty_data['targets']);
    $data['seo_path'] = isset($data['doc_id']) ? $GLOBALS['seo']->getdbPath('doc', $data['doc_id']) : '';
    if (!isset($data['navigation_link'])) {
        $data['navigation_link'] = 1;
    }
    $GLOBALS['smarty']->assign('DOCUMENT', $data);
    foreach ($GLOBALS['hooks']->load('admin.documents.tabs') as $hook) {
        include $hook;
    }
    $GLOBALS['smarty']->assign('PLUGIN_TABS', ($smarty_data['plugin_tabs'] ?? false));
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
} else {
    $smarty_data = array('documents' => array());
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
            $document['fully_translated'] = $GLOBALS['language']->fullyTranslated('document', $document['doc_id']);
            $smarty_data['documents'][] = $document;
        }
        $GLOBALS['smarty']->assign('DOCUMENTS', $smarty_data['documents']);
    }
    $GLOBALS['smarty']->assign('DISPLAY_DOCUMENT_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/documents.index.php');
