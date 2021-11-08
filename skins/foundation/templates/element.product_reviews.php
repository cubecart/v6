{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if $CTRL_REVIEW}
<div id="element-reviews">
   <h2 id="reviews">{$LANG.catalogue.customer_reviews}</h2>
   <div id="review_read">
      {if $REVIEWS}
      <div class="pagination_top"><span class="pagination">{if isset($PAGINATION)}{$PAGINATION}{/if}</span>{$LANG.catalogue.average_rating}: <strong>{$REVIEW_AVERAGE}</strong></div>
      {foreach from=$REVIEWS item=review}
      <div class="panel" itemprop="review" itemscope itemtype="http://schema.org/Review">
         <meta itemprop="datePublished" content="{$review.date_schema}">
         <div class="row">
            <div class="medium-9 columns">
               <h3>{$review.title}</h3>
            </div>
            <div class="medium-3 columns text-right" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
               {for $i = 1; $i <= 5; $i++}
               {if $i <= $review.rating}
               <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star.png" alt="{$i}">
               {else}
               <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_off.png" alt="{$i}">
               {/if}
               {/for}
               <meta itemprop="worstRating" content="0">
               <meta itemprop="ratingValue" content="{$review.rating}">
               <meta itemprop="bestRating" content="5">
            </div>
         </div>
         <div class="row review_row" rel="{$review.id}_{$review.gravatar}">
            {if $review.gravatar_exists}
            <div class="small-3 medium-2 columns gravatar">
               <a href="http://gravatar.com/emails/"><img class="th marg-right" id="{$review.id}_{$review.gravatar}" src="" align="left"></a>
            </div>
            {/if}
            <div class="{if $review.gravatar_exists}small-9 medium-10{else}small-12{/if} columns review_copy">
            <blockquote><span itemprop="description">{$review.review}</span><cite><span itemprop="author" itemscope itemtype="https://schema.org/Person"><span itemprop="name">{$review.name}</span></span>{if !empty($review.date)} ({$review.date}){/if}</cite></blockquote>
            </div>
         </div>
      </div>
      {/foreach}
      {if isset($PAGINATION)}{$PAGINATION}{/if}
      <a href="#" class="button review_show">{$LANG.catalogue.write_a_review}</a>
      {else}
      <p>{$LANG.catalogue.product_not_reviewed}</p>
      <a href="#" class="button review_show">{$LANG.catalogue.write_a_review}</a>
      {/if}
   </div>
   <div id="review_write" class="hide">
      <h3>{$LANG.catalogue.write_review}</h3>
      <form action="{$VAL_SELF}#reviews_write" id="review_form" method="post">
         <div class="panel">
            {if $IS_USER}
            <div class="row">
               <div class="small-12 columns"><input type="checkbox" id="rev_anon" name="review[anon]" value="1"> <label for="rev_anon">{$LANG.catalogue.post_anonymously}</label></div>
            </div>
            {else}
            <div class="row">
               <div class="small-12 columns"><label for="rev_name">{$LANG.common.name}</label><input id="rev_name" type="text" name="review[name]" value="{$WRITE.name}" placeholder="{$LANG.common.name} {$LANG.form.required}" required></div>
            </div>
            <div class="row">
               <div class="small-12 columns"><label for="rev_email">{$LANG.common.email}</label><input id="rev_email" type="text" name="review[email]" value="{$WRITE.email}" placeholder="{$LANG.common.email} {$LANG.form.required}" required></div>
            </div>
            {/if}
            <div class="row">
               <div class="small-12 columns" id="review_stars">
                  <label for="rating">{$LANG.documents.rating}</label>
                  {foreach from=$RATING_STARS item=star}
                  <input type="radio" id="rating_{$star.value}" name="rating" value="{$star.value}" class="rating" {$star.checked}>
                  {/foreach}
               </div>
            </div>
            <div class="row">
               <div class="small-12 columns"><label for="rev_title" class="inline">{$LANG.catalogue.review_title}</label><input id="rev_title" type="text" name="review[title]" value="{$WRITE.title}" placeholder="{$LANG.catalogue.review_title} {$LANG.form.required}" required></div>
            </div>
            <div class="row">
               <div class="small-12 columns"><label for="rev_review" class="return">{$LANG.catalogue.review}</label><textarea id="rev_review" name="review[review]" placeholder="{$LANG.catalogue.review} {$LANG.form.required}" required>{$WRITE.review}</textarea></div>
            </div>
            {include file='templates/content.recaptcha.php'}
         </div>
         <div class="clearfix">
            <input type="submit" value="{$LANG.catalogue.submit_review}" data-form-id="review_form" id="review_submit" class="g-recaptcha button">
            <input type="button" value="{$LANG.common.cancel}" class="button secondary right review_hide">
         </div>
      </form>
   </div>
   <div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
</div>
{/if}