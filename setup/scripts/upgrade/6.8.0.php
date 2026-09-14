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

// Import missing email content for every installed language, as 6.6.0 does.
// Passing no content type imports whatever the language file has that the store
// does not, so this picks up account.activate (the confirmation link sent before
// a guest record becomes a real account) and any other type a refreshed language
// pack has gained since. importEmail() only inserts rows that do not already
// exist, so a merchant edited copy is never overwritten.
//
// Only the English files carry account.activate today; other languages get it
// when their pack is updated. Until then User::requestActivation() falls back to
// any available language rather than sending nothing.
if (is_array($languages) && !empty($languages)) {
    foreach ($languages as $code => $lang) {
        $email_file = 'email_'.$code.'.xml';
        if (file_exists(CC_LANGUAGE_DIR.$email_file)) {
            $language->importEmail($email_file);
        }
    }
}

/**
 * Apache 2.4 maps .js to text/javascript, which the DEFLATE list generated since
 * #1905 never named, so skin JavaScript ships uncompressed (~300KB on the default
 * skin). seo.class.php now generates the fix, but that only reaches new installs:
 * _checkModRewrite() leaves an existing .htaccess alone forever. Patched here.
 *
 * Additive - types are appended, nothing removed - so a tuned line survives and a
 * second run is a no-op. A store with no mod_deflate block is left alone.
 */
$htaccess_path = CC_ROOT_DIR.'/.htaccess';

if (!file_exists($htaccess_path)) {
    // No file yet; _checkModRewrite() will write the corrected block itself.
} elseif (!is_writable($htaccess_path)) {
    trigger_error('.htaccess is not writable, so JavaScript compression could not be enabled. Add "text/javascript" to the AddOutputFilterByType DEFLATE line by hand.', E_USER_WARNING);
} else {
    $htaccess_before = file_get_contents($htaccess_path);

    // No woff/png/jpg/webp: already compressed, deflating again just costs CPU.
    $wanted = array(
        'text/javascript',      // what Apache 2.4 actually labels .js
        'application/json',
        'application/ld+json',
        'application/xml',
        'application/rss+xml',
        'image/svg+xml',
    );

    $added = array();
    $htaccess_after = preg_replace_callback(
        '/^([ \t]*AddOutputFilterByType\s+DEFLATE)([^\r\n]*)$/mi',
        function ($m) use ($wanted, &$added) {
            $types = preg_split('/\s+/', trim($m[2]), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($wanted as $type) {
                if (!in_array($type, $types, true)) {
                    $types[] = $type;
                    $added[] = $type;
                }
            }
            return $m[1].' '.implode(' ', $types);
        },
        $htaccess_before
    );

    if ($htaccess_after === null) {
        trigger_error('.htaccess could not be parsed, so JavaScript compression was left unchanged.', E_USER_WARNING);
    } elseif (empty($added)) {
        // Already correct, or the store has no mod_deflate block to patch.
        if (!preg_match('/AddOutputFilterByType\s+DEFLATE/i', $htaccess_before)) {
            trigger_error('No mod_deflate block found in .htaccess, so JavaScript compression was not enabled. If the server supports it, add "AddOutputFilterByType DEFLATE text/javascript text/css" inside an <IfModule mod_deflate.c> block.', E_USER_NOTICE);
        }
    } else {
        // This file can 503 the whole store. backup/ is denied to the web.
        $backup_path = CC_ROOT_DIR.'/backup/htaccess-pre-'.CC_VERSION.'-'.date('YmdHis').'.txt';
        @file_put_contents($backup_path, $htaccess_before);

        if (@file_put_contents($htaccess_path, $htaccess_after, LOCK_EX) === false) {
            trigger_error('Could not write .htaccess, so JavaScript compression was not enabled.', E_USER_WARNING);
        } else {
            clearstatcache(true, $htaccess_path);
            // Read back rather than trust the write; we still hold the original.
            $verify = file_get_contents($htaccess_path);
            if ($verify !== $htaccess_after) {
                @file_put_contents($htaccess_path, $htaccess_before, LOCK_EX);
                trigger_error('.htaccess did not verify after writing and has been restored unchanged. JavaScript compression was not enabled.', E_USER_WARNING);
            } else {
                trigger_error('Enabled compression in .htaccess for: '.implode(', ', array_unique($added)).'. Previous file saved to '.basename($backup_path).' in the backup folder.', E_USER_NOTICE);
            }
        }
    }
}
