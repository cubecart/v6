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
Admin::getInstance()->permissions('maintenance', CC_PERM_READ, true);


$GLOBALS['gui']->addBreadcrumb($lang['hooks']['title_hook'], currentPage(array('action', 'hook_id', 'plugin')));

if (Admin::getInstance()->permissions('maintenance', CC_PERM_EDIT)) {
    $snippet_redirect = false;

    if (isset($_POST['snippet_status']) && is_array($_POST['snippet_status'])) {
        foreach ($_POST['snippet_status'] as $key => $value) {
            if ($GLOBALS['db']->update('CubeCart_code_snippet', array('enabled' => $value), array('snippet_id' => $key))) {
                $snippet_redirect = true;
            }
        }
        if ($snippet_redirect) $GLOBALS['hooks']->clearCache();
    }

    if (!empty($_FILES['code_snippet_import']['tmp_name'])) {
        if ($GLOBALS['hooks']->import_code_snippets($_FILES['code_snippet_import'])) {
            $GLOBALS['main']->successMessage($lang['hooks']['notify_snippet_imported']);
        } else {
            $GLOBALS['main']->errorMessage($lang['hooks']['notify_snippet_import_failed']);
        }
        $snippet_redirect = true;
    } else {
        if (isset($_POST['snippet']) && is_array($_POST['snippet'])) {
            $GLOBALS['hooks']->delete_snippet_file($_POST['snippet']['unique_id']);

            if (isset($_POST['snippet']['snippet_id']) && is_numeric($_POST['snippet']['snippet_id'])) {
                if ($GLOBALS['db']->update('CubeCart_code_snippet', $_POST['snippet'], array('snippet_id' => (int)$_POST['snippet']['snippet_id']))) {
                    $GLOBALS['main']->successMessage($lang['hooks']['notify_snippet_updated']);
                    $GLOBALS['hooks']->clearCache();
                }
            } else {
                if ($GLOBALS['db']->select('CubeCart_code_snippet', array('snippet_id'), array('unique_id' => $_POST['snippet']['unique_id']))) {
                    $GLOBALS['main']->errorMessage($lang['hooks']['notify_snippet_not_added']);
                } else {
                    if ($GLOBALS['db']->insert('CubeCart_code_snippet', $_POST['snippet'])==true) {
                        $GLOBALS['main']->successMessage($lang['hooks']['notify_snippet_added']);
                        $snippet_redirect = true;
                        $GLOBALS['hooks']->clearCache();
                    } else {
                        $GLOBALS['main']->setACPWarn($lang['hooks']['notify_snippet_not_added']);
                    }
                }
            }
        }
    }

    if (isset($_GET['delete_snippet']) && is_numeric($_GET['delete_snippet'])) {
        if ($GLOBALS['db']->delete('CubeCart_code_snippet', array('snippet_id' => (int)$_GET['delete_snippet']))) {
            $GLOBALS['hooks']->delete_snippet_file($_GET['delete_snippet']);
            $GLOBALS['main']->successMessage($lang['hooks']['notify_snippet_deleted']);
            $snippet_redirect = true;
            $GLOBALS['hooks']->clearCache();
        }
    }

    if ($snippet_redirect) {
        httpredir(currentPage(array('snippet', 'delete_snippet', 'add_snippet')), 'snippets');
    }


    // Helper: resolve hook file path from DB record
    $resolve_hook_path = function($hf_data) {
        $hf_path = (!empty($hf_data['filepath'])) ? $hf_data['filepath'] : 'hooks/'.$hf_data['trigger'].'.php';
        $hf_path = preg_replace(array('#[^a-z0-9.\\\\_/-]#i', '#\.{1,2}/#'), '', $hf_path);
        return CC_ROOT_DIR.'/modules/plugins/'.$hf_data['plugin'].'/'.$hf_path;
    };

    // Helper: prune timestamped backups to max 10, deleting oldest first
    $prune_backups = function($hook_path) {
        $backups = glob($hook_path.'.*.bak');
        // Filter to only timestamped backups (not defaults)
        $timestamped = array();
        foreach ($backups as $bf) {
            if (preg_match('/\.(\d+)\.bak$/', $bf)) {
                $timestamped[] = $bf;
            }
        }
        sort($timestamped); // oldest first
        while (count($timestamped) > 10) {
            unlink(array_shift($timestamped));
        }
    };

    // Sanitize hook_id — strip any URL fragment that may have been encoded
    if (isset($_GET['hook_id'])) {
        $_GET['hook_id'] = (int)$_GET['hook_id'];
    }

    // Restore hook file from backup
    if (isset($_GET['restore_backup']) && isset($_GET['hook_id']) && is_numeric($_GET['hook_id'])) {
        $backup_ts = preg_match('/^v[\d.]+\.default$/', $_GET['restore_backup']) ? $_GET['restore_backup'] : preg_replace('/[^0-9]/', '', $_GET['restore_backup']);
        if (($hf = $GLOBALS['db']->select('CubeCart_hooks', false, array('hook_id' => (int)$_GET['hook_id']))) !== false) {
            $hf_full = $resolve_hook_path($hf[0]);
            $backup_file = $hf_full.'.'.$backup_ts.'.bak';
            if (file_exists($backup_file) && file_exists($hf_full) && is_writable($hf_full)) {
                // Backup current before restoring (only if different from backup and from default)
                if (md5_file($hf_full) !== md5_file($backup_file)) {
                    $skip_backup = false;
                    $def_files = glob($hf_full.'.v*.default.bak');
                    foreach ($def_files as $df) {
                        if (md5_file($hf_full) === md5_file($df)) {
                            $skip_backup = true;
                            break;
                        }
                    }
                    if (!$skip_backup) {
                        copy($hf_full, $hf_full.'.'.time().'.bak');
                        $prune_backups($hf_full);
                    }
                }
                if (copy($backup_file, $hf_full)) {
                    $GLOBALS['main']->successMessage($lang['hooks']['notify_hook_file_restored']);
                }
            }
        }
        httpredir(currentPage(array('restore_backup')), 'hook_code');
    }

    // Delete hook file backup (protect default)
    if (isset($_GET['delete_backup']) && isset($_GET['hook_id']) && is_numeric($_GET['hook_id'])) {
        $backup_ts = preg_replace('/[^0-9]/', '', $_GET['delete_backup']);
        if (!empty($backup_ts) && ($hf = $GLOBALS['db']->select('CubeCart_hooks', false, array('hook_id' => (int)$_GET['hook_id']))) !== false) {
            $hf_full = $resolve_hook_path($hf[0]);
            $backup_file = $hf_full.'.'.$backup_ts.'.bak';
            if (file_exists($backup_file)) {
                unlink($backup_file);
                $GLOBALS['main']->successMessage($lang['hooks']['notify_hook_backup_deleted']);
            }
        }
        httpredir(currentPage(array('delete_backup')), 'hook_code');
    }

    // Save hook file code if submitted (use RAW POST to avoid HTML entity encoding)
    $raw_hook_code = isset($GLOBALS['RAW']['POST']['hook_file_code']) ? $GLOBALS['RAW']['POST']['hook_file_code'] : null;
    if ($raw_hook_code !== null && strlen($raw_hook_code) > 0 && isset($_GET['hook_id']) && is_numeric($_GET['hook_id'])) {
        if (($hf = $GLOBALS['db']->select('CubeCart_hooks', false, array('hook_id' => (int)$_GET['hook_id']))) !== false) {
            $hf_full = $resolve_hook_path($hf[0]);
            $hf_dir  = dirname($hf_full);
            if (file_exists($hf_full) && is_writable($hf_full)) {
                // Only save if content actually changed
                if (md5($raw_hook_code) !== md5_file($hf_full)) {
                    // Create versioned default backup on first change
                    $cfg_file = CC_ROOT_DIR.'/modules/plugins/'.$hf[0]['plugin'].'/config.xml';
                    $ver = '0.0.0';
                    if (file_exists($cfg_file)) {
                        try { $cfg_xml = new SimpleXMLElement(file_get_contents($cfg_file)); $ver = (string)$cfg_xml->info->version; } catch (Exception $e) {}
                    }
                    $def_bak = $hf_full.'.v'.$ver.'.default.bak';
                    if (!file_exists($def_bak)) {
                        copy($hf_full, $def_bak);
                        foreach (glob($hf_full.'.v*.default.bak') as $old_def) {
                            if ($old_def !== $def_bak) unlink($old_def);
                        }
                    }
                    // Only create timestamped backup if current file differs from default
                    if (md5_file($hf_full) !== md5_file($def_bak)) {
                        copy($hf_full, $hf_full.'.'.time().'.bak');
                        $prune_backups($hf_full);
                    }
                    if (file_put_contents($hf_full, $raw_hook_code) !== false) {
                        $GLOBALS['main']->successMessage($lang['hooks']['notify_hook_file_saved']);
                    } else {
                        $GLOBALS['main']->errorMessage($lang['hooks']['error_hook_file_save']);
                    }
                }
            } elseif (!file_exists($hf_full) && is_dir($hf_dir) && is_writable($hf_dir)) {
                // File is missing but the plugin's hooks/ directory exists and is writable —
                // create the file from the editor content. No backup since there's nothing to preserve.
                if (file_put_contents($hf_full, $raw_hook_code) !== false) {
                    $GLOBALS['main']->successMessage($lang['hooks']['notify_hook_file_saved']);
                } else {
                    $GLOBALS['main']->errorMessage($lang['hooks']['error_hook_file_save']);
                }
            }
        }
    }

    if (isset($_POST['hook']) && is_array($_POST['hook'])) {
        // Validation
        $error = array();
        $required = array('trigger', 'hook_name', 'plugin');
        $_POST['hook']['priority'] = ctype_digit($_POST['hook']['priority']) ?  $_POST['hook']['priority'] : 0;
        foreach ($_POST['hook'] as $key => $value) {
            if (in_array($key, $required)) {
                if (empty($value)) {
                    $error[$key] = $key;
                }
            }
        }

        if (empty($error)) {
            if (isset($_POST['hook']['hook_id']) && is_numeric($_POST['hook']['hook_id'])) {
                $GLOBALS['db']->update('CubeCart_hooks', $_POST['hook'], array('hook_id' => $_POST['hook']['hook_id']));
                if ($GLOBALS['db']->affected() > 0) {
                    $GLOBALS['main']->successMessage($lang['hooks']['notify_hook_update']);
                    $GLOBALS['hooks']->clearCache();
                }
                if (isset($_POST['submit_cont'])) {
                    $tab = !empty($_POST['previous-tab']) ? preg_replace('/[^a-z0-9_#-]/i', '', $_POST['previous-tab']) : '#hook_code';
                    httpredir(currentPage().$tab);
                }
                httpredir(currentPage(array('action', 'hook_id')));
            } else {
                $new_hook_id = $GLOBALS['db']->insert('CubeCart_hooks', $_POST['hook']);
                if ($new_hook_id) {
                    $GLOBALS['hooks']->clearCache();
                    // If the merchant supplied PHP in the Hook Code editor, write the file now.
                    $raw_hook_code = isset($GLOBALS['RAW']['POST']['hook_file_code']) ? $GLOBALS['RAW']['POST']['hook_file_code'] : null;
                    if ($raw_hook_code !== null && strlen($raw_hook_code) > 0) {
                        $row = $GLOBALS['db']->select('CubeCart_hooks', false, array('hook_id' => (int)$new_hook_id));
                        if ($row) {
                            $hf_full = $resolve_hook_path($row[0]);
                            $hf_dir  = dirname($hf_full);
                            if (!file_exists($hf_full) && is_dir($hf_dir) && is_writable($hf_dir)) {
                                file_put_contents($hf_full, $raw_hook_code);
                            }
                        }
                    }
                    $GLOBALS['main']->successMessage($lang['hooks']['notify_hook_create']);
                    httpredir(currentPage(array('action', 'hook_id')));
                } else {
                    $GLOBALS['main']->errorMessage($lang['hooks']['error_hook_create']);
                    $GLOBALS['smarty']->assign('HOOK', $_POST['hook']);
                }
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['hooks']['error_hook_create']);
            $GLOBALS['smarty']->assign('HOOK', $_POST['hook']);
        }
    }
    if (isset($_POST['status']) && is_array($_POST['status'])) {
        // Enable/Disable individual hooks
        $updated = false;
        foreach ($_POST['status'] as $hook_id => $status) {
            if ($GLOBALS['db']->update('CubeCart_hooks', array('enabled' => (int)$status), array('hook_id' => $hook_id))) {
                $updated = true;
            }
        }
        if ($updated) {
            $GLOBALS['main']->successMessage($lang['hooks']['notify_hook_status']);
            $GLOBALS['hooks']->clearCache();
        } else {
            $GLOBALS['main']->errorMessage($lang['hooks']['error_hook_status']);
        }
        httpredir(currentPage());
    }

    // Delete all hook rows for a plugin whose folder no longer exists. Only fires
    // when the plugin folder is genuinely missing — refuses on installed plugins so
    // a stray click can't wipe live hook configuration.
    if (isset($_GET['delete_orphans']) && isset($_GET['token']) && $_GET['token'] === SESSION_TOKEN) {
        $orphan_plugin = preg_replace('#[^a-zA-Z0-9._-]#', '', (string)$_GET['delete_orphans']);
        if ($orphan_plugin !== '' && !is_dir(CC_ROOT_DIR.'/modules/plugins/'.$orphan_plugin)) {
            $GLOBALS['db']->delete('CubeCart_hooks', array('plugin' => $orphan_plugin));
            $GLOBALS['hooks']->clearCache();
            $GLOBALS['main']->successMessage(sprintf($lang['hooks']['notify_orphans_deleted'], $orphan_plugin));
        }
        httpredir(currentPage(array('delete_orphans', 'token')));
    }

    // Delete a single hook row whose backing file is missing. Useful when a plugin
    // is half-uninstalled (folder still present but a hook PHP file was removed).
    if (isset($_GET['delete_hook']) && is_numeric($_GET['delete_hook']) && isset($_GET['token']) && $_GET['token'] === SESSION_TOKEN) {
        $del_id = (int)$_GET['delete_hook'];
        if (($hook = $GLOBALS['db']->select('CubeCart_hooks', false, array('hook_id' => $del_id))) !== false) {
            $hf_full = $resolve_hook_path($hook[0]);
            // Only allow deletion if the hook file is genuinely absent.
            if (!file_exists($hf_full)) {
                $GLOBALS['db']->delete('CubeCart_hooks', array('hook_id' => $del_id));
                $GLOBALS['hooks']->clearCache();
                $GLOBALS['main']->successMessage($lang['hooks']['notify_orphan_hook_deleted']);
            }
        }
        httpredir(currentPage(array('delete_hook', 'token')));
    }
}
// Create list of enabled plugin folders
$plugins = $GLOBALS['hooks']->scan_all_plugins('plugins', true);
$smarty_data = array();

