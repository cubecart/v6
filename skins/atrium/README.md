# Atrium

CubeCart v6 storefront skin. Tailwind CSS v4 + Alpine.js 3.

Requires **CubeCart 6.7.6 or newer**. Core does not enforce `<minVersion>`, so the
skin shows an admin-only banner if the store is older.

---

## For merchants

**Do not edit any file in this skin except `css/custom.css`.** Skin updates
overwrite templates in place, with no backup.

`css/custom.css` is loaded last, needs no build step, and is left alone by
updates. Retheme by overriding the design tokens:

```css
:root {
  --cc-brand-600: oklch(52% 0.19 25);   /* your brand colour */
  --cc-brand-700: oklch(45% 0.17 25);   /* its hover state */
  --cc-radius: 0.25rem;                 /* squarer corners */
  --cc-font-sans: "Your Font", ui-sans-serif, system-ui, sans-serif;
}
```

Every colour, radius and font in the skin indirects through a `--cc-*` token, so
this is enough to reskin it without touching a template or installing anything.

Self-hosting a webfont? Put the `@font-face` in `custom.css` too. Do not add a
third-party font CDN link — this stylesheet loads on the checkout page.

---

## For developers

### Build

Node is needed **only to develop the skin**. Merchants never build anything:
`css/tailwind.css` and `js/vendor/0.atrium.components.js` are committed and shipped.

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
redis-cli FLUSHDB                       # this store caches in redis (global.inc.php:7)
rm -f cache/skin/*                      # compiled templates
```

Then load the store. To preview without changing the store default, append
`?select_skin=atrium|default` — but note this is **not** admin-gated
(`classes/gui.class.php:110-119`), so any visitor or crawler can switch skins on
a live store. Develop on staging.

Caches that will otherwise hide your edits: `info.skins.list` (every skin's
parsed `config.xml`), `skin.atrium.custom` (the `<custom>` block), the rendered
navigation, and Smarty's compiled templates — which are **not** recompiled at
all when debug is off and cache is on (`bootstrap.view.inc.php:37`).

### Two rules that are not style preferences

**1. No ES6 template literals in `js/src/`.** CubeCart minifies skin JS with
JSMin (2002-era), which predates backticks. A single apostrophe inside one
throws an uncaught PHP exception — a white page for every customer. `bundle-js.sh`
fails the build if it finds one. Everything else in ES2020 is fine.

**2. Write Alpine components as function references, never object literals.**

```html
GOOD:  x-data="ccDrawer('menuOpen')"     :class="open ? 'block' : 'hidden'"
BAD:   x-data="{open:false}"             :class="{'block':open}"
```

Smarty parses `{` followed by a non-space as a tag. `auto_literal` happens to
save `{ ` with a space, but relying on that puts a fatal one keystroke away.
Function references keep logic in `js/src/` where it can be linted anyway.

### Why the stylesheet bypasses `{combine}`

`css/tailwind.css` is served with a plain `<link>`, not through `{combine}`,
because it is already minified by Lightning CSS and `{combine}` would re-minify
it, rewrite relative `url()` paths, and blanket-replace `'../images/'` inside the
file's content (`function.combine.php:97,101`) — none of which it needs.

Bypassing `{combine}` forfeits its mtime cache-busting, so `build/stamp.php`
generates `templates/element.css.version.php` with a content hash instead. Note
`{$SKIN_VERSION}` does **not** exist in CubeCart; only `SKIN_FOLDER` and
`SKIN_SUBSET` are assigned.

One real hazard from `{combine}`'s CSS minifier, should you ever route anything
through it: it strips units from zero values, so an **unregistered** custom
property `--gap: 0rem` becomes `--gap: 0` and `calc(1rem + var(--gap))` turns
invalid and is dropped. Do not give a token in `custom.css` a bare zero length
that `calc()` consumes.

*(A widely-repeated claim that this same transform breaks Tailwind's `ring-*`
utilities via `@property --tw-ring-offset-width` is **false** — tested in Chrome,
unitless zero is a valid `<length>`, the registration succeeds, and Tailwind's
own `--minify` makes the identical change.)*

### Licensing

Everything shipped here is MIT and GPL-3.0 compatible: Tailwind CSS, Alpine.js
and its plugins, jQuery, jQuery Migrate. See `js/vendor/VERSIONS.txt`.

**No markup in this skin is derived from Tailwind Plus.** That kit's licence
prohibits using its components to build a theme distributed to other people,
free or paid. It may be looked at for design ideas; nothing may be copied,
retyped or converted from it into a template here.

### jQuery

jQuery 3.7.1 + Migrate is loaded in `<head>` purely as a compatibility shim for
third-party plugins — nothing in this skin uses it. It cannot be dropped or
deferred: plugins inject raw jQuery through the `class.gui.head_js` hook and it
must already be defined (e.g.
`modules/plugins/paypal_commerce/hooks/class.gui.head_js.php:69,146-154`).
Not 4.x — that removes the deprecated APIs those plugins call. Not slim — that
omits `$.ajax` and the effects methods, which is most of what they use.

### Templates core requires

31 templates are fetched **unguarded** — a missing one throws `SmartyException`
and takes the page down; there is no fallback to another skin. Of the ones a
storefront skin owns, `box.basket.php` is fetched on nearly every page
(`gui.class.php:435`), and `main.stream.php` / `print.receipt.php` are standalone
documents with their own `<html>`. 13 box/element templates *are*
`templateExists()`-guarded and may be omitted.

### Module template overrides (Atrium ships none)

CubeCart lets a skin replace a module's own template. `GUI::getCustomModuleSkin()`
(`classes/gui.class.php:463`) has exactly two callers —
`modules/gateway/Card_Capture/gateway.class.php:338` and
`modules/plugins/paypal_commerce/gateway.class.php:112` — so the slot is
`templates/modules/gateway/<ModuleDir>/<file>` and applies to those two modules
only.

**Atrium deliberately ships no override.** The slot is there if you want one;
occupying it has a real cost:

- It **forks the module's form permanently**. A later module release that adds or
  renames a field would silently break payment for Atrium stores only, because
  the skin copy never receives the change. On a gateway that is a bad trade.
- It cannot be tested without making Atrium the store default — see below — so
  it is easy to ship untested markup onto a card-entry page.
- `paypal_commerce/form.tpl` is only `{$PAYPAL_HTML}`, a passthrough; there is
  nothing to gain by overriding it, and something to lose.

The one behaviour the reference skin provided and the modules depend on — the
`.colorbox` CVV lightbox in Card_Capture — is supplied instead by
`js/src/90-compat.js`, as a delegated handler. The module keeps ownership of its
markup; the skin supplies the missing behaviour.

**If you do add an override**, two things will bite you:

1. **It resolves against the STORE DEFAULT skin, not the session skin.**
   `gui.class.php:465` reads `config.skin_folder` and `file_exists()` fails
   silently, so an override is invisible while previewing with
   `?select_skin=atrium`. Atrium must be the store default to test it at all.
2. **It keeps the module's file name, which is `.tpl`.** `css/src/input.css`
   already scans `../../templates/**/*.tpl` and `build/lint-compile.php` already
   globs `templates/modules/*/*/*.tpl` — both are there purely so a future
   override is not silently purged by Tailwind or skipped by the linter. Leave
   them.

### Merchant step: PayPal Commerce placement

PayPal Commerce picks its DOM injection points from
`modules/plugins/paypal_commerce/config.<skin>.json`, falling back to
`config.foundation.json` whose product selector
(`.product_wrapper #main_content form .row .row:last-child`) matches nothing in
Atrium. The product-page button and pay-later message then vanish **with no
console error** — the module's guard tests the length of the selector *string*,
which is never zero.

