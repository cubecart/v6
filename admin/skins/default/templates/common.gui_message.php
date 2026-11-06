{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if isset($GUI_MESSAGE) || (!empty($NEWS.items) && $NEWS_TOP_HASH != $NEWS_DISMISSED_LINK)}
<div id="gui_message">
  {if isset($GUI_MESSAGE.error)}
  <div class="error">
	<ul>
		{foreach from=$GUI_MESSAGE.error item=error}
	  	<li>{$error}</li>
	  	{/foreach}
	</ul>
	<button type="button" class="gui_message_close" title="{$LANG.common.close}" aria-label="{$LANG.common.close}">&times;</button>
  </div>
  {/if}
  {if isset($GUI_MESSAGE.notice)}
	{foreach from=$GUI_MESSAGE.notice item=notice}
  	<div class="success"><span>{$notice}</span><button type="button" class="gui_message_close" title="{$LANG.common.close}" aria-label="{$LANG.common.close}">&times;</button></div>
	{/foreach}
  {/if}
  {if !empty($NEWS.items) && $NEWS_TOP_HASH != $NEWS_DISMISSED_LINK}
  <div id="news-banner" class="success" data-link="{$NEWS.items[0].link}" data-token="{$SESSION_TOKEN}">
	<span class="news-banner__label">{$NEWS.title|default:"News"}</span>
	<a href="{$NEWS.items[0].link}" target="_blank">{$NEWS.items[0].title}</a>
	<button type="button" class="gui_message_close" title="{$LANG.common.close}" aria-label="{$LANG.common.close}">&times;</button>
  </div>
  {literal}
  <script>
  (function() {
     var banner = document.getElementById('news-banner');
     if (!banner) return;
     var btn = banner.querySelector('.gui_message_close');
     if (!btn) return;
     // admin.js hides it like any other message; this remembers the dismissal
     btn.addEventListener('click', function() {
        var fd = new FormData();
        fd.append('dismiss_news', '1');
        fd.append('news_link', banner.getAttribute('data-link') || '');
        fd.append('token', banner.getAttribute('data-token') || '');
        fetch('?_g=dashboard', { method: 'POST', body: fd, credentials: 'same-origin' });
     });
  })();
  </script>
  {/literal}
  {/if}
</div>
{/if}
<noscript>
	<div id="gui_message">
		<div class="error">
		<ul>
    		<li>{$LANG.settings.error_js_required}</li>
    	</ul>
    	</div>
    </div>
</noscript>