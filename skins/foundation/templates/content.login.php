{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<div class="row">
   <div class="large-6 columns">
      <form action="{$VAL_SELF}" id="login_form" method="post">
         <p class="show-for-small-only">{$LANG.account.want_to_signup} <a href="{$STORE_URL}/register{$CONFIG.seo_ext}">{$LANG.account.register_here}</a></p>
         <h2>{$LANG.account.login}</h2>
         {foreach from=$LOGIN_HTML item=html}
         {$html}
         {/foreach}
         <div class="row">
            <div class="small-12 columns">
               <label for="login-username" class="show-for-medium-up">{$LANG.user.email_address}</label>
               <input type="text" autocomplete="off" name="username" id="login-username" placeholder="{$LANG.user.email_address} {$LANG.form.required}" value="{$USERNAME}" required>
            </div>
         </div>
         <div class="row">
            <div class="small-12 columns">
               <label for="login-password" class="show-for-medium-up">{$LANG.account.password}</label><input type="password" maxlength="64" autocomplete="off" name="password" id="login-password" placeholder="{$LANG.account.password} {$LANG.form.required}" required>
            </div>
         </div>
         <div class="row">
            <div class="small-12 columns">
               <p><a href="{$URL.recover}">{$LANG.account.forgotten_password}</a></p>
            </div>
         </div>
         <div class="row">
            <div class="small-12 columns"><input type="checkbox" name="remember" id="login-remember" value="1" {if $REMEMBER}checked{/if}><label for="login-remember">{$LANG.account.remember_me}</label></div>
         </div>
         <div class="row">
            <div class="small-12 columns">
               <button name="submit" type="submit" class="button"><svg class="icon"><use xlink:href="#icon-sign-in"></use></svg> {$LANG.account.log_in}</button>
            </div>
         </div>
         <input type="hidden" name="redir" value="{$REDIRECT_TO}">
      </form>
   </div>
   <div class="large-6 columns show-for-medium-up">
      <h2>{$LANG.account.register}</h2>
      <p>{$LANG.account.register_welcome}</p>
      <a href="{$URL.register}" class="button">{$LANG.account.register}</a>
   </div>
</div>
<div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hide" id="empty_password">{$LANG.account.error_password_empty}</div>
<div class="hide" id="validate_password_length_max">{$LANG.account.error_password_length_max}</div>