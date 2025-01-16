{if isset($GUI_MESSAGE.error)}
<div class="rounded-md bg-red-50 p-4 mb-3">
  <div class="flex">
    <div class="flex-shrink-0">
      <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
      </svg>
    </div>
    <div class="ml-3">
      <h3 class="text-sm font-medium text-red-800">{$LANG.gui_message.errors_detected}</h3>
      <div class="mt-2 text-sm text-red-700">
        <ul role="list" class="list-disc space-y-1 pl-5">
            {foreach from=$GUI_MESSAGE.error item=error}
            <li>{$error}</li>
            {/foreach}
        </ul>
      </div>
    </div>
  </div>
</div>
{/if}
{if isset($GUI_MESSAGE.notice)}
<div class="rounded-md bg-green-50 p-4 mb-3">
  <div class="flex">
    <div class="flex-shrink-0">
      <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
      </svg>
    </div>
      <div class="ml-3 text-sm text-green-700">
      <ul>
        {foreach from=$GUI_MESSAGE.notice item=notice}
        <li>{$notice}</li>
        {/foreach}
        </ul>
      </div>
  </div>
</div>
{/if}
{if isset($GUI_MESSAGE.info)}
<div class="rounded-md bg-blue-50 p-4 mb-3">
  <div class="flex">
    <div class="flex-shrink-0">
      <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
      </svg>
    </div>
    <div class="ml-3 text-sm text-blue-700">
        <ul>
        {foreach from=$GUI_MESSAGE.info item=info}
        <li>{$info}</li>
      {/foreach}
    </ul>
    </div>
  </div>
</div>
{/if}