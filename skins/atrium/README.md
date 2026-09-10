# Atrium

CubeCart v6 storefront skin. Tailwind CSS v4 + Alpine.js 3.

Requires **CubeCart 6.7.6 or newer**. Core does not enforce `<minVersion>`, so the
skin shows an admin-only banner on older stores.

---

## How it works

| Path | What it is |
| --- | --- |
| `templates/` | 78 Smarty templates. Core fetches 31 of them unguarded |
| `css/src/` | Tailwind sources. **Edited, not shipped** |
| `css/tailwind.css` | The compiled stylesheet. Committed, served directly |
| `css/cubecart.<style>.css` | One per sub-theme, linked from `$SKIN_SUBSET` |
| `css/custom.css` | Yours. Loaded last, never overwritten |
| `js/src/` | Alpine components. **Edited, not shipped** |
| `js/vendor/0.atrium.components.js` | The concatenated bundle. Committed |
| `config.xml` | Sub-themes, image sizes, admin settings, `<custom>` block |

Every colour, radius and font resolves through a `--cc-*` custom property with a
fallback:

```css
--color-brand-600: var(--cc-brand-600, oklch(53.8% 0.153 246));
```

So setting `--cc-brand-600` anywhere repaints the skin, with no build step and no
Node. That indirection is the whole customisation model; the sub-themes and the
admin colour settings are just different places that set the same tokens.

Node is needed **only to develop the skin**. Merchants build nothing: the
compiled CSS and JS are committed and shipped.

---

## Customising

### From the admin panel

**Sub-theme.** Seven styles, chosen where the skin is chosen: Blue (default),
Grey, Teal, Green, Purple, Red, Amber. Each repaints the brand ramp only, so
buttons, links, focus rings and price accents change; the bands, the neutrals
and the semantic red/green/amber stay put. A sub-theme should change what the
store is branded with, not what "your payment failed" looks like.

**Main Menu.** Horizontal band under the header (default), or a vertical rail
down the left of the page. Desktop only — below `lg` both use the slide-out
drawer. Same markup either way; `main.php` renders `.cc-navbar` or `.cc-navrail`
and switches the content wrapper to `.cc-layout-rail`, a grid that puts the
sidebar boxes under the content at `lg` and beside it at `xl`.

