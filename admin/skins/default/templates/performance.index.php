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
