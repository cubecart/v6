# Migrating from Foundation to Atrium

Notes for a skin developer who knows the old default skin. This document is the
diff: what Foundation did, what Atrium does instead, and why. Rules and
contracts for editing individual templates stay in the template comments — none
of that is repeated here.

---

## What changed structurally

- **Nothing is rendered twice any more.** Foundation duplicated the navigation
  tree, the search form, every product card (list *and* grid), and the whole
  checkout table, then hid one copy with CSS or deleted it with JS. Atrium
  renders each of those once and switches classes.
- **Alternative templates are gone.** Where Foundation shipped two or three
  variants of a template and expected you to comment `{include}` lines in and
  out, Atrium ships one template that adapts.
- **jQuery to Alpine.** Skin behaviour (skin switcher, search-as-you-type,
  mobile drawer, gateway hand-off) is Alpine. jQuery survives only as a shim so
  plugins that expect it keep working.
- **Tailwind build, no `{combine}` for the skin's own CSS.** The compiled
  stylesheet is minified by the Tailwind build and linked directly.
- **Foundation compatibility is deliberate in places.** Element ids, panel ids,
  the `noindex` section list, the `class="es"` search flag and the unsupported
  product-option types are carried over verbatim so plugins and SEO do not
  change under an upgrading store's feet.

### Templates that no longer exist

| Foundation | Atrium |
| --- | --- |
| `box.off_canvas.left.php` | `box.navigation.php`, included once and restyled |
| `content.checkout.medium-up.php`, `content.checkout.small.php` | one responsive table in `content.checkout.php` |
| `element.product.vertical_gallery.php`, `element.product.horizontal_gallery.php` | `element.product.gallery.php` |
| `element.search.manufacturers.checkbox.grid.php`, `.checkbox.table.php`, `.select.chosen.php` | `element.search.manufacturers.php` |
| `element.product.specs.php` | absorbed into `element.product.tabs.php` |
| `element.category.pagination.more.php` | dropped; one pagination style for all screens |

---

## Layout

| Area | Foundation | Atrium | Why |
| --- | --- | --- | --- |
| Robots meta | `noindex` list of `$SECTION_NAME` values that omits `saleitems` | ported verbatim, `saleitems` still omitted | An upgrading store must not silently change its SEO. Adding `saleitems` (a live section name assigned by `Cubecart::loadPage()`) is a deliberate decision to make, not a bug to fix. |
| JSON-LD | — | `main.php` includes `element.markup.json-ld.php`; `main.checkout.php` deliberately does not | A basket is not a product. |
| Theme switching | — | auto colour-scheme script is opt-in via `config.xml`, not default-on | A storefront is full of merchant-supplied product HTML and CMS content that cannot be trusted to invert cleanly. |
| GUI messages | `$GUI_MESSAGE` printed unescaped | same | Core sanitises them, and several core messages legitimately embed an `<a>`. |

## Navigation and boxes

| Area | Foundation | Atrium | Why |
| --- | --- | --- | --- |
| Mobile navigation | `box.off_canvas.left.php` was a second complete copy of the navigation tree, duplicating every link, kept in sync by hand | `box.navigation.php` is included once; the drawer reuses the same markup via Alpine, restyled into a stacked accordion by the `.cc-nav-mobile` class | One tree to maintain. |
| Search box | rendered twice — a desktop copy and a hidden `#small-search` copy, duplicating the `name="search[keywords]"` input on every page | rendered once | — |
| Search-as-you-type | jQuery live-search JS keyed off `class="es"` on the input, active only when Elasticsearch is enabled | `ccSearch()` Alpine component reproduces the same behaviour, same flag, same condition | Carried over unchanged so the trigger stays familiar. |
| Social links | resolved `$SOCIAL_LINKS.icon` against an SVG sprite (`<use xlink:href="#icon-{icon}">`) | no sprite; renders the network name as a text label | An unknown or newly added network yields a readable link instead of an empty box. |
| Newsletter box | — | hidden `#validate_email`, `#validate_already_subscribed`, `#validate_subscribe`, `#validate_unsubscribe` divs keep Foundation's exact ids | Plugins look them up by id. |
| Newsletter captcha | reCAPTCHA rendered inline | revealed on focus | An unused footer widget should not load third-party JS on every page. |
| Skin switcher | jQuery `.autosubmit` handler on the `<select>` | Alpine submit | The skin carries no jQuery behaviour of its own. |

## Catalogue

