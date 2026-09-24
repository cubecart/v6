{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Logo Size skin setting. Nothing renders at the default. See #4290.
 * Only listed values are emitted: selects are not re-validated on the way
 * out of the config table, and this lands in a <style> block.
 *}
{$cc_logo_sizes = ['3' => '4rem', '3.5' => '4rem', '4' => '4.5rem', '4.5' => '5rem', '5' => '5.5rem']}
{if isset($SKIN_SETTINGS.logo_height) && isset($cc_logo_sizes[$SKIN_SETTINGS.logo_height])}
<style>
:root {
   --cc-logo-height: {$SKIN_SETTINGS.logo_height}rem;
   --cc-header-height: {$cc_logo_sizes[$SKIN_SETTINGS.logo_height]};
}
</style>
{/if}
