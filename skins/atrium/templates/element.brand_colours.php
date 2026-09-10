{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Merchant brand colours, from the "Brand Colour" / "Secondary Colour" skin
 * settings (config.xml <settings>, edited in Manage Extensions).
 *
 * Both are EMPTY by default and this whole block then renders nothing, so the
 * skin ships with its own palette and pays nothing for the feature.
 *
 * ⚠ Order matters. This must load AFTER tailwind.css (whose @theme reads these
 * --cc-* names as fallbacks) and BEFORE custom.css, which stays the last word.
 * See element.css.php.
 *
 * Why one colour is enough: every token in theme.css indirects through a --cc-*
 * custom property, so setting --cc-brand-600 alone would already repaint the
 * buttons — but the rest of the ramp would stay blue and the result would look
 * broken. The ramp below is derived from the one colour with color-mix(), which
 * keeps the merchant's hue and needs no build step and no colour maths in PHP.
 *
 * ⚠ The dark block is NOT optional. theme.css lifts brand 50/100/600/700 for
 * dark mode on [data-theme="dark"], which is the SAME specificity as :root, and
 * this file loads after tailwind.css — so a :root-only override beats the lift
 * and leaves a light-mode accent sitting on a near-black page. The mix ratios
 * below reproduce theme.css's own dark values when fed the shipped blue.
 *
 * This setting deliberately overrides whichever colour sub-theme is selected
 * (css/cubecart.<style>.css, linked earlier by element.css.php). A merchant who
 * has typed an exact brand colour means it.
 *
 * ⚠ The values are validated to #rrggbb by GUI::getSkinSettings() on the way
 * out of the config table, not merely on save — they land inside a <style>.
 *}
{if !empty($SKIN_SETTINGS.brand_colour) || !empty($SKIN_SETTINGS.secondary_colour) || !empty($SKIN_SETTINGS.footer_colour)}
<style>
:root {
{if !empty($SKIN_SETTINGS.brand_colour)}
   /* The chosen colour is the 600, which is what the skin paints buttons and
      links with; everything else is that colour lightened or darkened. */
   --cc-brand-50:  color-mix(in oklab, {$SKIN_SETTINGS.brand_colour}  6%, white);
   --cc-brand-100: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 12%, white);
   --cc-brand-200: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 24%, white);
   --cc-brand-300: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 42%, white);
   --cc-brand-400: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 65%, white);
   --cc-brand-500: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 84%, white);
   --cc-brand-600: {$SKIN_SETTINGS.brand_colour};
   --cc-brand-700: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 86%, black);
   --cc-brand-800: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 72%, black);
   --cc-brand-900: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 60%, black);
   --cc-brand-950: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 42%, black);
   /* Label colour for anything painted brand-600. A pale brand colour would
      otherwise keep white button text and be unreadable. */
   --cc-brand-fg: {$SKIN_SETTINGS.brand_colour_fg};
{/if}
{if !empty($SKIN_SETTINGS.secondary_colour)}
   /* The navigation band. Non-inverting by design, so there is one definition
      rather than a light and a dark variant. No sub-theme touches these, so
      this never competes with the theme picker.

      The FOOTER is deliberately not painted from here: components.css remaps
      these same token names to --cc-footer-* inside .cc-footer, so the two
      bands are set separately. */
   --cc-chrome:        {$SKIN_SETTINGS.secondary_colour};
   --cc-chrome-hover:  color-mix(in oklab, {$SKIN_SETTINGS.secondary_colour} 88%, {$SKIN_SETTINGS.secondary_colour_fg});
   --cc-chrome-border: color-mix(in oklab, {$SKIN_SETTINGS.secondary_colour} 82%, {$SKIN_SETTINGS.secondary_colour_fg});
   --cc-chrome-fg:     {$SKIN_SETTINGS.secondary_colour_fg};
   --cc-chrome-muted:  color-mix(in oklab, {$SKIN_SETTINGS.secondary_colour_fg} 65%, {$SKIN_SETTINGS.secondary_colour});
   {* chrome-sale is a red lifted to clear a near-black band. On a LIGHT band
      that lift becomes the contrast problem, so drop back to the normal red. *}
   {if $SKIN_SETTINGS.secondary_colour_fg != '#ffffff'}
   --cc-chrome-sale:   var(--color-danger-700);
   {/if}
{/if}
{if !empty($SKIN_SETTINGS.footer_colour)}
   /* The footer band, read by the .cc-footer block in components.css. Same
      derivation as the nav band above; no -sale token, because the footer has
      no sale link. */
   --cc-footer:        {$SKIN_SETTINGS.footer_colour};
   --cc-footer-hover:  color-mix(in oklab, {$SKIN_SETTINGS.footer_colour} 88%, {$SKIN_SETTINGS.footer_colour_fg});
   --cc-footer-border: color-mix(in oklab, {$SKIN_SETTINGS.footer_colour} 82%, {$SKIN_SETTINGS.footer_colour_fg});
   --cc-footer-fg:     {$SKIN_SETTINGS.footer_colour_fg};
   --cc-footer-muted:  color-mix(in oklab, {$SKIN_SETTINGS.footer_colour_fg} 65%, {$SKIN_SETTINGS.footer_colour});
{/if}
}
{if !empty($SKIN_SETTINGS.brand_colour)}
/* Dark mode reverses the ramp's direction at the accent end: 600 becomes a
   LIGHT tint so it clears a near-black page, and 700, its hover, lighter still.
   Only the four stops theme.css itself overrides are restated here; the others
   inherit from :root above, exactly as the built-in palette does.

   Each stop is declared TWICE on purpose. Mixing toward white raises lightness
   but drags chroma down with it, so the accent washes out; relative colour
   syntax sets the lightness and keeps the hue and (scaled) chroma, which
   reproduces theme.css's own dark values almost exactly when fed the shipped
   blue. A browser too old for oklch(from …) simply keeps the color-mix line,
   which is still a lift and still better than the light ramp on a dark page. */
[data-theme="dark"] {
   --cc-brand-50:  color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 42%, black);
   --cc-brand-50:  oklch(from {$SKIN_SETTINGS.brand_colour} 24.2% calc(c * 0.40) h);
   --cc-brand-100: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 52%, black);
   --cc-brand-100: oklch(from {$SKIN_SETTINGS.brand_colour} 30.4% calc(c * 0.51) h);
   --cc-brand-600: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 62%, white);
   --cc-brand-600: oklch(from {$SKIN_SETTINGS.brand_colour} 68.4% calc(c * 0.86) h);
   --cc-brand-700: color-mix(in oklab, {$SKIN_SETTINGS.brand_colour} 46%, white);
   --cc-brand-700: oklch(from {$SKIN_SETTINGS.brand_colour} 76.2% calc(c * 0.70) h);
}
{/if}
</style>
{/if}
