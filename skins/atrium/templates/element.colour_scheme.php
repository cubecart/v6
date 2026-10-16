{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Colour Scheme "Follow browser": stamp data-theme from prefers-color-scheme
 * before first paint, and keep following it if the device switches. Light and
 * Dark are stamped on <html> by each main template instead.
 *
 * Must stay inline and in <head>, after the stylesheets, or the wrong theme
 * flashes.
 *}
{if ($SKIN_SETTINGS.colour_scheme|default:'light') == 'auto'}
<script>{literal}(function(){try{var m=matchMedia('(prefers-color-scheme: dark)'),d=document.documentElement,s=function(){if(m.matches){d.setAttribute('data-theme','dark');}else{d.removeAttribute('data-theme');}};s();if(m.addEventListener){m.addEventListener('change',s);}else if(m.addListener){m.addListener(s);}}catch(e){}})();{/literal}</script>
{/if}
