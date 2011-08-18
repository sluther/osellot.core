<table cellspacing="0" cellpadding="0" border="0" width="100%" style="padding-bottom:5px;">
<tr>
	<td width="1%" nowrap="nowrap" valign="top" style="padding-right:5px;">
		<form action="{devblocks_url}{/devblocks_url}" method="post" style="padding-bottom:5px;">
			<input type="hidden" name="c" value="crm">
			<input type="hidden" name="a" value="">
				<button type="button" onclick="genericAjaxPopup('peek','c=billing&a=handleTabAction&tab=agencies.tab.billing.hummingbird&action=showAgencyPanel&id=0&view_id={$view->id}',null,false,'500');"><span class="cerb-sprite2 sprite-plus-circle-frame"></span> {$translate->_('billing.agencies.add')}</button>
		</form>
	</td>
	<td width="98%" valign="middle"></td>
	<td width="1%" nowrap="nowrap" valign="middle" align="right">
		{include file="devblocks:cerberusweb.crm::crm/quick_search.tpl"}
	</td>
</tr>
</table>

{include file="devblocks:cerberusweb.core::internal/views/search_and_view.tpl" view=$view}

{include file="devblocks:cerberusweb.core::internal/views/view_workflow_keyboard_shortcuts.tpl" view=$view}