Atrium ships the anchor (`id="call_to_action_block"` on the product page, the
same id `config.dillion.json` targets), but the config file lives under
`modules/` and so cannot ship with a skin. Copy it:

```bash
cp modules/plugins/paypal_commerce/config.dillion.json \
   modules/plugins/paypal_commerce/config.atrium.json
```

Then clear the cache — the module memoises it under `pp_config.atrium`, so until
you do it will look like the new file is being ignored.

### Intentional omissions

**`modal.exit.php` is deliberately not shipped.** Store Settings → *Show exit modal*
(`config[exit_modal]`) therefore has no effect on Atrium. Core never fetches the
template — the only call site in the reference skin is its own `main.php` — so
omitting it cannot raise a Smarty error.

It was a duplicate of the footer newsletter form: same `subscribe` +
`force_unsubscribe` payload, same handler (`GUI::_displayMailingList()`), wrapped
in a mouseout interstitial. Subscribe, unsubscribe and double opt-in are
unaffected and are served by `box.newsletter.php`. The reference implementation
is also broken three ways (wrapper and form share one id, the submit button has
no `data-form-id` so invisible captcha cannot submit it, and it duplicates
`id="newsletter_recaptcha"` from the footer box), so there was nothing to port.

**`element.recaptcha.invisible.php` IS shipped and is intentionally empty** — it
is a `file_exists()` capability marker read by the admin settings screen. Deleting
it makes the admin claim this skin does not support invisible reCAPTCHA. See the
comment inside the file.

`main.checkout.php` is used **automatically** for `_a` in
`confirm|basket|gateway|cart|checkout`, whenever the file exists
(`controllers/controller.index.inc.php:64`). Deleting it silently sends checkout
back through `main.php` with the full nav and sidebar. Anything `main.php`
provides must be repeated there if checkout needs it.

### Testing the basket with curl

You can't, by default. `classes/session.class.php:141-145` skips session
creation entirely — so nothing persists in a basket — unless the `cc_browser`
cookie is present, and that cookie is set by JavaScript
(`classes/gui.class.php:176-181`). Set it by hand in your cookie jar:

```
.dev1.cubecart.com	TRUE	/v6	TRUE	<expiry>	cc_browser	1
```

Also note AJAX calls must use the product's **SEO URL**;
`index.php?_a=product&product_id=N` is 302-redirected and the POST body is lost.

---

Last reconciled with CubeCart **6.7.6**.
