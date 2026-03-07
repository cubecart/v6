# Currency

Manage the currencies available in your store and their exchange rates.

## Currency List

All currencies are shown in an editable table. Click any value to edit it inline.

| Column | Description |
| --- | --- |
| Default | Select which currency is the store's base currency. All product prices are stored in this currency. |
| Status | Enable or disable the currency for customer use. |
| ISO Code | The 3-letter ISO 4217 currency code (e.g. GBP, USD, EUR). |
| Name | Display name of the currency. |
| Symbol Left | Symbol shown before the amount (e.g. £, $). |
| Exchange Rate | Rate relative to the default currency. The default currency is always 1.000000. |
| Adjustment | A percentage adjustment applied on top of the exchange rate (useful for covering conversion fees). |
| Symbol Right | Symbol shown after the amount (used by some currencies). |
| Decimal Places | Number of decimal places to display. |
| Decimal Symbol | Character used as the decimal separator (e.g. . or ,). |
| Thousand Symbol | Character used as the thousands separator (e.g. , or .). |
| Updated | When the exchange rate was last updated. |

## Auto-Update Rates

Click the **Update from ECB** button to automatically fetch the latest exchange rates from the European Central Bank. Rates are relative to the Euro.

## Add Currency

Add a new currency by entering its name, ISO code, exchange rate, decimal places, and symbols.

> [!NOTE]
> The default currency cannot be deleted. To change your default, first set another currency as default, then delete the old one.
