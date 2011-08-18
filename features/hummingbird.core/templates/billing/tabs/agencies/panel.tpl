<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formAgencyPeek" name="formAgencyPeek">
<input type="hidden" name="c" value="billing">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="agencies.tab.billing.hummingbird">
<input type="hidden" name="action" value="saveAgencyPanel">
<input type="hidden" name="agency_id" value="{$agency->id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">

<fieldset>
	<legend>{'agency.authentication'|devblocks_translate}</legend>
	
	<table cellpadding="0" cellspacing="2" border="0" width="98%">
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('dao.contact_person.email_id')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="email" id="emailinput" value="{$address->email}" class="required email" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('dao.contact_person.auth_password')|capitalize}: </td>
			<td width="100%">
				<input type="password" name="password" style="width:92%;" value="" autocomplete="off">
			</td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend>{'agency.contactinfo'|devblocks_translate}</legend>
	<table cellpadding="0" cellspacing="2" border="0" width="98%">
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('dao.contact_person.name')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="name" id="name" value="{$agency->name}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('dao.contact_person.phone')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="phone" id="phone" value="{$agency->phone}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('dao.contact_person.address_line1')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="address_line1" id="address_line1" value="{$agency->address_line1}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('dao.contact_person.address_line2')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="address_line2" id="address_line2" value="{$agency->address_line2}" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('dao.contact_person.address_city')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="address_city" id="address_city" value="{$agency->address_city}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('dao.contact_person.address_province')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="address_province" id="address_province" value="{$agency->address_province}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('dao.contact_person.address_postal')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="address_postal" id="address_postal" value="{$agency->address_postal}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
	</table>
</fieldset>
{if !empty($custom_fields)}
<fieldset>
	<legend>{'common.custom_fields'|devblocks_translate}</legend>
	{include file="devblocks:cerberusweb.core::internal/custom_fields/bulk/form.tpl" bulk=false}
</fieldset>
{/if}

{* Comment *}
{if !empty($last_comment)}
	{include file="devblocks:cerberusweb.core::internal/comments/comment.tpl" readonly=true comment=$last_comment}
{/if}


<button type="button" onclick="if($('#formAgencyPeek').validate().form()) { genericAjaxPopupPostCloseReloadView('peek','formAgencyPeek','{$view_id}',false,'agency_save'); } "><span class="cerb-sprite2 sprite-tick-circle-frame"></span> {$translate->_('common.save_changes')}</button>
{if !empty($agency)}<button type="button" onclick="if(confirm('Are you sure you want to permanently delete this opportunity?')) { this.form.do_delete.value='1';genericAjaxPopupClose('peek');genericAjaxPost('formAgencyPeek', 'view{$view_id}'); } "><span class="cerb-sprite2 sprite-cross-circle-frame"></span> {$translate->_('common.delete')|capitalize}</button>{/if}
<input name="submit" type="submit" value="Test">
<br>

{if !empty($agency)}
<div style="float:right;">
	<a href="{devblocks_url}c=billing&a=agencies&id={$agency->id}{/devblocks_url}">view full record</a>
</div>
<br clear="all">
{/if}
</form>

<script type="text/javascript">
	$popup = genericAjaxPopupFetch('peek');
	$popup.one('popup_open',function(event,ui) {
		$(this).dialog('option','title', '{'Agency'|devblocks_translate}');
		ajax.emailAutoComplete('#emailinput');
		$("#formAgencyPeek").validate()
		$('#formAgencyPeek :input:text:first').focus();
	});
	$('#formAgencyPeek button.chooser_worker').each(function() {
		ajax.chooser(this,'cerberusweb.contexts.worker','worker_id', { autocomplete:true });
	});
	$('#formAgencyPeek button.chooser_notify_worker').each(function() {
		ajax.chooser(this,'cerberusweb.contexts.worker','notify_worker_ids', { autocomplete:true });
	});
</script>