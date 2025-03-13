{if isset($ACP_DATA)}
<style>
.acp_widget {
    position: absolute;
    top: 0;
    border-bottom-right-radius: 6px;
    padding: 5px 12px 5px 5px;
    background-color: #eee;
    -webkit-box-shadow: 0px 0px 6px 0px rgba(0,0,0,0.75);
    -moz-box-shadow: 0px 0px 6px 0px rgba(0,0,0,0.75);
    box-shadow: 0px 0px 6px 0px rgba(0,0,0,0.75);
    border-right:1px solid #dcdcdc;
    border-bottom:1px solid #dcdcdc;
}
.acp_widget a {
    box-shadow:inset 0px 1px 0px 0px #ffffff;
    background:linear-gradient(to bottom, #ffffff 5%, #f6f6f6 100%);
    background-color:#ffffff;
    border-radius:6px;
    border:1px solid #dcdcdc;
    display:inline-block;
    cursor:pointer;
    color:#666666;
    font-family:Arial;
    font-size:12px;
    font-weight:bold;
    padding:2px 10px;
    text-decoration:none;
    text-shadow:0px 1px 0px #ffffff;
    margin: 3px;
    width: 100%;
    text-align:center;
}
.acp_widget a:hover {
    background:linear-gradient(to bottom, #f6f6f6 5%, #ffffff 100%);
    background-color:#f6f6f6;
}
.acp_widget a:active {
    position:relative;
    top:1px;
}
</style>
<div class="acp_widget">
    <a href="{$ACP_DATA.acp_path}">{$LANG.navigation.acp_home}</a>
    {if isset($ACP_DATA)}<br>
    <a href="{$ACP_DATA.edit_url}">{$ACP_DATA.url_text}</a>
    {/if}
</div>
{/if}