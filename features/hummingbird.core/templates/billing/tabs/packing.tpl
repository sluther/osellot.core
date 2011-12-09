<a href="{devblocks_url}ajax.php?c=billing&a=handleTabAction&tab=packing.tab.billing.hummingbird&action=printPackingSheet{/devblocks_url}">Print All Sheets</a>
{foreach $products as $id => $product}
<a href="{devblocks_url}ajax.php?c=billing&a=handleTabAction&tab=packing.tab.billing.hummingbird&action=printPackingSheet&product_id={$id}{/devblocks_url}">Print</a>
<h1>{$product->name}</h1>
<table class="packing">
	<tr>
		<th>Packing order</th>
		<th>Item</th>
		<th>Source</th>
		<th>Quantity</th>
		<th>Unit</th>
		<th>Weighed</th>
	</tr>
	{foreach $items as $item}
	{$source_id = $item->source}
	{$source = $sources.$source_id}
	{if $item->products.$id > 0}
	<tr>
		<td>1</td>
		<td>{$item->item}</td>
		<td>{$source->name}</td>
		<td>{$item->products.$id}</td>
		<td>{$item->unit}</td>
		<td>{if $item->weighed}Yes{else}No{/if}</td>
	</tr>
	{/if}
	{/foreach}
	
</table>
{/foreach}