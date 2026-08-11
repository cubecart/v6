{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * The compiled Tailwind stylesheet deliberately does NOT pass through {combine}.
 * It is already minified by the Tailwind build, and smarty_build_combine() also
 * blanket-replaces '../images/' inside file CONTENT and rewrites relative url()
 * paths — transforms this stylesheet neither needs nor wants.
 *
 * ⚠ CSSmin (includes/smarty/plugins/minify/CSSmin.php), which {combine} runs,
 * strips units from zero values, and that breaks calc() on UNREGISTERED custom
 * properties: `--gap: 0rem` becomes `--gap: 0`, so `calc(1rem + var(--gap))` is
 * invalid and the whole declaration is dropped. Never give a hand-authored token
 * in custom.css a bare zero length that calc() later consumes — use 0.001rem, or
 * keep it out of calc().
 *
 * ✗ NOT a problem, despite the claim: the same unit-stripping does not kill
 *   ring-* utilities. Unitless zero is a valid <length>, so Tailwind's
 *   `@property --tw-ring-offset-width` registration still succeeds and computes
 *   to 0px. Tailwind's own --minify does the same transform. Do not re-justify
 *   this file on that basis.
 *}
{include file='templates/element.css.version.php'}
<link rel="stylesheet" href="{$ROOT_PATH}skins/{$SKIN_FOLDER}/css/tailwind.css?v={$CSS_VERSION}">
{if !empty($SKIN_SUBSET)}
<link rel="stylesheet" href="{$ROOT_PATH}skins/{$SKIN_FOLDER}/css/cubecart.{$SKIN_SUBSET}.css?v={$CSS_VERSION}">
{/if}
{* Plugin stylesheets arrive via the class.gui.css hook. Keep this loop or every
   plugin that ships CSS loses its styling.

   ⚠ |ltrim:'/' is load-bearing. $ROOT_PATH (CC_ROOT_REL) always ends in a slash
   and is exactly "/" on a root install, while plugins may push a path with or
   without a leading slash (silktide's class.gui.css hook pushes one). A raw
   concat then yields "//modules/…", which the browser reads as a
   PROTOCOL-RELATIVE URL and fetches from host "modules": the stylesheet 404s
   and, in silktide's case, the cookie banner ships unstyled. Only bites root
   installs — a subdirectory install collapses "/v6//modules/…". ltrim is
   pre-registered in the modifier allowlist in
   cubecart_smarty_security.class.php. *}
{foreach from=$CSS item=css_file}
<link rel="stylesheet" href="{$ROOT_PATH}{$css_file|ltrim:'/'}">
{/foreach}
{* Merchant overrides load LAST so they win. custom.css needs no rebuild and is
   the only edit point a skin update will not overwrite. *}
<link rel="stylesheet" href="{$ROOT_PATH}skins/{$SKIN_FOLDER}/css/custom.css?v={$CSS_VERSION}">
