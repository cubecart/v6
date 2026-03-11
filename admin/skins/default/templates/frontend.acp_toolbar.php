{if isset($ACP_DATA)}
<style>
.acp_widget {
    all: initial;
    position: absolute;
    z-index: 999999;
    top: -10px;
    left: -180px;
    border-radius: 0 12px 12px 0;
    padding: 12px 20px 12px 10px;
    background-color: #f2f2f7;
    transition: left 0.3s ease;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #d1d1d6;
    border-left: none;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    font-size: 12px;
    line-height: normal;
    color: #333;
    text-align: center;
    text-transform: none;
    letter-spacing: normal;
    word-spacing: normal;
    direction: ltr;
    width: 170px;
}
.acp_widget a.button {
    display: block;
    cursor: pointer;
    color: #333;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    font-size: 12px;
    font-weight: 500;
    line-height: 1;
    padding: 7px 10px;
    text-decoration: none;
    text-transform: none;
    letter-spacing: normal;
    margin: 5px 0;
    width: 100%;
    text-align: center;
    box-sizing: border-box;
    float: none;
    opacity: 1;
    min-height: 0;
    max-width: none;
    background: #fff;
    border: 1px solid #d1d1d6;
    border-radius: 7px;
    text-shadow: none;
    box-shadow: none;
    transition: background 0.15s ease;
}
.acp_widget a.button:hover {
    background: #e8e8ed;
    color: #333;
    text-decoration: none;
}
.acp_widget a.button:active {
    background: #d1d1d6;
}
.acp_widget a.button.cache-clear {
    background: #ffd89e;
    border-color: #f0b95a;
    color: #5a3e00;
}
.acp_widget a.button.cache-clear:hover {
    background: #ffc96b;
}
.acp_widget a.button.status-online {
    background: #b8e6c8;
    border-color: #7ec99a;
    color: #155724;
}
.acp_widget a.button.status-online:hover {
    background: #9adbb0;
}
.acp_widget a.button.status-offline {
    background: #f5c6cb;
    border-color: #e4959e;
    color: #721c24;
}
.acp_widget a.button.status-offline:hover {
    background: #f0a8b0;
}
.acp_widget .close a {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    right: 0;
    width: 20px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 14px;
    font-weight: 300;
    text-decoration: none;
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    line-height: 1;
    cursor: pointer;
    float: none;
    opacity: 1;
    z-index: 1;
}
.acp_widget .close a:hover {
    color: #333;
    text-decoration: none;
}
.acp_widget .acp_admin_name {
    display: block;
    padding: 0 4px 4px;
    color: #666;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    margin: 0;
    border: none;
    background: none;
    box-shadow: none;
    text-shadow: none;
    text-decoration: none;
    text-transform: none;
    letter-spacing: normal;
}
@media only screen and (max-width: 40em) {
  .acp_widget {
      display: none;
  }
}
</style>
<div class="acp_widget">
    <div class="close"><a href="#">&laquo;</a></div>
    <span class="acp_admin_name">{$ACP_DATA.admin_name}</span>
    <a href="{$ACP_DATA.acp_path}" class="button" target="acp_window">{$LANG.navigation.acp_home}</a>
    {if isset($ACP_DATA.edit_url)}
    <a href="{$ACP_DATA.edit_url}" class="button" target="acp_window">{$ACP_DATA.url_text}</a>
    {/if}
    <a href="?acp_action=clear_cache&amp;acp_redirect={$ACP_DATA.current_url}" class="button cache-clear">{$LANG.maintain.cache_clear|ucwords}</a>
    <a href="?acp_action=toggle_maintenance&amp;acp_redirect={$ACP_DATA.current_url}" class="button {if $ACP_DATA.is_offline}status-offline{else}status-online{/if}">{if $ACP_DATA.is_offline}{$LANG.settings.tab_offline}{else}{$LANG.settings.tab_online}{/if}</a>
    <a href="?acp_action=logout" class="button">{$LANG.account.logout}</a>
</div>
<script>
{literal}
(function(){
    var w = document.querySelector('.acp_widget');
    if (!w) return;
    function getCookie(n){ var m = document.cookie.match('(^|;)\\s*'+n+'\\s*=\\s*([^;]*)'); return m ? m[2] : null; }
    function setCookie(n,v){ document.cookie = n+'='+v+';path=/;max-age=2592000;SameSite=Lax'; }
    function applyState(show, animate){
        var link = w.querySelector('.close a');
        if (!animate) {
            w.style.transition = 'none';
            w.style.left = show ? '0px' : '-'+(w.offsetWidth - 20)+'px';
            link.innerHTML = show ? '&laquo;' : '&raquo;';
        } else {
            w.style.transition = 'none';
            void w.offsetWidth;
            w.style.transition = 'left 0.3s ease';
            w.style.left = show ? '0px' : '-'+(w.offsetWidth - 20)+'px';
            link.innerHTML = show ? '&laquo;' : '&raquo;';
        }
    }
    var cn = 'cc_acp_toolbar';
    var state = getCookie(cn);
    applyState(state !== '0', false);
    w.querySelector('.close a').addEventListener('click', function(e){
        e.preventDefault();
        var cur = getCookie(cn) !== '0';
        setCookie(cn, cur ? '0' : '1');
        applyState(!cur, true);
    });
})();
{/literal}
</script>
{/if}
