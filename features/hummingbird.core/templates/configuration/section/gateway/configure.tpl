<input type="hidden" name="c" value="config">
<input type="hidden" name="a" value="handleSectionAction">
<input type="hidden" name="section" value="gateway">
<input type="hidden" name="action" value="saveGatewaySettings">
<input type="hidden" name="id" value="{$gateway->id}">

<fieldset>
	<table cellpadding="2" cellspacing="0" border="0">
		<legend>{$gateway->name}</legend>
		<tr>
			<td width="0%" nowrap="nowrap"><b>Enabled:</b></td>
			<td width="100%"><input type="checkbox" name="enabled" value="1" {if $gateway->enabled}checked{/if}></td>
		</tr>
		{$plugin->configure()}
	</table>
	<br>
	
	<div class="status"></div>	

	<button type="button" class="submit"><span class="cerb-sprite2 sprite-tick-circle-frame"></span> {$translate->_('common.save_changes')|capitalize}</button>
</fieldset>

<script type="text/javascript">
$('#configGateway BUTTON.submit')
	.click(function(e) {
		genericAjaxPost('configGateway','',null,function(json) {
			$o = $.parseJSON(json);
			if(false == $o || false == $o.status) {
				Devblocks.showError('#configGateway div.status',$o.error);
			} else {
				document.location.href = '{devblocks_url}c=config&a=gateway{/devblocks_url}';
			}
		});
	})
;
</script>
