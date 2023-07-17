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
{if in_array($RECAPTCHA, array('2','4'))}
<div class="row">
   <div class="medium-8 columns">
        {if $RECAPTCHA=='2'}
        {include file='templates/element.recaptcha.php' ga_fid=$ga_fid}
        {else if $RECAPTCHA=='4'}
        {include file='templates/element.hcaptcha.php' ga_fid=$ga_fid}
        {/if}
    </div>
</div>
{/if}