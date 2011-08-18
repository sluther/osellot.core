<h2>{'gateway.setup.hummingbird'|devblocks_translate|capitalize}</h2>


<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td width="1%" nowrap="nowrap" valign="top" style="padding-right:5px;">
			<fieldset>
				<legend>Gateways</legend>
				
				<ul style="margin:0;padding:0;list-style:none;">
				{if !empty($gateways)}
					{foreach $gateways as $gateway}
						<li style="line-height:150%;">
							<a href="javascript:;" onclick="genericAjaxGet('configGateway','c=config&a=handleSectionAction&section=gateway&action=editGateway&id={$gateway->id}');" style="{if !$gateway->enabled}font-style:italic;color:rgb(150,0,0);{/if}">{$gateway->name}</a>
						</li>
					{/foreach}
				{/if}
				</ul>
			</fieldset>
		</td>
		
		<td width="99%" valign="top">
			<form action="{devblocks_url}{/devblocks_url}" method="post" id="configGateway" onsubmit="return false;">

				<fieldset>
					<table cellpadding="2" cellspacing="0" border="0">
						<legend>Select a gateway</legend>
						Select a gateway on the left
					</table>
				</fieldset>
			</form>
		</td>
	</tr>
</table>

