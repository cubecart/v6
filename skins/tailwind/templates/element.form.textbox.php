<div class="sm:col-span-3 mt-4">
  <label for="{$name}" class="block text-sm font-medium leading-6 text-gray-900">{$label}</label>
  <div class="mt-2">
    <input type="{if isset($type) && !empty($type)}{$type}{else}text{/if}" name="{$name}" id="{$name}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6{if isset($transform) && !empty($transform)} {$transform}{/if}" value="{$value}" placeholder='{$placeholder}{if isset($required) && $required}{$LANG.common.required}{/if}'{if isset($maxlength) && !empty($maxlength)} maxlength="{$maxlength}"{/if}{if isset($autocomplete) && !empty($autocomplete)} autocomplete="{$autocomplete}"{/if}>
  </div>
</div>