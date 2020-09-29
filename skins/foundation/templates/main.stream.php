{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<!DOCTYPE html>
<html class="no-js" xmlns="http://www.w3.org/1999/xhtml" dir="{$TEXT_DIRECTION}" lang="{$HTML_LANG}">
    <head>
        <title>{$META_TITLE}</title>
        {include file='templates/element.meta.php'}
        <link href="{$CANONICAL}" rel="canonical">
        <link href="{$ROOT_PATH}favicon.ico" rel="shortcut icon" type="image/x-icon">
        {include file='templates/element.css.php'}
        {include file='templates/content.recaptcha.head.php'}
        {include file='templates/element.google_analytics.php'}
        {include file='templates/element.js_head.php'}
    </head>
    <body class="stream">
        <div class="row">
            <div class="small-12 columns">
                <h1>{$DATA.title}</h1>
                <p>{$DATA.description|nl2br}</p>
                <{$TYPE} width="100%" controls autoplay>
                    <source src="{$STREAM_URL}" type="{$DATA.mimetype}">
                </{$TYPE}>
            </div>
        </div>
    </body>
</html>