**Skin settings** (the cog on the skin's card in Manage Extensions) cover the
menu and colours plus what to show: quick view, listing add-to-basket, basket
count and total, company name, mobile number, mailing list, coupon field, order
comments, review stars, product view, checkout registration mode. `<settings>`
in `config.xml` is the full list.

| Control | Paints | Notes |
| --- | --- | --- |
| Sub-theme | brand ramp | Curated, contrast-checked, dark mode included |
| Brand Colour | brand ramp | Exact hex. **Replaces the sub-theme** |
| Header Colour | navigation band | No sub-theme touches the bands |
| Footer Colour | footer band | Independent of the header |

### `css/custom.css`

**The only file in this skin you should edit.** Updates overwrite templates in
place with no backup. `custom.css` loads last, so it beats everything above.

```css
:root {
  --cc-brand-600: oklch(52% 0.19 25);   /* brand colour */
  --cc-brand-700: oklch(45% 0.17 25);   /* its hover state */
  --cc-radius: 0.25rem;                 /* squarer corners */
  --cc-font-sans: "Your Font", ui-sans-serif, system-ui, sans-serif;
}
```

Self-hosting a webfont? Put the `@font-face` here too. Do not add a third-party
font CDN link: this stylesheet loads on the checkout page.

### `<custom>` in `config.xml` (developer-only, no admin UI)

`colour_scheme` (`light` | `auto`), `container_width`, `recaptcha_badge_position`.
Flattened into `$SKIN_CUSTOM[<name>]`, one level deep, cached under
`skin.atrium.custom` — clear the cache after editing.

### Adding a sub-theme

Two files: a `<style>` block in `config.xml` and a matching
`css/cubecart.<directory>.css`. The stylesheet **must** exist —
`element.css.php` links it unconditionally from `$SKIN_SUBSET`, so a missing one
404s on every page. Give it a `[data-theme="dark"]` block as well as `:root`:
`theme.css` lifts brand 50/100/600/700 for dark mode at the same specificity, and
a sub-theme file loads later, so without one the light ramp wins in dark mode.

---

## Developing

### Build

```bash
npm install            # once
./build/build.sh       # compile CSS (minified) + stamp the cache-buster
./build/watch.sh       # rebuild CSS on change, unminified
./build/bundle-js.sh   # concatenate js/src/*.js into the shipped bundle
./build/vendor.sh      # refresh Alpine/jQuery from node_modules
./build/lint-compile.php   # compile every template through real Smarty
```

Run `build.sh` and `bundle-js.sh` before every commit. `lint-compile.php` exits
non-zero on failure, so it can gate one.

### Testing a change

```bash
redis-cli FLUSHDB    # this store caches in redis (global.inc.php:7)
rm -f cache/skin/*   # compiled templates
```

Caches that otherwise hide edits: `info.skins.list` (every skin's parsed
`config.xml`), `skin.atrium.custom`, `skin.atrium.settings`, the rendered
navigation, and Smarty's compiled templates — **not** recompiled at all when
debug is off and cache is on (`bootstrap.view.inc.php:37`).

Preview without changing the store default by appending
`?select_skin=atrium|default`, or any sub-theme directory in place of `default`.
This is **not** admin-gated (`gui.class.php:110-119`), so any visitor can switch
skins on a live store. Develop on staging.

### Two rules that are not style preferences

**1. No ES6 template literals in `js/src/`.** CubeCart minifies skin JS with
JSMin (2002-era), which predates backticks; one apostrophe inside a template
literal throws an uncaught PHP exception and white-pages every customer.
`bundle-js.sh` fails the build if it finds one. The rest of ES2020 is fine.

**2. Alpine components as function references, never object literals.**

```html
GOOD:  x-data="ccDrawer('menuOpen')"     :class="open ? 'block' : 'hidden'"
BAD:   x-data="{open:false}"             :class="{'block':open}"
```

Smarty parses `{` followed by a non-space as a tag. `auto_literal` happens to
save `{ ` with a space, but relying on that puts a fatal one keystroke away.

### The stylesheet bypasses `{combine}`

`css/tailwind.css` is served with a plain `<link>`: `{combine}` would re-minify
already-minified output, rewrite relative `url()` paths and blanket-replace
`'../images/'` inside the file's content (`function.combine.php:97,101`). That
loses its mtime cache-busting, so `build/stamp.php` writes a content hash into
`templates/element.css.version.php` instead. (`{$SKIN_VERSION}` does not exist in
CubeCart; only `SKIN_FOLDER` and `SKIN_SUBSET` are assigned.)

`{combine}`'s minifier strips units from zero values, so an **unregistered**
`--gap: 0rem` becomes `0` and any `calc()` using it is dropped. Never give a token
in `custom.css` a bare zero length that `calc()` consumes. (It does *not* break
Tailwind's `ring-*` utilities, despite the claim — unitless zero is a valid
`<length>`.)

### Templates core requires

31 are fetched **unguarded**: a missing one throws `SmartyException` and takes the
page down, with no fallback to another skin. `box.basket.php` is fetched on
nearly every page (`gui.class.php:435`); `main.stream.php` and
`print.receipt.php` are standalone documents with their own `<html>`. A further
13 box/element templates are `templateExists()`-guarded and may be omitted.

`main.checkout.php` is used **automatically** for `_a` in
`confirm|basket|gateway|cart|checkout` whenever it exists
(`controller.index.inc.php:64`). Delete it and checkout silently reverts to
`main.php` with the full nav and sidebar.

`element.recaptcha.invisible.php` is intentionally empty: it is a `file_exists()`
capability marker read by the admin settings screen. Delete it and the admin
claims this skin cannot do invisible reCAPTCHA.

### Module template overrides — Atrium ships none

The slot is `templates/modules/gateway/<ModuleDir>/<file>` and
`GUI::getCustomModuleSkin()` (`gui.class.php:463`) has exactly two callers:
Card_Capture and PayPal Commerce.

Occupying it forks that module's form permanently, so a later release that
renames a field breaks payment for Atrium stores only. Card_Capture's `.colorbox`
CVV lightbox — the one behaviour the reference skin supplied — comes from
`js/src/90-compat.js` as a delegated handler instead.

If you add one anyway: it resolves against the **store default** skin, not the
session skin, and fails silently, so Atrium must be the store default to test it.
It also keeps the module's `.tpl` extension, which is why `css/src/input.css` and
`build/lint-compile.php` both glob `.tpl` under `templates/modules/` — leave
those lines in.

### PayPal Commerce placement

The module picks its DOM injection points from
`modules/plugins/paypal_commerce/config.<skin>.json`. **2.0.7 and later ship
`config.atrium.json`.** Anything older falls back to `config.foundation.json`,
whose product selector matches nothing here, and the product-page button and
pay-later message vanish **with no console error** — the guard tests the length
of the selector *string*, which is never zero. Fix by copying
`config.dillion.json` to `config.atrium.json` (it targets the same
`id="call_to_action_block"` Atrium ships), then clear the cache: the module
memoises it under `pp_config.atrium`.

### Exit modal

`modal.exit.php` is driven by Store Settings → *Show exit modal*. Core never
fetches it, so `main.php` includes it explicitly; `main.checkout.php` deliberately
does not — an interstitial over a customer mid-payment costs more than a
subscriber is worth. Every id carries an `_exit` suffix so it cannot collide with
the footer newsletter box.

The trigger (`ccExitModal` in `js/src/60-newsletter.js`) is the pointer leaving
past the top edge, armed 3s after load, remembered for 30 days in the
`newsletter_exit` cookie. Touch has no such event so it never fires there, which
also keeps it clear of Google's intrusive-interstitial penalty.

### jQuery

3.7.1 + Migrate, loaded in `<head>` purely as a shim for third-party plugins;
nothing here uses it. It cannot be dropped or deferred — plugins inject raw
jQuery through the `class.gui.head_js` hook and it must already be defined. Not
4.x (removes the deprecated APIs those plugins call), not slim (omits `$.ajax`
and the effects methods).

### Testing the basket with curl

You can't by default: `session.class.php:141-145` skips session creation unless
the `cc_browser` cookie is present, and that cookie is set by JavaScript
(`gui.class.php:176-181`). Add it to your cookie jar by hand:

```
.dev1.cubecart.com	TRUE	/v6	TRUE	<expiry>	cc_browser	1
```

AJAX calls must use the product's **SEO URL**;
`index.php?_a=product&product_id=N` is 302-redirected and the POST body is lost.

### Licensing

Everything shipped is MIT and GPL-3.0 compatible: Tailwind CSS, Alpine.js and
plugins, jQuery, jQuery Migrate. See `js/vendor/VERSIONS.txt`.

Country flags in `images/flags/` are from
[lipis/flag-icons](https://github.com/lipis/flag-icons) (4x3 set), copyright (c)
2013 Panayiotis Lipiridis, MIT. The licence text ships verbatim as
`images/flags/LICENSE` — do not delete it. `images/flags/README.md` explains the
language-code-to-country-code mapping.

**No markup here is derived from Tailwind Plus.** That kit's licence prohibits
using its components to build a theme distributed to other people, free or paid.
Look at it for ideas; copy nothing.

---

Last reconciled with CubeCart **6.7.6**.
