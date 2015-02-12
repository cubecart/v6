{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<div class="element-social">
   <h3>{$LANG.common.follow_us}</h3>
   <ul class="small-block-grid-4 no-bullet nomarg social-icons text-left">
      {if !empty($CONFIG.twitter)}
      <li><a href="https://twitter.com/{$CONFIG.twitter}" title="Twitter"><i class="fa fa-twitter"></i></a></li>
      {/if}
      {if !empty($CONFIG.facebook)}
      <li><a href="https://www.facebook.com/{$CONFIG.facebook}" title="Facebook"><i class="fa fa-facebook"></i></a></li>
      {/if}
      {if !empty($CONFIG.google_plus)}
      <li><a href="https://plus.google.com/{$CONFIG.google-plus}" title="Google+"><i class="fa fa-google-plus"></i></a></li>
      {/if}
      {if !empty($CONFIG.pinterest)}
      <li><a href="http://www.pinterest.com/{$CONFIG.pinterest}" title="Pinterest"><i class="fa fa-pinterest"></i></a></li>
      {/if}
      {if !empty($CONFIG.youtube)}
      <li><a href="http://www.youtube.com/user/{$CONFIG.youtube}" title="YouTube"><i class="fa fa-youtube"></i></a></li>
      {/if}
      {if !empty($CONFIG.instagram)}
      <li><a href="https://instagram.com/{$CONFIG.instagram}" title="Instagram"><i class="fa fa-instagram"></i></a></li>
      {/if}
      {if !empty($CONFIG.flickr)}
      <li><a href="http://www.flickr.com/photos/{$CONFIG.flickr}" title="Flickr"><i class="fa fa-flickr"></i></a></li>
      {/if}
      {if !empty($CONFIG.linkedin)}
      <li><a href="http://www.linkedin.com/company/{$CONFIG.linkedin}" title="LinkedIn"><i class="fa fa-linkedin"></i></a></li>
      {/if}
   </ul>
</div>