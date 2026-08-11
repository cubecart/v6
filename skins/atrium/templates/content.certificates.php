{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by core.
 *
 * Field contract: gc[value] gc[method] gc[name] gc[email] gc[message]
 * $GC.delivery: 1 = email only, 2 = post only, 3 = both. The recipient email
 * field only exists when email delivery is possible, so its required state is
 * bound to the method select rather than fixed.
 *
 * ⚠ $ctrl_allow_purchase is LOWER CASE here, unlike $CTRL_* elsewhere.
 *}
<form id="gc_form" action="{$VAL_SELF}" method="post" class="mx-auto max-w-2xl"
      x-data="ccGiftCertificate('{if in_array($GC.delivery, array(1,3))}e{else}m{/if}')">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.catalogue.gift_certificates}</h1>
   <p class="mt-2 text-sm text-ink-600">{$LANG_CERT_VALUES}</p>

   {if !empty($GC.image)}
   <img src="{$GC.image}"
        alt="{if isset($GC.image_tags.alt)}{$GC.image_tags.alt}{else}{$LANG.catalogue.gift_certificates}{/if}"
        {if isset($GC.image_tags.title)}title="{$GC.image_tags.title}"{/if}
        class="mt-6 w-full rounded-cc-lg">
   {/if}

   <div class="cc-card mt-6 space-y-4 p-5">
      <div>
         <label for="gc-value" class="cc-label">{$LANG.common.value} ({$CONFIG.default_currency})</label>
         <input type="text" name="gc[value]" id="gc-value" value="{$POST.value}" required inputmode="decimal">
      </div>

      <div>
         <label for="gc-method" class="cc-label">{$LANG.catalogue.delivery_method}</label>
         <select name="gc[method]" id="gc-method" x-model="method">
            {if in_array($GC.delivery, array(1,3))}<option value="e">{$LANG.common.email}</option>{/if}
            {if in_array($GC.delivery, array(2,3))}<option value="m">{$LANG.common.post}</option>{/if}
         </select>
      </div>

      <div>
         <label for="gc-name" class="cc-label">{$LANG.catalogue.recipient_name}</label>
         <input type="text" name="gc[name]" id="gc-name" value="{$POST.name}" required>
      </div>

      {if in_array($GC.delivery, array(1,3))}
      <div id="gc-method-e" x-show="method === 'e'" x-cloak x-collapse>
         <label for="gc-email" class="cc-label">{$LANG.catalogue.recipient_email}</label>
         <input type="email" name="gc[email]" id="gc-email" value="{$POST.email}" :required="method === 'e'">
      </div>
      {/if}

      <div>
         <label for="gc-message" class="cc-label">{$LANG.common.message} <span class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
         <textarea name="gc[message]" id="gc-message" rows="3">{$POST.message}</textarea>
      </div>
   </div>

   <button type="submit" name="Submit" value="{$LANG.catalogue.add_to_basket}"
           class="cc-btn cc-btn-primary mt-6 w-full"{if !$ctrl_allow_purchase} disabled{/if}>
      {$LANG.catalogue.add_to_basket}
   </button>

   <div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
   <div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
</form>
