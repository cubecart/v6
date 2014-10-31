<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
?>
<h3>{$LANG.common.information}</h3>
<nav>
<ul class="small-block-grid-1 medium-block-grid-3 large-block-grid-3">
  {if isset($DOCUMENTS) && count($DOCUMENTS) > 0}
  	{foreach from=$DOCUMENTS item=document}
  <li><a href="{$document.doc_url}" title="{$document.doc_name}" {if $document.doc_url_openin}target="_blank"{/if}>{$document.doc_name}</a></li>
	{/foreach}
  {/if}
  {if isset($CONTACT_URL)}
  <li><a href="{$CONTACT_URL}" title="{$LANG.documents.document_contact}">{$LANG.documents.document_contact}</a></li>
  {/if}
</ul>

</nav>