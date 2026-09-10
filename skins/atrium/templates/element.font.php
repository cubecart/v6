{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Optional Google Font, from the "Font" skin setting. EMPTY BY DEFAULT, and the
 * whole block then renders nothing — the skin ships on system stacks and pays
 * nothing for the feature.
 *
 * ⚠ Opting in adds a third-party request to EVERY page, checkout included: a
 * render-blocking dependency, a transfer of the visitor's IP to Google (which a
 * German court has held to be a GDPR breach), and one more entry for a PCI
 * script inventory. That is why it is off unless a merchant chooses it, and why
 * self-hosting via custom.css is still the better answer for a store that cares.
 *
 * ⚠ THE LIST BELOW IS THE WHITELIST, not a convenience. $SKIN_SETTINGS values
 * are not validated against config.xml's <options> on the way out of the config
 * table, so a row edited straight in the database would otherwise land inside a
 * <link href> and a <style>. An unrecognised value simply matches no key here
 * and nothing is emitted. Keep it in step with config.xml.
 *
 * Every family here was checked to serve wght@400;500;600;700, which is what
 * the skin actually uses. css2 returns HTTP 400 and NO CSS AT ALL if one weight
 * is missing, so a family that lacks any of them cannot be added without
 * giving it its own axis string.
 *
 * Loaded before custom.css (see element.css.php) so a merchant's own
 * --cc-font-sans still wins.
 *}
{assign var='cc_google_fonts' value=[
   'Inter'            => 'sans-serif',
   'Roboto'           => 'sans-serif',
   'Open Sans'        => 'sans-serif',
   'Lato'             => 'sans-serif',
   'Montserrat'       => 'sans-serif',
   'Poppins'          => 'sans-serif',
   'Nunito Sans'      => 'sans-serif',
   'Source Sans 3'    => 'sans-serif',
   'Work Sans'        => 'sans-serif',
   'DM Sans'          => 'sans-serif',
   'Figtree'          => 'sans-serif',
   'Rubik'            => 'sans-serif',
   'Karla'            => 'sans-serif',
   'Raleway'          => 'sans-serif',
   'Lora'             => 'serif',
   'Merriweather'     => 'serif',
   'Playfair Display' => 'serif'
]}
{if !empty($SKIN_SETTINGS.google_font) && isset($cc_google_fonts[$SKIN_SETTINGS.google_font])}
{assign var='cc_font' value=$SKIN_SETTINGS.google_font}
<link rel="preconnect" href="https://fonts.googleapis.com">
{* crossorigin is load-bearing: the font FILES come from gstatic and are fetched
   in CORS mode, so a preconnect without it opens a connection that cannot be
   reused and the hint is wasted. *}
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={$cc_font|replace:' ':'+'}:wght@400;500;600;700&amp;display=swap">
{* display=swap above: text paints in the fallback immediately and reflows when
   the webfont lands, rather than staying invisible for up to 3s. *}
<style>
:root {
   --cc-font-sans: "{$cc_font}", {$cc_google_fonts[$cc_font]};
}
</style>
{/if}