// Helper used by both the index list and the per-plugin view: resolve a hook
// file's absolute path the same way the loader does.
$resolve_hook_path_check = function($plugin, $hook_row) {
    $hf_path = (!empty($hook_row['filepath'])) ? $hook_row['filepath'] : 'hooks/'.$hook_row['trigger'].'.php';
    $hf_path = preg_replace(array('#[^a-z0-9.\\\\_/-]#i', '#\.{1,2}/#'), '', $hf_path);
    return CC_ROOT_DIR.'/modules/plugins/'.$plugin.'/'.$hf_path;
};

// Allow the per-plugin view for orphaned plugins (folder gone, hook rows still in
// CubeCart_hooks). Synthesise an entry in $plugins so the breadcrumb / PLUGIN var
// downstream don't blow up.
if (isset($_GET['plugin']) && !is_numeric($_GET['plugin']) && preg_match('#^[a-zA-Z0-9._-]+$#', (string)$_GET['plugin'])) {
    if (!isset($plugins[(string)$_GET['plugin']])) {
        $orphan_name = (string)$_GET['plugin'];
        if ($GLOBALS['db']->count('CubeCart_hooks', 'hook_id', array('plugin' => $orphan_name)) > 0) {
            $plugins[$orphan_name] = array(
                'plugin' => $orphan_name,
                'name'   => str_replace('_', ' ', $orphan_name),
            );
        }
    }
}

