# Countries & Zones

Manage the countries and zones (states/regions) available for customer addresses, shipping, and tax rules.

## Countries

Each country has an editable name, ISO codes, and a status setting that controls how it appears in address forms.

### Country Status Options

| Status | Behaviour |
| --- | --- |
| Disabled | Country does not appear in address forms. |
| Enabled (Zone Required) | Country is available and customers must select a zone/state. |
| Enabled (Zone Optional) | Country is available, zone/state selection is optional. |
| Enabled (Zone Disabled) | Country is available but no zone/state field is shown. |

### Country Fields

| Field | Description |
| --- | --- |
| Name | Country name. Click to edit inline. |
| ISO Alpha-2 | Two-letter country code (e.g. GB, US). |
| ISO Alpha-3 | Three-letter country code (e.g. GBR, USA). |
| ISO Numeric | Numeric country code. |
| EU | Toggle to mark the country as an EU member state (used by tax rules). |

## Zones

Zones are states, provinces, or regions within a country. Each zone has a name, abbreviation, and status toggle. Zones are used in shipping rules and tax calculations.

## Add Country / Add Zone

Use the respective tabs to add new countries or zones. When adding a zone, select which country it belongs to.

> [!TIP]
> Only enable the countries you ship to. This keeps address forms clean and prevents orders from unsupported regions.
