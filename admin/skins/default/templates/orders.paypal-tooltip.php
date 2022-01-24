{if $SUMMARY.gateway=='PayPal'}         
<style>
.ppcp-container {
display: flex;
}
.ppcp-container a {
color: #139bd7
}
.flex-child {
flex: 1;
}  
.ppcp-container .monogram {
width:55px;
flex: 0 0 55px;
background-image: url({$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/PP_Monogram.png);
background-position: center;
background-repeat: no-repeat;
}
.ppcp-container .message {
background-color: #003087;
color: #ffffff;
padding: 15px;
font-size: 14px;
}
</style>
<div class="ppcp-container">
<div class="flex-child monogram"></div>
<div class="flex-child message">
Did you know? PayPal Standard has now been superseded by PayPal Commerce.<br><a href="https://www.cubecart.com/extensions/plugins/paypal-commerce" target="_blank">Find out more and upgrade today &raquo;</a>
</div>

</div>
{/if}