| Area | Foundation | Atrium | Why |
| --- | --- | --- | --- |
| Product listing (`content.category.php`) | every product emitted **twice** — a `.product_list_view` and a `.product_grid_view` — one hidden with CSS, and the inactive block's quantity input disabled so the form did not post two values for `name="quantity"` | each product rendered once; the container's layout classes switch | No duplicate DOM, no duplicate field names, no disabling hack. |
| Product gallery | two alternative templates (`element.product.vertical_gallery.php`, `element.product.horizontal_gallery.php`) selected by hand-editing an `{include}` in `content.product.php` | one responsive `element.product.gallery.php` | — |
| Manufacturer picker | three alternative pickers chosen by commenting includes in and out of `content.search.php`; the "chosen" variant existed only because a long `<select multiple>` is unusable, and needed jQuery Chosen plus a `<style scoped>` hack to beat Chosen's own CSS | one `element.search.manufacturers.php` that adapts to the data: checkbox grid up to 20 manufacturers, filterable scrolling list beyond | No jQuery Chosen, no choice to make. |
| Category pagination | also shipped `element.category.pagination.more.php`: an infinite-scroll "More" button restricted to small screens, backed by ~90 lines of jQuery, a `cc_scroll` cookie and a `localStorage` cache of rendered HTML | one pagination style at every screen size | Crawlable, linkable, correct on the back button, and no cache to go stale. |
| Product tabs | `element.product.specs.php` was separate | absorbed into `element.product.tabs.php`; panel ids `product_info`, `product_spec` and `quantity_discounts` carried over | The call-to-action still links to `#quantity_discounts`. |
| Review stars | read `$PRODUCT.review_score`; drew `star.png` / `star_half.png` / `star_off.png` — three HTTP requests per product row | `element.product.review_score.php` falls back to `$PRODUCT.review_score` when no `score` include parameter is passed, and draws inline SVG inheriting `currentColor` | Drop-in compatible; works in both themes with no image set. |
| Review gravatars | per-review AJAX HEAD probe to gravatar.com to decide whether an avatar existed | loads the avatar directly and hides it on error | Cheaper, and degrades to no avatar rather than a broken icon. Both skins rely on `config.xml` `<gravatar_ajax>true</gravatar_ajax>` to stop `Catalogue::displayProduct()` making a blocking per-review server-side cURL HEAD. |
| Product options | `CHECKBOX`, `HIDDEN`, `PASSWORD`, `DATEPICKER` and `FILE` are configurable in admin but fall through to `$OTHER_CHOOSERS` and render nothing unless a plugin supplies markup | identical | Parity is deliberate; fixing it is a separate change with its own testing. |
| CMS document page | `{if {$DOCUMENT.hide_title==0}}` — doubled braces, tolerated by Smarty, the inner `{...}` a nested tag for no reason | plain expression | — |

## Checkout and payment

| Area | Foundation | Atrium | Why |
| --- | --- | --- | --- |
| Basket / checkout table | `content.checkout.medium-up.php` and `content.checkout.small.php` were **both** included on every request, one hidden with `show-for-*` classes while `2.cubecart.js` deleted the non-matching copy from the DOM at runtime — otherwise every `quan[]` and shipping field existed twice in one form | single responsive table; those two templates are deliberately absent | — |
| State field | on country change the state element was destroyed and rebuilt, losing anything the customer had typed | the text input and the select are rendered together sharing one name (`estimate[state]`, `billing[state]`, `delivery[state]`) and the JS enables exactly one | The typed value survives. |
| Payment hand-off | the automatic transfer had no explicit submit: `2.cubecart.js` found `class="icon-submit"` anywhere on the page — it sat on a spinner icon — and submitted that element's closest form | explicit submit via `x-init` on `#gateway-transfer`, plus a `<noscript>` fallback button | `.icon-submit` survives on Atrium's spinner but nothing in Atrium's JS binds to it. Every other bundled skin (cg, dillion, amzin, cburst) still does. |
| Progress bar | only the `current` state class was styled | `previous`, `next` and `last` (also emitted by core) are passed through unchanged | Plugins can hook them. |
| Cross-sell strip | hidden entirely on small screens via `.show-for-medium-up` | visible at all sizes | — |
| Cross-sell data shape | — | `$RELATED` uses `img_src` for the image, not the `image` key `$PRODUCTS` uses elsewhere | Carry-over from the old data shape; easy to trip over when copying a product card between templates. |

## Account and address forms

| Area | Foundation | Atrium | Why |
| --- | --- | --- | --- |
| what3words autosuggest (`element.w3w.php`) | jQuery-based, executed before jQuery was guaranteed ready in some load orders | vanilla JS in an IIFE with null guards | No load-order dependency. |

The shared state field described under Checkout applies to the address book too
— it is the same `billing[state]` / `delivery[state]` markup.

## Captcha

| Area | Foundation | Atrium | Why |
| --- | --- | --- | --- |
| Invisible reCAPTCHA bootstrap (`content.recaptcha.head.php`) | onload callback written in jQuery and emitted before jQuery had loaded — it only worked because the callback fires later | vanilla JS, no script-ordering dependency | jQuery survives in Atrium only as a plugin shim. |
| `content.recaptcha.head.php` inclusion | — | Atrium's first pass shipped without it included anywhere, so mode 3 stores silently rejected every protected form. It is now included from `main.php`, `main.checkout.php` and `main.stream.php`. | Regression, fixed — noted so it is not "tidied away" again. |
| `element.recaptcha.invisible.php` | ships the file | ships an empty file | The `file_exists()` capability probe in `admin/sources/settings.index.inc.php` is mislabelled; the file is kept to match the reference skin rather than fixing the probe. |

## CSS and JS build

| Area | Foundation | Atrium | Why |
| --- | --- | --- | --- |
| Skin stylesheet | CSS went through `{combine}` and the bundled CSSmin | the compiled Tailwind stylesheet is already minified by the build and bypasses `{combine}` entirely | Also avoids `{combine}`'s blanket `'../images/'` replacement inside file content and its relative `url()` rewriting. |
| Plugin stylesheets | `$CSS` went through `{combine}`, which tolerates a plugin pushing a path with a leading slash | each plugin stylesheet is linked with a raw concat onto `$ROOT_PATH`, so it needs `\|ltrim:'/'` | Without it a root install produces `//modules/...`, a protocol-relative URL fetched from host `modules`. Plugins such as silktide deliberately push the leading-slash form for old raw-concat skins. |
| Footer JS | top-level `skins/<skin>/js/*.js` always existed, so `$JS_SCRIPTS` was never empty and `{combine}` never raised its "input cannot be empty" `E_USER_NOTICE` | no top-level `js/*.js` (Atrium's own bundle lives in `js/vendor/`, out of JSMin's reach), so on a store with no CSS/JS plugins the array *is* empty | The `{if $js_foot}` guard around `{combine}` is mandatory. |
