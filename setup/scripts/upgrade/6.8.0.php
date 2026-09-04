<?php
/**
 * #4243 — Move the Contact Us page into CubeCart_documents so it can be
 * translated like any other document.
 *
 * The content, SEO meta and status come out of the Contact_Form config rows and
 * become a document flagged doc_contact. Department names move onto the document
 * as well, keyed by their existing index, so each translation can name them
 * itself; the key to email mapping stays in config because a recipient address
 * is not language specific.
 *
 * The store's existing contact-us SEO row is repointed at the new document
 * rather than replaced, so the live URL keeps working and no redirect is needed.
 * Later translations then seed their own paths from their own doc_name.
 */

$prefix = $glob['dbprefix'];

// Nothing to do if a contact document already exists (re-run of the upgrade).
$existing = $db->select('CubeCart_documents', array('doc_id'), array('doc_contact' => 1), false, 1, false, false);
if ($existing) {
    trigger_error('#4243: contact document already present, skipping migration.', E_USER_NOTICE);
} else {
    $contact = array();
    if (($rows = $db->select('CubeCart_config', array('config_key', 'config_value'), array('name' => 'Contact_Form'), false, false, false, false)) !== false) {
        foreach ($rows as $row) {
            $contact[$row['config_key']] = $row['config_value'];
        }
    }

    // description is stored base64 encoded by the old contact form editor.
    $content = isset($contact['description']) ? base64_decode($contact['description']) : '';
    if ($content === false) {
        $content = '';
    }

    // Department names, keyed so the email mapping left in config still matches.
    // Config stores arrays as JSON, so decode it that way rather than unserialise.
    $departments = array();
    if (!empty($contact['department'])) {
        $stored = json_decode($contact['department'], true);
        if (is_array($stored)) {
            foreach ($stored as $key => $dept) {
                if (isset($dept['name']) && $dept['name'] !== '') {
                    $departments[$key] = $dept['name'];
                }
            }
        }
    }

    $default_language = $db->select('CubeCart_config', array('config_value'), array('name' => 'config', 'config_key' => 'default_language'), false, 1, false, false);
    $doc_lang = ($default_language) ? $default_language[0]['config_value'] : 'en-GB';

    // Sit it after the existing documents rather than jumping the running order.
    $last = $db->misc("SELECT MAX(`doc_order`) AS `m` FROM `".$prefix."CubeCart_documents`", false);
    $doc_order = (is_array($last) && isset($last[0]['m'])) ? ((int)$last[0]['m'] + 1) : 1;

    $document = array(
        'doc_name'             => 'Contact Us',
        'doc_content'          => $content,
        'doc_departments'      => !empty($departments) ? json_encode($departments) : null,
        'doc_lang'             => $doc_lang,
        'doc_order'            => $doc_order,
        'doc_status'           => isset($contact['status']) ? (int)$contact['status'] : 1,
        'doc_parse'            => isset($contact['parse']) ? (int)$contact['parse'] : 0,
        'doc_contact'          => 1,
        'doc_home'             => 0,
        'doc_terms'            => 0,
        'navigation_link'      => 1,
        'seo_meta_title'       => $contact['seo_meta_title'] ?? '',
        'seo_meta_description' => $contact['seo_meta_description'] ?? '',
        'date_added'           => date('Y-m-d H:i:s'),
        'updated'              => date('Y-m-d H:i:s'),
    );

    if (($doc_id = $db->insert('CubeCart_documents', $document)) !== false) {
        // Repoint the static contact routes at the document, including the
        // legacy .html redirect, so existing links resolve to the new page.
        $db->update(
            'CubeCart_seo_urls',
            array('type' => 'doc', 'item_id' => (int)$doc_id),
            array('type' => 'contact')
        );

        // The content now lives on the document. The remaining Contact_Form keys
        // (email, phone, attachments, department emails) are still read.
        $db->delete('CubeCart_config', array('name' => 'Contact_Form', 'config_key' => 'description'));
        $db->delete('CubeCart_config', array('name' => 'Contact_Form', 'config_key' => 'seo_meta_title'));
        $db->delete('CubeCart_config', array('name' => 'Contact_Form', 'config_key' => 'seo_meta_description'));
    } else {
        trigger_error('#4243: failed to create the contact document; Contact_Form left untouched.', E_USER_WARNING);
    }
}

// The contact form is edited with its document now, so the old admin page has
// gone. Upgrades overlay new files rather than removing withdrawn ones, so these
// would sit there and still work, editing settings the storefront no longer reads.
$admin_folder = !empty($glob['adminFolder']) ? $glob['adminFolder'] : 'admin';
$obsolete = array($admin_folder.'/sources/documents.contact.inc.php');
foreach ((array)glob(CC_ROOT_DIR.'/'.$admin_folder.'/skins/*/templates/documents.contact.php') as $template) {
    $obsolete[] = str_replace(CC_ROOT_DIR.'/', '', $template);
}
// Quietly; a file left behind by permissions is untidy, not harmful, since
// nothing links to it any more.
foreach ($obsolete as $relative) {
    @unlink(CC_ROOT_DIR.'/'.$relative);
}

if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
    $GLOBALS['cache']->clear();
}
