{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<style>
   .perf-page { max-width: 900px; }
   .perf-page .perf-intro { color: var(--text-muted); margin: 0 0 20px; font-size: 14px; line-height: 1.5; }
   .perf-page .perf-score-summary { display: flex; align-items: center; gap: 16px; padding: 14px 18px; border-radius: 6px; margin-bottom: 20px; border: 1px solid var(--border); background: var(--panel); }
   .perf-page .perf-score-summary.perf-score-3 { border-color: rgba(92,184,92,0.4); background: rgba(92,184,92,0.08); }
   .perf-page .perf-score-summary.perf-score-2,
   .perf-page .perf-score-summary.perf-score-1 { border-color: rgba(240,173,78,0.4); background: rgba(240,173,78,0.08); }
   .perf-page .perf-score-summary.perf-score-0 { border-color: rgba(217,83,79,0.4); background: rgba(217,83,79,0.08); }
   .perf-page .perf-score-num { font-size: 26px; font-weight: 700; padding: 6px 14px; border-radius: 6px; background: rgba(0,0,0,0.05); }
   .perf-page .perf-score-summary.perf-score-3 .perf-score-num { background: rgba(92,184,92,0.2); color: #3c763d; }
   .perf-page .perf-score-summary.perf-score-2 .perf-score-num,
   .perf-page .perf-score-summary.perf-score-1 .perf-score-num { background: rgba(240,173,78,0.2); color: #8a6d3b; }
   .perf-page .perf-score-summary.perf-score-0 .perf-score-num { background: rgba(217,83,79,0.2); color: #a94442; }
   .perf-page .perf-score-text { font-size: 14px; line-height: 1.4; }
   .perf-page .perf-card { padding: 18px 20px; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 16px; background: var(--panel); display: grid; grid-template-columns: 40px 1fr auto; gap: 16px; align-items: start; }
   .perf-page .perf-card .perf-status-icon { font-size: 26px; text-align: center; padding-top: 3px; }
   .perf-page .perf-card .perf-status-icon.perf-on  { color: #5cb85c; }
   .perf-page .perf-card .perf-status-icon.perf-off { color: #d9534f; }
   .perf-page .perf-card h4 { margin: 0 0 4px; font-size: 16px; }
   .perf-page .perf-card .perf-status-label { font-size: 12px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
   .perf-page .perf-card .perf-status-label.perf-on  { color: #3c763d; }
   .perf-page .perf-card .perf-status-label.perf-off { color: #a94442; }
   .perf-page .perf-card p { margin: 8px 0 0; line-height: 1.5; color: var(--text-muted); font-size: 13px; }
   .perf-page .perf-cta-btn { display: inline-block; padding: 8px 14px; background: #0072bc; color: #fff; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 600; white-space: nowrap; border: none; cursor: pointer; }
   .perf-page .perf-cta-btn:hover { background: #005a93; color: #fff; }
   .perf-page .perf-recheck-wrap { margin-left: auto; }
   .perf-page .perf-recheck { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; background: var(--panel); color: var(--text); border: 1px solid var(--border); border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600; white-space: nowrap; transition: background .15s, border-color .15s; }
   .perf-page .perf-recheck:hover { background: var(--panel-muted); border-color: var(--text-muted); color: var(--text); text-decoration: none; }
   .perf-page .perf-recheck .fa { font-size: 12px; color: var(--text-muted); }
   .perf-page .perf-cloud { margin-top: 28px; padding: 24px 28px; border-radius: 8px; background: linear-gradient(135deg, #0072bc 0%, #00aeef 100%); color: #fff; box-shadow: 0 4px 16px rgba(0,114,188,0.25); }
   .perf-page .perf-cloud h3,
   .perf-page .perf-cloud h3 i { color: #fff !important; margin: 0 0 10px; font-size: 20px; }
   .perf-page .perf-cloud p { margin: 0 0 16px; line-height: 1.55; color: #fff; font-size: 14px; }
   .perf-page .perf-cloud ul { list-style: none; padding: 0; margin: 0 0 18px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 8px 20px; }
   .perf-page .perf-cloud ul li { color: #fff; font-size: 13px; line-height: 1.5; padding-left: 22px; position: relative; }
   .perf-page .perf-cloud ul li:before { content: "\f00c"; font-family: FontAwesome; position: absolute; left: 0; top: 1px; color: rgba(255,255,255,0.95); font-size: 12px; }
   .perf-page .perf-cloud .perf-cta-wrap { text-align: center; margin: 28px 0 12px; }
   .perf-page .perf-cloud a.perf-cta-btn { background: #fff; color: #0072bc !important; padding: 12px 22px; font-size: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
   .perf-page .perf-cloud a.perf-cta-btn:hover { background: #e8f4fd; color: #005a93 !important; }
</style>

<div id="performance" class="tab_content perf-page">
   <h3><i class="fa fa-tachometer"></i> Performance</h3>
   <p class="perf-intro">These three components make CubeCart fast. Each one solves a different bottleneck. Most basic hosting plans don't include them — that's where CubeCart&rsquo;s own hosting comes in.</p>

   <div class="perf-score-summary perf-score-{$PERFORMANCE.enabled_count}">
      <span class="perf-score-num">{$PERFORMANCE.enabled_count}/3</span>
      <div class="perf-score-text">
         {if $PERFORMANCE.enabled_count == 3}
         <strong>Everything's switched on.</strong> Your store has all three performance features active.
         {elseif $PERFORMANCE.enabled_count == 0}
         <strong>None of these are active.</strong> Your store is running without any performance acceleration.
         {else}
         <strong>{$PERFORMANCE.enabled_count} of 3 active.</strong> You're getting some of the speed benefits &mdash; see below for what's missing.
         {/if}
      </div>
      <div class="perf-recheck-wrap"><a href="?_g=performance&refresh=1" class="perf-recheck"><i class="fa fa-refresh"></i> Re-check</a></div>
   </div>

   {* Memory Cache *}
   <div class="perf-card">
      <div class="perf-status-icon {if $PERFORMANCE.memcache}perf-on{else}perf-off{/if}"><i class="fa fa-{if $PERFORMANCE.memcache}check-circle{else}times-circle{/if}"></i></div>
      <div>
         <h4>Memory Cache{if $PERFORMANCE.memcache} <small>({$PERFORMANCE.cache_driver})</small>{/if}</h4>
         <span class="perf-status-label {if $PERFORMANCE.memcache}perf-on{else}perf-off{/if}">{if $PERFORMANCE.memcache}Active{else}Not active{/if}</span>
         <p>CubeCart already remembers frequently-used information so it doesn&rsquo;t have to look it up every time &mdash; but by default it saves that information to files on the server&rsquo;s hard drive. A memory cache keeps the same information in the server&rsquo;s memory (RAM) instead, which is many times faster than reading from disk. You&rsquo;ll especially notice the difference under busy traffic, during sales, and on pages that pull together lots of data like the home page, category listings and checkout.</p>
      </div>
      {if !$PERFORMANCE.memcache}
      <div><a href="https://www.cubecart.com/hosting" target="_blank" class="perf-cta-btn">Get this &rarr;</a></div>
      {/if}
   </div>

   {* Elasticsearch *}
   <div class="perf-card">
      <div class="perf-status-icon {if $PERFORMANCE.elasticsearch}perf-on{else}perf-off{/if}"><i class="fa fa-{if $PERFORMANCE.elasticsearch}check-circle{else}times-circle{/if}"></i></div>
      <div>
         <h4>Search Engine (Elasticsearch)</h4>
         <span class="perf-status-label {if $PERFORMANCE.elasticsearch}perf-on{else}perf-off{/if}">{if $PERFORMANCE.elasticsearch}Active{else}Not active{/if}</span>
         <p>When a shopper types into the search box, your store has to read every product name, description and category to find matches. This gets slow quickly once you have more than a few hundred products, and the results aren&rsquo;t always the most relevant. A dedicated search engine finds the right products in milliseconds no matter how big your catalogue is, and ranks them by relevance so customers see the best matches first. Stores that make searching easy sell more.</p>
      </div>
      {if !$PERFORMANCE.elasticsearch}
      <div><a href="https://www.cubecart.com/hosting" target="_blank" class="perf-cta-btn">Get this &rarr;</a></div>
      {/if}
   </div>

   {* CDN *}
   <div class="perf-card">
      <div class="perf-status-icon {if $PERFORMANCE.cdn}perf-on{else}perf-off{/if}"><i class="fa fa-{if $PERFORMANCE.cdn}check-circle{else}times-circle{/if}"></i></div>
      <div>
         <h4>Content Delivery Network (CDN){if $PERFORMANCE.cdn && $PERFORMANCE.cdn_provider} <small>({$PERFORMANCE.cdn_provider})</small>{/if}</h4>
         <span class="perf-status-label {if $PERFORMANCE.cdn}perf-on{else}perf-off{/if}">{if $PERFORMANCE.cdn}Active{else}Not detected{/if}</span>
         {if $PERFORMANCE.cdn}
            {if $PERFORMANCE.cdn_provider == 'Cloudflare'}
               <p>Cloudflare is proxying your store, caching static assets at edge locations worldwide and adding DDoS protection and a basic web application firewall. Visitors download your images, CSS and JavaScript from a server near them rather than your origin.</p>
            {elseif $PERFORMANCE.cdn_provider == 'AWS CloudFront'}
               <p>Amazon CloudFront is delivering your content from AWS's global edge network. Static assets are cached close to each visitor, reducing load on your origin and speeding up page loads worldwide.</p>
            {elseif $PERFORMANCE.cdn_provider == 'Fastly'}
               <p>Fastly's edge network is accelerating your store with real-time cache control. Static assets are served from points of presence near each visitor.</p>
            {elseif $PERFORMANCE.cdn_provider == 'Akamai'}
               <p>Akamai's enterprise edge network is delivering your content from servers near each visitor, giving consistently fast page loads at any scale.</p>
            {elseif $PERFORMANCE.cdn_provider == 'Sucuri'}
               <p>Sucuri is proxying your store through its CDN and website firewall &mdash; combining global content delivery with malware scanning and attack protection.</p>
            {elseif $PERFORMANCE.cdn_provider == 'Azure CDN'}
               <p>Microsoft Azure CDN is serving your content from its global edge network, caching static assets close to each visitor.</p>
            {elseif $PERFORMANCE.cdn_provider == 'Google Cloud CDN'}
               <p>Google Cloud CDN is distributing your content from Google's edge network, putting your static assets near each shopper.</p>
            {else}
               <p>A CDN is in front of your store, serving static assets from edge locations near each visitor.</p>
            {/if}
         {else}
            <p>Your product images, logos and styles are probably served from a single server in one country. A shopper halfway around the world has to wait while all those files travel across the globe before the page finishes loading. A CDN keeps copies of your images on servers all over the world, so every customer downloads them from somewhere nearby. Pages feel instant, bounce rates drop, and Google rewards the faster page speed with better rankings.</p>
         {/if}
         <p style="font-size:11px;color:var(--text-muted);margin-top:10px;font-style:italic;">CDN detection is informational. If your CDN strips identifying response headers or your server resolves its own hostname locally, a real CDN may not be detected here even when it's serving your visitors. This check should never be relied on for security or billing purposes.</p>
      </div>
      {if !$PERFORMANCE.cdn}
      <div><a href="https://www.cubecart.com/hosting" target="_blank" class="perf-cta-btn">Get this &rarr;</a></div>
      {/if}
   </div>

   {if $PERFORMANCE.enabled_count < 3}
   <div class="perf-cloud">
      <h3><i class="fa fa-cloud"></i> Host your store with CubeCart</h3>
      <p>Most basic web hosting can&rsquo;t run the performance services above &mdash; they need specific software that shared hosting providers don&rsquo;t offer. Our own hosting is built by the people who make CubeCart, with everything configured, maintained and included in the price.</p>
      <ul>
         <li>Memory caching, Elasticsearch and CDN all included</li>
         <li>Support from the official CubeCart developers</li>
         <li>Managed upgrades &mdash; we keep your store on the latest version while respecting code customisations</li>
         <li>Daily backups with easy one-click restore</li>
         <li>SSL certificate included and renewed automatically</li>
         <li>PHP, database and server software kept secure and up to date &mdash; powered by CloudLinux</li>
         <li>Security patches applied as soon as they&rsquo;re released</li>
         <li>Free migration from your current host</li>
      </ul>
      <div class="perf-cta-wrap"><a href="https://www.cubecart.com/hosting" target="_blank" class="perf-cta-btn">Learn about CubeCart hosting &rarr;</a></div>
   </div>
   {/if}
</div>
