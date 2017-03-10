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
{if $RECAPTCHA==='1' || $RECAPTCHA==='2'}
<h3>{$LANG.form.verify_human}</h3>
<div class="row">
   <div class="medium-8 columns">
      {if $RECAPTCHA==='2'}
      {if empty($CONFIG.recaptcha_public_key) || empty($CONFIG.recaptcha_secret_key)}
      <p>{$LANG.form.recaptcha_key_not_set}</p>
      {else}
      <div class="g-recaptcha" data-sitekey="{$CONFIG.recaptcha_public_key}"></div>
      {/if}
      {else}
      <script type="text/javascript">
         var RecaptchaOptions = {
            theme : 'custom',
            custom_theme_widget: 'recaptcha_widget'
         };
      </script>
      <div id="recaptcha_widget" style="display:none">
         <div class="row">
            <div id="recaptcha_image" class="small-8 columns"></div>
            <div class="small-4 columns">
            <a href="javascript:Recaptcha.reload()"><svg class="icon" title="{$LANG.form.recaptcha_try_diff_img}"><use xlink:href="#icon-refresh"></use></svg></a>
            <span class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')"><svg class="icon" title="{$LANG.form.recaptcha_get_audio}"><use xlink:href="#icon-volume-up"></use></svg></a></span>
            <span class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')"><svg class="icon" title="{$LANG.form.recaptcha_get_img}"><use xlink:href="#icon-picture-o"></use></svg></a></span>
         <span><a href="javascript:Recaptcha.showhelp()"><svg class="icon" title="{$LANG.common.help}"><use xlink:href="#icon-info-circle"></use></svg></a></span>
            </div>
         </div>
         <div class="recaptcha_only_if_incorrect_sol error">{$LANG.form.recaptcha_incorrect}</div>
         <span class="recaptcha_only_if_image"><label for="recaptcha_response_field">{$LANG.form.recaptcha_enter_words}</label></span>
         <span class="recaptcha_only_if_audio">{$LANG.form.recaptcha_enter_numbers}</span>
         <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" required />
      </div>
      <script type="text/javascript"
         src="//www.google.com/recaptcha/api/challenge?k=6LfT4sASAAAAAOl71cRz11Fm0erGiqNG8VAfKTHn"></script>
      <noscript>
         <iframe src="//www.google.com/recaptcha/api/noscript?k=6LfT4sASAAAAAOl71cRz11Fm0erGiqNG8VAfKTHn"
            height="300" width="500" frameborder="0"></iframe><br>
         <textarea name="recaptcha_challenge_field" rows="3" cols="40">
         </textarea>
         <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
      </noscript>
      {/if}
   </div>
</div>
{/if}