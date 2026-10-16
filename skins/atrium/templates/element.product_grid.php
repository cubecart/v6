{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Products Per Row setting, resolved into $cc_grid_cols (the listing grid's
 * column classes) and $cc_grid_row (products in the widest row, which must not
 * be lazy loaded).
 *
 * scope='global' is REQUIRED, see element.media_fit.php. Every class string is
 * spelled out literally so the CSS build keeps it.
 *}
{if ($SKIN_SETTINGS.products_per_row|default:'4') == '3'}
{assign var='cc_grid_cols' value='grid-cols-2 lg:grid-cols-3' scope='global'}
{assign var='cc_grid_row' value=3 scope='global'}
{elseif ($SKIN_SETTINGS.products_per_row|default:'4') == '5'}
{assign var='cc_grid_cols' value='grid-cols-2 lg:grid-cols-4 xl:grid-cols-5' scope='global'}
{assign var='cc_grid_row' value=5 scope='global'}
{else}
{assign var='cc_grid_cols' value='grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' scope='global'}
{assign var='cc_grid_row' value=4 scope='global'}
{/if}