if (isset($_GET['plugin']) && isset($plugins[(string)$_GET['plugin']]) && !is_numeric($_GET['plugin'])) {
    $GLOBALS['gui']->addBreadcrumb(ucwords($plugins[$_GET['plugin']]['name']), currentPage(array('hook_id', 'action')));

    // Load config.xml if it exists
    $config_file = CC_ROOT_DIR.'/modules/plugins/'.$_GET['plugin'].'/config.xml';
    if (file_exists($config_file)) {
        try {
            $xml = new SimpleXMLElement(file_get_contents($config_file));
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }
    $this_plugin = (isset($_POST['hook']['plugin'])) ? $_POST['hook']['plugin'] : $_GET['plugin'];

    if (isset($_GET['hook_id']) && is_numeric($_GET['hook_id']) || isset($_GET['action']) && $_GET['action'] == 'add') {
        $is_add_hook = (isset($_GET['action']) && $_GET['action'] == 'add');
        $GLOBALS['main']->AddTabControl(
            $is_add_hook ? $lang['hooks']['title_hook_add'] : $lang['hooks']['title_hook_edit'],
            'hook_edit'
        );
        if (isset($_GET['hook_id'])) {
            // Edit hook
            if (($hook = $GLOBALS['db']->select('CubeCart_hooks', false, array('hook_id' => (int)$_GET['hook_id']))) !== false) {
                $hook_data = $hook[0];
                $GLOBALS['smarty']->assign('HOOK', $hook_data);
                $GLOBALS['gui']->addBreadcrumb($hook_data['trigger']);

                // Resolve hook file path for code viewer
                $hook_filepath = (!empty($hook_data['filepath'])) ? $hook_data['filepath'] : 'hooks/'.$hook_data['trigger'].'.php';
                $hook_filepath = preg_replace(array('#[^a-z0-9.\\\\_/-]#i', '#\.{1,2}/#'), '', $hook_filepath);
                $hook_full_path = CC_ROOT_DIR.'/modules/plugins/'.$hook_data['plugin'].'/'.$hook_filepath;

                $hook_dir = dirname($hook_full_path);
                if (file_exists($hook_full_path)) {
                    $hook_writable = is_writable($hook_full_path);

                    // Get plugin version for default backup display
                    $plugin_version = (isset($xml->info->version)) ? (string)$xml->info->version : '0.0.0';
                    $default_backup = $hook_full_path.'.v'.$plugin_version.'.default.bak';

                    // Find backups
                    $hook_backups = array();
                    $backup_pattern = $hook_full_path.'.*.bak';
                    $backup_files = glob($backup_pattern);
                    if ($backup_files) {
                        rsort($backup_files); // newest first
                        foreach ($backup_files as $bf) {
                            if (preg_match('/\.(\d+)\.bak$/', $bf, $m)) {
                                $hook_backups[] = array(
                                    'timestamp' => $m[1],
                                    'date' => date('Y-m-d H:i:s', (int)$m[1]),
                                    'size' => filesize($bf),
                                    'is_default' => false,
                                    'restore_url' => currentPage(null, array('restore_backup' => $m[1])),
                                    'delete_url' => currentPage(null, array('delete_backup' => $m[1])),
                                );
                            }
                        }
                    }
                    // Append default backup at the end (current version only)
                    if (file_exists($default_backup)) {
                        $hook_backups[] = array(
                            'timestamp' => 'v'.$plugin_version.'.default',
                            'date' => $lang['hooks']['default_version'].' (v'.$plugin_version.')',
                            'size' => filesize($default_backup),
                            'is_default' => true,
                            'restore_url' => currentPage(null, array('restore_backup' => 'v'.$plugin_version.'.default')),
                            'delete_url' => '',
                        );
                    }

                    $hook_file_data = array(
                        'path' => 'modules/plugins/'.$hook_data['plugin'].'/'.$hook_filepath,
                        'code' => base64_encode(file_get_contents($hook_full_path)),
                        'writable' => $hook_writable,
                        'backups' => $hook_backups,
                        'missing' => false,
                    );
                    $GLOBALS['smarty']->assign('HOOK_FILE', $hook_file_data);
                    $GLOBALS['main']->AddTabControl($lang['hooks']['title_hook_code'], 'hook_code');
                } elseif (is_dir($hook_dir)) {
                    // File missing but the hooks/ directory is on disk — let the merchant
                    // recreate it via the editor. Save path also handles this case.
                    $hook_file_data = array(
                        'path' => 'modules/plugins/'.$hook_data['plugin'].'/'.$hook_filepath,
                        'code' => base64_encode(''),
                        'writable' => is_writable($hook_dir),
                        'backups' => array(),
                        'missing' => true,
                    );
                    $GLOBALS['smarty']->assign('HOOK_FILE', $hook_file_data);
                    $GLOBALS['main']->AddTabControl($lang['hooks']['title_hook_code'], 'hook_code');
                }
            } else {
                httpredir(currentPage(array('hook_id')));
            }
        } else {
            // Create hook
            if (isset($plugins) && is_array($plugins)) {
                foreach ($plugins as $plugin) {
                    $plugin['selected'] = ($this_plugin === $plugin['plugin']) ? ' selected="selected"' : '';
                    $smarty_data['plugins'][]  = $plugin;
                }
                $GLOBALS['smarty']->assign('PLUGINS', $smarty_data['plugins']);
            }
            // Expose an empty Hook Code editor on the add screen. The save handler
            // resolves the actual filesystem path from the new row's plugin/trigger/filepath
            // and writes the file once the hook has been inserted.
            $GLOBALS['smarty']->assign('HOOK_FILE', array(
                'path'     => sprintf($lang['hooks']['hook_file_path_pending'], 'modules/plugins/'.$this_plugin.'/hooks/'),
                'code'     => base64_encode(''),
                'writable' => is_dir(CC_ROOT_DIR.'/modules/plugins/'.$this_plugin.'/hooks') && is_writable(CC_ROOT_DIR.'/modules/plugins/'.$this_plugin.'/hooks'),
                'backups'  => array(),
                'missing'  => true,
                'pending'  => true,
            ));
            $GLOBALS['main']->AddTabControl($lang['hooks']['title_hook_code'], 'hook_code');
        }

        // List dynamic hooks
        $plugin_list = glob(CC_ROOT_DIR.'/modules/plugins/*');
        foreach ($plugin_list as $plugin_path) {
            if (is_dir($plugin_path)) {
                $hook_name = 'admin.'.basename($plugin_path);
                $selected = (isset($hook_data) && $hook_name==$hook_data['trigger']) ? ' selected="selected"' : '';
                $smarty_data['triggers'][] = array('trigger' => $hook_name, 'deprecated' => 0, 'selected' => $selected);
            }
        }

        // List static hooks
        $hooks_list = CC_ROOT_DIR.'/modules/plugins/hooks.xml';
        if (file_exists($hooks_list)) {
            $source = file_get_contents($hooks_list);
            try {
                if (($xml = new SimpleXMLElement($source)) !== false) {
                    foreach ($xml as $entry) {
                        $attrib = $entry->attributes();
                        foreach ($attrib as $key => $value) {
                            $option[$key] = (string)$value;
                        }
                        $option['selected'] = (isset($hook_data) && (string)$entry->attributes()->trigger === $hook_data['trigger']) ? ' selected="selected"' : '';
                        $smarty_data['triggers'][] = $option;
                    }
                    $GLOBALS['smarty']->assign('TRIGGERS', $smarty_data['triggers']);
                }
            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
                $GLOBALS['main']->errorMessage($lang['hooks']['error_plugin_config']);
            }
        }
        $add_hook = (isset($_GET['action']) && $_GET['action']=='add') ? true : false;
        $GLOBALS['smarty']->assign('ADD_HOOK', $add_hook);
        $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
    } else {
        $GLOBALS['main']->AddTabControl($lang['hooks']['title_hook'], 'hooks');
        $GLOBALS['main']->AddTabControl($lang['hooks']['title_hook_add'], null, currentPage(null, array('action' => 'add')));

        // Update hooks and add more if we need to...
        if(isset($_GET['revert'])) {
            if($hooks->install($this_plugin)) {
                $GLOBALS['main']->successMessage($lang['module']['success_install']);
                httpredir(currentPage(array('revert')));
            } else {
                $GLOBALS['main']->errorMessage($lang['module']['failed_install']);
                httpredir(currentPage(array('revert')));
            }
        }

        // Display all hooks for the selected plugin. Each row carries a `file_missing`
        // flag and a `delete` URL for half-installed plugins (folder exists but a
        // hook file was removed) so the merchant can clean up without dropping into SQL.
        if (($hook_list = $GLOBALS['db']->select('CubeCart_hooks', false, array('plugin' => $this_plugin))) !== false) {
            foreach ($hook_list as $hook) {
                if (empty($hook['hook_name'])) {
                    $hook['hook_name'] = $hook['trigger'];
                }
                $hook['edit'] = currentPage(null, array('hook_id' => $hook['hook_id']));
                $hook['file_missing'] = !file_exists($resolve_hook_path_check($this_plugin, $hook));
                $hook['delete'] = currentPage(null, array('delete_hook' => $hook['hook_id'], 'token' => SESSION_TOKEN));
                $smarty_data['hooks'][] = $hook;
            }
            $GLOBALS['smarty']->assign('HOOKS', $smarty_data['hooks']);
        }
        // Revert is only possible if the plugin's config.xml is on disk for install() to read.
        $GLOBALS['smarty']->assign('CAN_REVERT', file_exists(CC_ROOT_DIR.'/modules/plugins/'.$this_plugin.'/config.xml'));
        $GLOBALS['smarty']->assign('DISPLAY_HOOKS', true);
        $GLOBALS['smarty']->assign('PLUGIN', $plugins[$this_plugin]['name']);
    }
} else {
    $GLOBALS['main']->AddTabControl($lang['hooks']['title_hook'], 'plugins');
    $GLOBALS['main']->AddTabControl($lang['hooks']['title_code_snippets'], 'snippets');
    $GLOBALS['main']->AddTabControl($lang['hooks']['title_import_code_snippets'], 'snippets_import');
    ## List all plugins using hooks. Two-pass build:
    ##   1. Folder-based plugins (the legacy "installed" list).
    ##   2. Distinct plugins from CubeCart_hooks whose folder no longer exists -
    ##      these are flagged orphaned with a delete-all-hooks action so admins
    ##      can clean up the "Hook 'X' was not found" notices without SQL.
    $plugin_rows = array();
    if (isset($plugins) && is_array($plugins)) {
        foreach ($plugins as $plugin) {
            $plugin['edit'] = currentPage(null, array('plugin' => $plugin['plugin']));
            $plugin['orphaned'] = false;
            $plugin_rows[$plugin['plugin']] = $plugin;
        }
    }
    $db_plugins = $GLOBALS['db']->misc(
        sprintf('SELECT DISTINCT `plugin` FROM `%sCubeCart_hooks` WHERE `plugin` <> \'\'', $GLOBALS['config']->get('config', 'dbprefix')),
        false
    );
    if (is_array($db_plugins)) {
        foreach ($db_plugins as $r) {
            $name = (string)$r['plugin'];
            if ($name === '' || isset($plugin_rows[$name])) continue;
            $plugin_rows[$name] = array(
                'plugin'   => $name,
                'name'     => str_replace('_', ' ', $name),
                'edit'     => currentPage(null, array('plugin' => $name)),
                'orphaned' => true,
                'delete'   => currentPage(null, array('delete_orphans' => $name, 'token' => SESSION_TOKEN)),
            );
        }
    }
    // Tally hook counts per plugin so the list can show "(N hooks, M missing)".
    foreach ($plugin_rows as $key => $p) {
        $count = (int)$GLOBALS['db']->count('CubeCart_hooks', 'hook_id', array('plugin' => $key));
        $plugin_rows[$key]['hooks_total'] = $count;
        $missing = 0;
        if ($count > 0 && empty($p['orphaned'])) {
            if (($hook_rows = $GLOBALS['db']->select('CubeCart_hooks', array('trigger', 'filepath'), array('plugin' => $key))) !== false) {
                foreach ($hook_rows as $hr) {
                    if (!file_exists($resolve_hook_path_check($key, $hr))) $missing++;
                }
            }
        }
        $plugin_rows[$key]['hooks_missing'] = $missing;
    }
    ksort($plugin_rows);
    $GLOBALS['smarty']->assign('PLUGINS', array_values($plugin_rows));
    $GLOBALS['smarty']->assign('DISPLAY_PLUGINS', true);

    if ($smarty_data['snippets'] = $GLOBALS['db']->select('CubeCart_code_snippet', '*', array(), array('priority' => 'ASC'))) {
        $GLOBALS['smarty']->assign('SNIPPETS', $smarty_data['snippets']);
    }

    if (isset($_GET['snippet']) && is_numeric($_GET['snippet'])) {
        $snippet = $GLOBALS['db']->select('CubeCart_code_snippet', '*', array('snippet_id' => (int)$_GET['snippet']));
        $GLOBALS['smarty']->assign('DISPLAY_SNIPPET_FORM', true);
    } elseif (isset($_POST['snippet'])) {
        $snippet[0] = $_POST['snippet'];
        $GLOBALS['smarty']->assign('SNIPPET', $snippet[0]);
        $GLOBALS['smarty']->assign('DISPLAY_SNIPPET_FORM', true);
    } elseif (isset($_GET['add_snippet']) && $_GET['add_snippet']) {
        $GLOBALS['smarty']->assign('DISPLAY_SNIPPET_FORM', true);
    }

    if (isset($snippet[0]) && is_array($snippet[0])) {
        $snippet[0]['php_code_base64'] = $snippet[0]['php_code'];
        $snippet[0]['php_code'] = base64_decode($snippet[0]['php_code']);
        $GLOBALS['smarty']->assign('SNIPPET', $snippet[0]);
    }

    // List static hooks
    $hooks_list = CC_ROOT_DIR.'/modules/plugins/hooks.xml';
    if (file_exists($hooks_list)) {
        $source = file_get_contents($hooks_list);
        try {
            if (($xml = new SimpleXMLElement($source)) !== false) {
                foreach ($xml as $entry) {
                    $attrib = $entry->attributes();
                    foreach ($attrib as $key => $value) {
                        $option[$key] = (string)$value;
                    }
                    $option['selected'] = (isset($snippet) && (string)$entry->attributes()->trigger === $snippet[0]['hook_trigger']) ? ' selected="selected"' : '';
                    $smarty_data['triggers'][] = $option;
                }
                $GLOBALS['smarty']->assign('TRIGGERS', $smarty_data['triggers']);
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            $GLOBALS['main']->errorMessage($lang['hooks']['error_plugin_config']);
        }
    }
}

$page_content = $GLOBALS['smarty']->fetch('templates/settings.hooks.php');
