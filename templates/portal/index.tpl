{include file="devblocks:osellot.core::portal/header.tpl"}

{if !empty($module)}
{$module->writeResponse($module_response)}
{/if}

{include file="devblocks:osellot.core::portal/footer.tpl"}