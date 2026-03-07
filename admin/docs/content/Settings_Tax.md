# Tax Settings

Configure tax classes, tax details, tax rules, and import tariffs.

## Tax Classes

Tax classes categorise products for tax purposes (e.g. "Standard Rate", "Reduced Rate", "Zero Rate"). Each product is assigned to one tax class.

Use **Assign All Products** to bulk-assign every product to a specific tax class.

## Tax Details

Tax details define the named taxes shown on invoices and in the basket (e.g. "VAT", "Sales Tax", "GST").

| Field | Description |
| --- | --- |
| Name | Internal reference name for the tax. |
| Display Name | The name shown to customers on invoices and in the basket. |
| Status | Enable or disable this tax detail. |

## Tax Rules

Tax rules link a tax class with a tax detail at a specific rate, optionally restricted by country and zone.

| Field | Description |
| --- | --- |
| Tax Class | Which product tax class this rule applies to. |
| Tax Detail | Which named tax to apply. |
| Country | Restrict to a specific country, or apply to all EU countries, or all non-EU countries. |
| State/Zone | Optionally restrict to a specific state or zone. |
| Subtotal | Apply this tax to product subtotals. |
| Shipping | Apply this tax to shipping charges. |
| Rate | The tax percentage (e.g. 20 for 20%). |

### Shortcuts

- **Assign to EU** -- creates a rule for all EU member countries at once.
- **Assign to Rest** -- creates a rule for all non-EU countries.

## Tariffs

Import duties applied based on country of manufacture or dispatch and the destination country.

| Field | Description |
| --- | --- |
| Source Country | Where the goods originate or are dispatched from. |
| Destination Country | Where the goods are being shipped to. |
| Tariff Type | Country of Manufacture or Country of Dispatch. |
| Percentage | The tariff rate. |
| Display | Optional label shown to customers. Tariffs with the same display name are grouped together. |

> [!TIP]
> Tax rules are evaluated based on the customer's delivery address. Create rules for each country/region where you need to charge tax.
