{foreach $products as $id => $product}
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
<script type="text/javascript">
window.print();
</script>