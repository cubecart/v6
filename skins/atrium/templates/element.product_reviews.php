{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ Form contract — do not rename:
 *   review[anon] review[name] review[email] review[title] review[review]
 *   rating                       (radio, value 1-5, from $RATING_STARS)
 *   id="review_form"             referenced by data-form-id on the submit button
 *   class="g-recaptcha" + data-form-id   how the captcha script finds the form
 *
 * Gravatar: config.xml sets <gravatar_ajax>true</gravatar_ajax>, which stops
 * Catalogue::displayProduct() making a blocking per-review cURL HEAD to
 * gravatar.com and leaves avatar handling to the skin. $review.gravatar is the
 * email hash; the image is loaded directly and hidden on error.
 *}
{if $CTRL_REVIEW}
<div id="element-reviews" x-data="ccTabs('{if $REVIEWS}read{else}read{/if}')">
   <div class="flex flex-wrap items-center justify-between gap-4">
      <h2 id="reviews" class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.catalogue.customer_reviews}</h2>
      {if $REVIEWS}
      <p class="text-sm text-ink-600">
         {$LANG.catalogue.average_rating}: <strong class="text-ink-900">{$REVIEW_AVERAGE}</strong>
      </p>
      {/if}
   </div>

   <div id="review_read" x-show="isActive('read')">
      {if $REVIEWS}
      {if isset($PAGINATION)}<div class="mt-2">{$PAGINATION}</div>{/if}

      <ul role="list" class="mt-6 space-y-6">
         {foreach from=$REVIEWS item=review}
         <li class="cc-card p-5{if $review.gravatar_exists} review_row{/if}" rel="{$review.id}_{$review.gravatar}">
            <div class="flex items-start justify-between gap-4">
               <h3 class="font-medium text-ink-900">{$review.title}</h3>
               <div class="flex shrink-0 items-center gap-0.5" role="img" aria-label="{$review.rating} / 5">
                  {for $i = 1; $i <= 5; $i++}
                  <svg class="size-4 {if $i <= $review.rating}text-warn-600{else}text-ink-300{/if}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                     <path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"/>
                  </svg>
                  {/for}
               </div>
            </div>

            <div class="mt-3 flex gap-4">
               {if $review.gravatar_exists}
               <img class="size-12 shrink-0 rounded-full bg-ink-200 object-cover"
                    id="{$review.id}_{$review.gravatar}"
                    src="https://gravatar.com/avatar/{$review.gravatar}?s=96&d=mp"
                    alt="" loading="lazy"
                    x-data @error="$el.style.display='none'">
               {/if}
               <div class="review_copy min-w-0 text-sm text-ink-700">
                  <p>{$review.review}</p>
                  <p class="mt-2 text-xs text-ink-500">{$review.name}{if !empty($review.date)} &middot; {$review.date}{/if}</p>
               </div>
            </div>
         </li>
         {/foreach}
      </ul>

      {if isset($PAGINATION)}<div class="mt-6">{$PAGINATION}</div>{/if}
      {else}
      <p class="mt-4 text-sm text-ink-600">{$LANG.catalogue.product_not_reviewed}</p>
      {/if}

      <button type="button" class="review_show cc-btn cc-btn-secondary mt-6" @click="select('write')">{$LANG.catalogue.write_a_review}</button>
   </div>

   <div id="review_write" x-show="isActive('write')" x-cloak class="mt-6">
      <h3 class="text-lg font-semibold text-ink-900">{$LANG.catalogue.write_review}</h3>
      <form action="{$VAL_SELF}#reviews_write" id="review_form" data-cc-validate method="post" class="cc-card mt-4 space-y-4 p-5">

         <div class="flex items-center gap-2">
            <input type="checkbox" id="rev_anon" name="review[anon]" value="1">
            <label for="rev_anon" class="text-sm text-ink-800">{$LANG.catalogue.post_anonymously}</label>
         </div>

         <div class="grid gap-4 sm:grid-cols-2">
            <div>
               <label for="rev_name" class="cc-label">{$LANG.common.name}</label>
               <input id="rev_name" type="text" name="review[name]" value="{$WRITE.name|default:''}" required>
            </div>
            <div>
               <label for="rev_email" class="cc-label">{$LANG.common.email} <span class="font-normal text-ink-500">({$LANG.catalogue.internal_use_only})</span></label>
               <input id="rev_email" type="email" name="review[email]" value="{$WRITE.email|default:''}" required>
            </div>
         </div>

         <fieldset id="review_stars">
            <legend class="cc-label">{$LANG.documents.rating}</legend>
            <div class="flex items-center gap-4">
               {foreach from=$RATING_STARS item=star}
               <span class="flex items-center gap-1">
                  <input type="radio" id="rating_{$star.value}" name="rating" value="{$star.value}" class="rating" {$star.checked}>
                  <label for="rating_{$star.value}" class="text-sm text-ink-700">{$star.value}</label>
               </span>
               {/foreach}
            </div>
         </fieldset>

         <div>
            <label for="rev_title" class="cc-label">{$LANG.catalogue.review_title}</label>
            <input id="rev_title" type="text" name="review[title]" value="{$WRITE.title|default:''}" required>
         </div>

         <div>
            <label for="rev_review" class="cc-label">{$LANG.catalogue.review}</label>
            <textarea id="rev_review" name="review[review]" rows="5" required>{$WRITE.review|default:''}</textarea>
         </div>

         {include file='templates/content.recaptcha.php' ga_fid='reviews'}

         <div class="flex flex-wrap gap-3">
            <button type="submit" id="review_submit" data-form-id="review_form" class="g-recaptcha cc-btn cc-btn-primary">{$LANG.catalogue.submit_review}</button>
            <button type="button" class="review_hide cc-btn cc-btn-secondary" @click="select('read')">{$LANG.common.cancel}</button>
         </div>
      </form>
   </div>

   <div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
</div>
{/if}
