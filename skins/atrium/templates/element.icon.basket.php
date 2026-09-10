{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * The basket icon, in one place: the header button and every add-to-basket
 * button in a listing draw it from here.
 *
 * Params:
 *   class    default 'size-5'
 *   variant  'solid' for the filled cart, anything else for the outline.
 *            box.basket.php renders BOTH and shows one, so a full basket reads
 *            as full at a glance. Add-to-basket buttons in listings are an
 *            action, not a state, and stay outline.
 *
 * Always aria-hidden — every caller pairs it with a text label, visible or
 * screen-reader only.
 *}
{if !isset($class)}{assign var=class value='size-5'}{/if}
{if isset($variant) && $variant == 'solid'}
<svg class="{$class}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
   <path d="M2.25 2.25a.75.75 0 0 0 0 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 0 0-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 0 0 0-1.5H5.378A2.25 2.25 0 0 1 7.5 15h11.218a.75.75 0 0 0 .674-.421 60.358 60.358 0 0 0 2.96-7.228.75.75 0 0 0-.525-.965A60.864 60.864 0 0 0 5.68 4.509l-.232-.867A1.875 1.875 0 0 0 3.636 2.25H2.25ZM3.75 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0ZM16.5 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z"/>
</svg>
{else}
<svg class="{$class}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
   <path d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
{/if}
