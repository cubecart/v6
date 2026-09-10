{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Optional GOOGLE-HOSTED font, from the "Font" skin setting. Empty by default,
 * and this renders nothing: the skin ships Figtree self-hosted instead.
 *
 * ⚠ The list below is a WHITELIST. Stored select values are not validated
 * against config.xml on the way out, and these land in a <link href> and a
 * <style>. An unknown value matches no key and emits nothing.
 *
 * Every family here serves wght@400;500;600;700 — css2 returns HTTP 400 and no
 * CSS at all if one weight is missing. Loaded before custom.css so a merchant's
 * own --cc-font-sans still wins.
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
{* crossorigin is load-bearing: fonts are fetched in CORS mode. *}
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={$cc_font|replace:' ':'+'}:wght@400;500;600;700&amp;display=swap">
<style>
:root {
   --cc-font-sans: "{$cc_font}", {$cc_google_fonts[$cc_font]};
}
</style>
{/if}
