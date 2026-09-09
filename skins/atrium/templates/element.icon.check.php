{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * The confirmation tick, shown beside "Added to basket" while the button is in
 * its added state (12-basket.js clears that after 2.5s). The ring sweeps round
 * and the tick draws in behind it: see .cc-check in css/src/components.css.
 *
 * The animation restarts each time x-show displays the element again, so a
 * second add re-draws rather than appearing finished.
 *
 * Params: class (default 'size-5'). Always aria-hidden: the caller pairs it
 * with a text label.
 *}
{if !isset($class)}{assign var=class value='size-5'}{/if}
<svg class="cc-check {$class}" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
   <circle cx="12" cy="12" r="9" stroke-width="2"/>
   <path d="m7.75 12.5 2.9 2.9 5.6-6.4" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
