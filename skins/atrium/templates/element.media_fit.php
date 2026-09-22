{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Image Fit setting, resolved once per request into $cc_media_fit, the class
 * every square catalogue thumbnail carries.
 *
 * scope='global' is REQUIRED — a plain {assign} inside an {include} is local to
 * this template, so the variable would be empty everywhere it is used.
 *
 * Both class names are spelled out literally: the CSS build scans the templates
 * and would drop whichever one no template mentions.
 *}
{if ($SKIN_SETTINGS.image_fit|default:'cover') == 'contain'}
{assign var='cc_media_fit' value='object-contain' scope='global'}
{else}
{assign var='cc_media_fit' value='object-cover' scope='global'}
{/if}
