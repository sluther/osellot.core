<div id="billingTabs">
	<ul>
		{foreach from=$tab_manifests item=tab_manifest}
			{if !isset($tab_manifest->params.acl) || $worker->hasPriv($tab_manifest->params.acl)}
				{$tabs[] = $tab_manifest->params.uri}
				<li><a href="{devblocks_url}ajax.php?c=billing&a=showTab&ext_id={$tab_manifest->id}&request={$response_uri|escape:'url'}{/devblocks_url}">{$tab_manifest->params.title|devblocks_translate}</a></li>
			{/if}
		{/foreach}
	</ul>
</div> 
<br>

{$tab_selected_idx=0}
{foreach from=$tabs item=tab_label name=tabs}
	{if $tab_label==$selected_tab}{$tab_selected_idx = $smarty.foreach.tabs.index}{/if}
{/foreach}

<script type="text/javascript">
	$(function() {
		var tabs = $("#billingTabs").tabs( { selected:{$tab_selected_idx} } );
	});
</script>