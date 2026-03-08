{if isset($ACP_DATA)}
<style>
.acp_widget {
    all: initial;
    position: absolute;
    z-index: 999999;
    top: 0;
    left: -200px;
    border-bottom-right-radius: 6px;
    padding: 5px 20px 10px 5px;
    background-color: #eee;
    transition: left 0.3s ease;
    box-shadow: 0px 0px 6px 0px rgba(0,0,0,0.75);
    border-right: 1px solid #dcdcdc;
    border-bottom: 1px solid #dcdcdc;
    box-sizing: content-box;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    line-height: normal;
    color: #666666;
    text-align: left;
    text-transform: none;
    letter-spacing: normal;
    word-spacing: normal;
    direction: ltr;
}
.acp_widget a.button {
    box-shadow: inset 0px 1px 0px 0px #ffffff;
    background: linear-gradient(to bottom, #ffffff 5%, #f6f6f6 100%);
    background-color: #ffffff;
    border-radius: 6px;
    border: 1px solid #dcdcdc;
    display: inline-block;
    cursor: pointer;
    color: #666666;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    font-weight: bold;
    line-height: normal;
    padding: 2px 10px;
    text-decoration: none;
    text-shadow: 0px 1px 0px #ffffff;
    text-transform: none;
    letter-spacing: normal;
    margin: 3px;
    width: 100%;
    text-align: center;
    box-sizing: border-box;
    float: none;
    opacity: 1;
    min-height: 0;
    max-width: none;
    transition: none;
}
.acp_widget a.button:hover {
    background: linear-gradient(to bottom, #f6f6f6 5%, #ffffff 100%);
    background-color: #f6f6f6;
    color: #666666;
    text-decoration: none;
}
.acp_widget a.button:active {
    position: relative;
    top: 1px;
}
.acp_widget a.button.cache-clear {
    background: linear-gradient(to bottom, #ffe0b2 5%, #ffcc80 100%);
    border-color: #e09540;
    color: #7a4a00;
    text-shadow: none;
}
.acp_widget a.button.status-online {
    background: linear-gradient(to bottom, #d4edda 5%, #c3e6cb 100%);
    border-color: #5a9a68;
    color: #155724;
    text-shadow: none;
}
.acp_widget a.button.status-offline {
    background: linear-gradient(to bottom, #f8d7da 5%, #f5c6cb 100%);
    border-color: #c94050;
    color: #721c24;
    text-shadow: none;
}
.acp_widget .close a {
    position: absolute;
    top: 40%;
    transform: translateY(-40%);
    right: 5px;
    color: rgb(145, 145, 145);
    font-size: 16px;
    font-weight: normal;
    text-decoration: none;
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    line-height: 1;
    cursor: pointer;
    float: none;
    opacity: 1;
}
.acp_widget .close a:hover {
    color: #333333;
    text-decoration: none;
}
.acp_widget .acp_admin_name {
    display: block;
    padding: 2px 10px 4px;
    color: #999;
    font-size: 11px;
    text-align: center;
    font-weight: normal;
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
    {if isset($ACP_DATA.edit_url)}<br>
    <a href="{$ACP_DATA.edit_url}" class="button" target="acp_window">{$ACP_DATA.url_text}</a>
    {/if}<br>
    <a href="?acp_action=clear_cache&amp;acp_redirect={$ACP_DATA.current_url}" class="button cache-clear">{$LANG.maintain.cache_clear|ucwords}</a><br>
    <a href="?acp_action=toggle_maintenance&amp;acp_redirect={$ACP_DATA.current_url}" class="button {if $ACP_DATA.is_offline}status-offline{else}status-online{/if}">{if $ACP_DATA.is_offline}{$LANG.settings.tab_offline}{else}{$LANG.settings.tab_online}{/if}</a><br>
    <a href="?acp_action=logout" class="button">{$LANG.account.logout}</a>
</div>
<script>
{literal}
(function(){
    var w = document.querySelector('.acp_widget');
    if (!w) return;
    var cookiePath = '{/literal}{$STORE_URL|regex_replace:"/^https?:\\/\\/[^\\/]+/":""}{literal}/';
    function getCookie(n){ var m = document.cookie.match('(^|;)\\s*'+n+'\\s*=\\s*([^;]*)'); return m ? m[2] : null; }
    function setCookie(n,v){ document.cookie = n+'='+v+';path='+cookiePath+';max-age=2592000'; }
    function applyState(show, animate){
        w.style.transition = animate ? 'left 0.3s ease' : 'none';
        if (show) {
            w.style.left = '0px';
            w.querySelector('.close a').innerHTML = '&laquo;';
        } else {
            w.style.left = '-'+(w.offsetWidth - 15)+'px';
            w.querySelector('.close a').innerHTML = '&raquo;';
        }
    }
    var state = getCookie('show_acp_widget');
    applyState(state !== '0', false);
    w.querySelector('.close a').addEventListener('click', function(e){
        e.preventDefault();
        var cur = getCookie('show_acp_widget') !== '0';
        setCookie('show_acp_widget', cur ? '0' : '1');
        applyState(!cur, true);
    });
})();
{/literal}
</script>
{/if}