{if $active_worker->hasPriv('feedback.actions.create')}
<form action="{devblocks_url}{/devblocks_url}" style="margin-bottom:5px;">
	<button type="button" onclick="genericAjaxPopup('peek','c=billing&a=handleTabAction&tab=products.tab.billing.osellot&action=showProductPanel&id=0&view_id={$view->id}',null,false,'500');">{$translate->_('billing.products.add')|capitalize}</button>
</form>
{/if}

{include file="devblocks:cerberusweb.core::internal/views/search_and_view.tpl" view=$view}

{include file="devblocks:cerberusweb.core::internal/views/view_workflow_keyboard_shortcuts.tpl" view=$view}