<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>HSBC - Cardholder Authentication</title>
	<style type="text/css">
	.border {
		text-align: center;
		border: 1px solid darkred;
		font-family: arial;
	}
	.submit {
		border: 1px solid darkred;
		background: red;
		font-family: arial;
		color: white;
		font-weight: bold;
	}

	</style>
</head>
<body onload="document.getElementById('ccVerifyCC').submit();">
	<div class="border"><br />
		<img src="{$DATA.logo}" alt="HSBC Logo" /><br /><br />
		Cardholder Authentication in Progress&hellip;<br /><br />
		<img src="{$DATA.ajax}" alt="" /><br /><br />
		<img src="{$DATA.vbv}" alt="Verified by Visa MasterCard SecureCode" /><img src="{$DATA.mcs}" /><br /><br />

		<form method="post" action="https://{$DATA.pas}" id="ccVerifyCC">
			<input type="hidden" name="CardExpiration" value="{$DATA.CardExpiration}" />
			<input type="hidden" name="CardholderPan" value="{$DATA.CardholderPan}" />
			<input type="hidden" name="CcpaClientId" value="{$DATA.CcpaClientId}" />
			<input type="hidden" name="CurrencyExponent" value="2" />
			<input type="hidden" name="PurchaseAmount" value="{$DATA.PurchaseAmount}" />
			<input type="hidden" name="PurchaseAmountRaw" value="{$DATA.PurchaseAmountRaw}" />
			<input type="hidden" name="PurchaseCurrency" value="826" />
			<input type="hidden" name="MD" value="{$DATA.MD}" />
			<input type="hidden" name="ResultUrl" value="{$DATA.ResultUrl}" />
			<!-- <p>This page should automatically transfer in a few seconds.<br /> If it does not please <input type="submit" name="submit" class='submit' value="Click Here" />.</p> -->
		</form>
	</div>
</body>
</html>
