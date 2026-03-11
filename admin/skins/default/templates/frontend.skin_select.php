{if $SKINS}
<style>
.skin_select_widget {
    all: initial;
    position: fixed;
    z-index: 999998;
    bottom: 0;
    right: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    font-size: 12px;
    line-height: normal;
    color: #333;
    text-align: left;
    direction: ltr;
    box-sizing: border-box;
}
.skin_select_widget .ssw_toggle {
    display: inline-block;
    cursor: pointer;
    background-color: #f2f2f7;
    border: 1px solid #d1d1d6;
    border-bottom: none;
    border-radius: 12px 12px 0 0;
    padding: 6px 14px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    font-size: 12px;
    font-weight: 500;
    color: #666;
    text-decoration: none;
    text-transform: none;
    letter-spacing: normal;
}
.skin_select_widget .ssw_toggle:hover {
    background-color: #e8e8ed;
    color: #333;
    text-decoration: none;
}
.skin_select_widget .ssw_panel {
    display: none;
    background-color: #f2f2f7;
    border: 1px solid #d1d1d6;
    border-bottom: none;
    border-radius: 12px 12px 0 0;
    padding: 10px 14px;
    box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
}
.skin_select_widget.open .ssw_panel {
    display: block;
}
.skin_select_widget.open .ssw_toggle {
    display: none;
}
.skin_select_widget .ssw_panel label {
    display: block;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    font-size: 11px;
    font-weight: 500;
    color: #666;
    margin: 0 0 5px 0;
    padding: 0;
    text-transform: none;
    letter-spacing: normal;
    border: none;
    background: none;
}
.skin_select_widget .ssw_panel select {
    display: block;
    width: 200px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    font-size: 12px;
    padding: 5px 8px;
    margin: 0;
    border: 1px solid #d1d1d6;
    border-radius: 7px;
    background-color: #fff;
    color: #333;
    box-sizing: border-box;
    appearance: auto;
    -webkit-appearance: auto;
    -moz-appearance: auto;
    height: auto;
    line-height: normal;
    outline: none;
}
.skin_select_widget .ssw_panel .ssw_close {
    display: inline-block;
    cursor: pointer;
    color: #999;
    font-size: 14px;
    text-decoration: none;
    float: right;
    margin: 0 0 0 8px;
    padding: 0;
    border: none;
    background: none;
    font-weight: normal;
    line-height: 1;
}
.skin_select_widget .ssw_panel .ssw_close:hover {
    color: #333;
    text-decoration: none;
}
@media only screen and (max-width: 40em) {
    .skin_select_widget {
        display: none;
    }
}
</style>
<div class="skin_select_widget" id="skin_select_widget">
    <a href="#" class="ssw_toggle" onclick="document.getElementById('skin_select_widget').classList.add('open');return false;">&#x1f3a8; Skin</a>
    <div class="ssw_panel">
        <a href="#" class="ssw_close" onclick="document.getElementById('skin_select_widget').classList.remove('open');return false;">&times;</a>
        <form action="{$VAL_SELF}" method="post">
            <label>Change Skin</label>
            <select name="select_skin" onchange="this.form.submit()">
            {foreach from=$SKINS item=skin}
            {if isset($skin.styles)}
            {foreach from=$skin.styles item=style}
            <option value="{$skin.name}|{$style.directory}" {$style.selected}>{$skin.display} - {$style.name}</option>
            {/foreach}
            {else}
            <option value="{$skin.name}" {$skin.selected}>{$skin.display}</option>
            {/if}
            {/foreach}
            </select>
        </form>
    </div>
</div>
{/if}
