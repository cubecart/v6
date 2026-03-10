# Extensions & Plugins

Install, configure, and manage store extensions including payment gateways, shipping modules, skins, and plugins from the CubeCart Marketplace.

## Extension Marketplace

The marketplace tab displays all available and installed extensions in a card-based grid layout. Extensions are fetched from the CubeCart Extensions API and cached for 1 hour. Use the refresh button to force an update.

### Sort Order

Extensions are displayed in the following priority:

1. **Installed** extensions appear first
2. **Recommended** extensions appear next
3. All remaining extensions sorted alphabetically

### Search & Filters

| Control | Description |
| --- | --- |
| Search | Real-time text filter across extension names and descriptions. |
| Category filters | Filter by category (payment, shipping, skin, affiliate, other). Each filter shows the count of extensions in that category. |
| Installed filter | Show only extensions that are currently installed. |
| Upgrade filter | Show only extensions with available upgrades. |

### Extension Cards

Each extension is displayed as a card showing:

| Element | Description |
| --- | --- |
| Name | Extension name. Installed extensions use the name from the local config.xml. |
| Version | Current installed version, or latest available version if not installed. |
| Description | Brief description of the extension's functionality. |
| Type badge | Extension type: gateway, shipping, plugins, skin, affiliate, or livehelp. |
| Category badge | Functional category (e.g. payment, shipping, other). |
| Installed badge | Shown in green when the extension is installed locally. |
| Upgrade badge | Shown in amber when a newer version is available. |
| 3rd Party badge | Shown in purple for installed extensions not found in the official marketplace. |
| Recommended star | Amber star icon for CubeCart-recommended extensions. |
| Gallery button | View extension screenshots (if available). |

### Actions

| Button | Description |
| --- | --- |
| Install | Download and install the latest version from the marketplace. |
| Upgrade | Download and install a newer version over the existing installation. |
| Downgrade | Install an older version via the version selector dropdown. |
| Configure | Open the extension's settings page (modules only, not skins). |
| Up to Date | Shown when the installed version matches the latest available. No action needed. |
| Delete | Permanently remove the extension files and configuration. |

### Version Selector

For extensions with multiple versions available, a dropdown selector allows choosing a specific version to install, upgrade, or downgrade to. The currently installed version (if any) is indicated in the dropdown.

### Post-Install Behaviour

- **Modules** (gateway, shipping, plugin, etc.): After successful installation, the browser redirects to the extension's configuration page.
- **Skins**: After successful installation, the page reloads on the marketplace tab. Skins have no configuration page.

## ionCube Compatibility

When installing an extension, the system automatically scans ZIP contents for ionCube-encoded PHP files.

| Check | Description |
| --- | --- |
| Encoded file detection | PHP files are inspected for the ionCube header signature (`<?php //00`). |
| Loader check | If encoded files are found and the ionCube Loader extension is not loaded, the installation is blocked with an error. |
| PHP version check | After extraction, if the ionCube Loader API is available, the encoded file's target PHP major version is compared against the running PHP version. A warning is shown if they don't match. |

## Skin Handling

Skins are installed to the `skins/` directory. The installer automatically detects whether the ZIP archive includes a `skins/` prefix in its paths and adjusts the extraction destination accordingly to prevent double-nesting.

Installed skins are shown in the marketplace alongside modules. They cannot be configured from the marketplace — use **Settings > Skin** to select and configure the active storefront skin.

## Installing via Token

| Field | Description |
| --- | --- |
| Token | Enter the extension token (format: XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX) provided after purchasing from the CubeCart Marketplace. |
| Backup if Exists | Create a backup of the existing extension files before overwriting. |
| Backup & Abort | Abort the installation if the backup fails. |

## Extension Types

| Type | Description |
| --- | --- |
| Gateway | Payment gateways (e.g. PayPal, Stripe, SumUp). |
| Shipping | Shipping rate calculators and integrations. |
| Plugin | General functionality extensions. |
| Affiliate | Affiliate and referral tracking modules. |
| Livehelp | Live chat and customer support integrations. |
| Skin | Storefront design themes. |
| External | External service integrations and export modules. |

## 3rd Party Extensions

Installed extensions that are not found in the official CubeCart Marketplace API are flagged as **3rd Party**. This applies when:
- The extension has no matching entry in the API, **and**
- The extension's `creator` field in config.xml does not contain "CubeCart"

3rd party extensions can be deleted but cannot be upgraded or downgraded through the marketplace.

## Screenshot Gallery

Extensions with images display a gallery button. The gallery opens as a modal overlay with:
- Full-size image viewer with previous/next navigation
- Thumbnail strip along the bottom for quick navigation
- Keyboard navigation (arrow keys, Escape to close)

> [!NOTE]
> Deleting an extension permanently removes its files and configuration. This cannot be undone.

> [!TIP]
> Extensions that are not yet configured show a Setup button instead of a toggle. Click Setup to enter the initial configuration before enabling.
