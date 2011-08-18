{include file="devblocks:hummingbird.core::portal/header.tpl"}

{if !empty($module)}
{$module->writeResponse($module_response)}
{/if}

{include file="devblocks:hummingbird.core::portal/footer.tpl"}