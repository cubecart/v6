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

/**
 * The email design: a logo, a few colours and a footer note, turned into the template
 * every email is wrapped in
 *
 * A merchant should not need HTML to put their own colours on their emails. The
 * settings are kept in the store's configuration; saving them writes an ordinary row
 * in CubeCart_email_template (and a second one for newsletters, with an unsubscribe
 * link), so the mailer, the newsletter composer and anything else that reads that
 * table carry on exactly as before. A store keeps its own template until it chooses
 * this one.
 */
class EmailDesign
{
    const CONFIG = 'email_design';

    //=====[ Public ]=======================================

    /**
     * @return array What a design is before anyone changes it: close to the stock template
     */
    public static function defaults()
    {
        return array(
            'logo'       => 'image',
            'align'      => 'left',
            'accent'     => '#27515d',
            'background' => '#f7f7f7',
            'panel'      => '#ffffff',
            'text'       => '#333333',
            'corners'    => 'rounded',
            'font'       => 'sans',
            'footer'     => '',
        );
    }

    /**
     * @return array The store's design
     */
    public static function settings()
    {
        $stored = $GLOBALS['config']->get(self::CONFIG);
        return self::clean(is_array($stored) ? $stored : array());
    }

    /**
     * Make a design out of whatever was posted. Anything not understood falls back
     * to the default, so what goes into the template is always one of a known few.
     *
     * @param array $input
     * @return array
     */
    public static function clean($input)
    {
        $design = self::defaults();
        if (!is_array($input)) {
            return $design;
        }
        $choices = array(
            'logo'    => array('image', 'text'),
            'align'   => array('left', 'center'),
            'corners' => array('rounded', 'square'),
            'font'    => array('sans', 'serif'),
        );
        foreach ($choices as $name => $allowed) {
            if (isset($input[$name]) && in_array($input[$name], $allowed, true)) {
                $design[$name] = $input[$name];
            }
        }
        foreach (array('accent', 'background', 'panel', 'text') as $name) {
            if (isset($input[$name]) && preg_match('/^#[0-9a-fA-F]{6}$/', (string)$input[$name])) {
                $design[$name] = strtolower($input[$name]);
            }
        }
        if (isset($input['footer']) && is_scalar($input['footer'])) {
            // Plain text, kept as typed: render() is what makes it safe to put in the template
            $footer = trim(html_entity_decode((string)$input['footer'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $design['footer'] = function_exists('mb_substr') ? mb_substr($footer, 0, 500, 'UTF-8') : substr($footer, 0, 500);
        }
        return $design;
    }

    /**
     * The template for a design
     *
     * Built the way email clients need it: tables for layout, the styling repeated
     * inline, fonts every device has. Each '{' of the stylesheet is followed by a new
     * line, which is what tells Smarty it is CSS and not a tag.
     *
     * @param array $design From clean()
     * @param bool $newsletter Whether to add the unsubscribe link
     * @return string
     */
    public static function render($design, $newsletter = false)
    {
        $design = self::clean($design);
        $lang   = $GLOBALS['language']->email;
        $font   = ($design['font'] === 'serif') ? "Georgia, 'Times New Roman', Times, serif" : 'Arial, Helvetica, sans-serif';
        $radius = ($design['corners'] === 'rounded') ? '8px' : '0';
        // "On the left" is the side a line starts on: the right, in a language read right to left.
        // The mailer gives every email its direction as {$DATA.textDirection} and {$DATA.textAlign}.
        $align  = ($design['align'] === 'center') ? 'center' : '{$DATA.textAlign}';
        $accent = $design['accent'];
        $text   = $design['text'];

        if ($design['logo'] === 'text') {
            $logo = '<a href="{$DATA.storeURL}" style="font-family:'.$font.';font-size:24px;font-weight:bold;line-height:1.3;color:'.$text.';text-decoration:none;">{$DATA.storeName}</a>';
        } else {
            $logo = '<a href="{$DATA.storeURL}"><img src="{$DATA.logoURL}" alt="{$DATA.storeName}" border="0" style="max-width:260px;height:auto;" /></a>';
        }
        $footer = '<a href="{$DATA.storeURL}" style="color:'.$accent.';">{$DATA.storeName}</a>';
        if ($design['footer'] !== '') {
            // The merchant's own words: nothing in them is HTML, and nothing in them is Smarty
            $note = str_replace(array('{', '}'), array('&#123;', '&#125;'), htmlspecialchars($design['footer'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            $footer .= '<br />'."\r\n".nl2br($note);
        }
        if ($newsletter) {
            $footer .= '<br /><br />'."\r\n".htmlspecialchars($lang['design_unsubscribe'], ENT_QUOTES, 'UTF-8').'<br />'."\r\n".'<a href="{$DATA.unsubscribeURL}" style="color:'.$accent.';">{$DATA.unsubscribeURL}</a>';
        }

        $lines = array(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            '<html xmlns="http://www.w3.org/1999/xhtml" dir="{$DATA.textDirection}">',
            '<head>',
            '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />',
            '<meta name="viewport" content="width=device-width, initial-scale=1.0" />',
            '<style type="text/css">',
            'html, body, table, td, th {',
            '	font-family: '.$font.';',
            '	font-size: 14px;',
            '	color: '.$text.';',
            '}',
            'a {',
            '	color: '.$accent.';',
            '}',
            // A button in an email carries a neutral colour of its own for mail clients that drop this
            '.cc-button {',
            '	background-color: '.$accent.' !important;',
            '	border-color: '.$accent.' !important;',
            '}',
            '.w3w {',
            '	color: #E11F26;',
            '	display: block;',
            '}',
            '.w3w a {',
            '	color: #333333;',
            '	text-decoration: none;',
            '}',
            '@media only screen and (max-width: 620px) {',
            '	.cc-email {',
            '		width: 100% !important;',
            '	}',
            '	.cc-email-cell {',
            '		padding-left: 16px !important;',
            '		padding-right: 16px !important;',
            '	}',
            '}',
            '</style>',
            '<title>{$DATA.storeName}</title>',
            '<base href="{$DATA.storeURL}" />',
            '{$DATA.jsonLd}',
            '</head>',
            '<body dir="{$DATA.textDirection}" bgcolor="'.$design['background'].'" style="margin:0;padding:0;background-color:'.$design['background'].';">',
            '<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="'.$design['background'].'" style="background-color:'.$design['background'].';">',
            '  <tr>',
            '    <td align="center" style="padding:24px 12px;">',
            '      <table role="presentation" class="cc-email" border="0" cellpadding="0" cellspacing="0" width="600" bgcolor="'.$design['panel'].'" style="width:600px;max-width:600px;background-color:'.$design['panel'].';border-top:4px solid '.$accent.';border-radius:'.$radius.';">',
            '        <tr>',
            '          <td class="cc-email-cell" align="'.$align.'" style="padding:24px 32px 12px 32px;text-align:'.$align.';">'.$logo.'</td>',
            '        </tr>',
            '        <tr>',
            '          <td class="cc-email-cell" align="{$DATA.textAlign}" style="padding:12px 32px 24px 32px;font-family:'.$font.';font-size:14px;line-height:1.6;color:'.$text.';text-align:{$DATA.textAlign};">{$EMAIL_CONTENT}</td>',
            '        </tr>',
            '        <tr>',
            '          <td class="cc-email-cell" align="{$DATA.textAlign}" style="padding:16px 32px 24px 32px;border-top:1px solid #e3e6e8;font-family:'.$font.';font-size:12px;line-height:1.6;color:#6b7479;text-align:{$DATA.textAlign};">'.$footer.'</td>',
            '        </tr>',
            '      </table>',
            '    </td>',
            '  </tr>',
            '</table>',
            '</body>',
            '</html>',
        );
        return implode("\r\n", $lines);
    }

    /**
     * Keep a design: its settings, and the two templates made from it
     *
     * @param array $design
     * @return array The design as kept
     */
    public static function save($design)
    {
        $design = self::clean($design);
        $lang   = $GLOBALS['language']->email;
        $stored = $GLOBALS['config']->get(self::CONFIG);
        $stored = is_array($stored) ? $stored : array();
        $rows   = array(
            'template_id'   => array(false, $lang['design_template_title']),
            'newsletter_id' => array(true, $lang['design_template_title_news']),
        );
        foreach ($rows as $key => $row) {
            $html = self::render($design, $row[0]);
            $id   = !empty($stored[$key]) ? (int)$stored[$key] : 0;
            if ($id && $GLOBALS['db']->select('CubeCart_email_template', array('template_id'), array('template_id' => $id), false, 1, false, false)) {
                // The database layer strips backslashes from what it is given, so shield any in the footer note
                $GLOBALS['db']->update('CubeCart_email_template', array('content_html' => addslashes($html)), array('template_id' => $id), true, array('content_html'));
            } else {
                $id = (int)$GLOBALS['db']->insert('CubeCart_email_template', array('title' => $row[1], 'template_default' => '0', 'content_html' => addslashes($html)));
            }
            $design[$key] = $id;
        }
        if (isset($stored['previous_default'])) {
            $design['previous_default'] = (int)$stored['previous_default'];
        }
        $GLOBALS['config']->set(self::CONFIG, '', $design, true);
        return $design;
    }

    /**
     * @return array Ids of the templates the design makes; they are not edited by hand
     */
    public static function managed()
    {
        $stored = $GLOBALS['config']->get(self::CONFIG);
        $ids = array();
        foreach (array('template_id', 'newsletter_id') as $key) {
            if (is_array($stored) && !empty($stored[$key])) {
                $ids[] = (int)$stored[$key];
            }
        }
        return $ids;
    }

    /**
     * @return bool Whether the store's emails are sent in this design
     */
    public static function isActive()
    {
        $stored = $GLOBALS['config']->get(self::CONFIG);
        if (!is_array($stored) || empty($stored['template_id'])) {
            return false;
        }
        return (bool)$GLOBALS['db']->select('CubeCart_email_template', array('template_id'), array('template_id' => (int)$stored['template_id'], 'template_default' => '1'), false, 1, false, false);
    }

    /**
     * Send the store's emails in this design, or go back to the template used before
     *
     * @param bool $on
     * @return bool
     */
    public static function activate($on)
    {
        $stored = $GLOBALS['config']->get(self::CONFIG);
        if (!is_array($stored) || empty($stored['template_id'])) {
            return false;
        }
        $ours    = (int)$stored['template_id'];
        $current = $GLOBALS['db']->select('CubeCart_email_template', array('template_id'), array('template_default' => '1'), false, 1, false, false);
        $current = $current ? (int)$current[0]['template_id'] : 0;
        if ($on) {
            if ($current === $ours) {
                return true;
            }
            if ($current) {
                $GLOBALS['config']->set(self::CONFIG, 'previous_default', $current, true);
            }
            $target = $ours;
        } else {
            if ($current !== $ours) {
                return true;
            }
            // Back to what was the default before, or failing that any template that is not ours
            $target   = !empty($stored['previous_default']) ? (int)$stored['previous_default'] : 0;
            $previous = $target ? $GLOBALS['db']->select('CubeCart_email_template', array('template_id'), array('template_id' => $target), false, 1, false, false) : false;
            if (!$previous) {
                $target = 0;
                $others = $GLOBALS['db']->select('CubeCart_email_template', array('template_id'), false, array('template_id' => 'ASC'), false, false, false);
                if ($others) {
                    foreach ($others as $other) {
                        if (!in_array((int)$other['template_id'], self::managed(), true)) {
                            $target = (int)$other['template_id'];
                            break;
                        }
                    }
                }
            }
            if (!$target) {
                return false; // there is nothing else to send emails in
            }
        }
        $GLOBALS['db']->update('CubeCart_email_template', array('template_default' => '0'));
        $GLOBALS['db']->update('CubeCart_email_template', array('template_default' => '1'), array('template_id' => $target));
        return true;
    }
}
