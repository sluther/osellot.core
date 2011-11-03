<h2>Collating</h2>

<table id="collatingTable">
	<tr>
		<th>Item</th>
		<th>Source</th>
		<th>Origin</th>
		<th>Unit</th>
		<th>Case</th>
		<th>Units/Case</th>
		<th>Unit Cost</th>
		<th>Cases Needed</th>
		<th>Rounded Up</th>
		<th>Remainder in units</th>
		<th>Guidance</th>
		<th>Total Cost</th>
		{foreach $products as $product}
		<th>{$product->name}</th>
		{/foreach}
		{foreach $products as $product}
		<th>{$product->name}</th>
		{/foreach}
	</tr>
	{foreach $items as $item}
	<tr id="{$item->id}">
		<td><input name="row[{$item->id}][item]" type="text"></input></td>
		<td><input name="row[{$item->id}][source]" type="text"></input></td>
		<td><input name="row[{$item->id}][origin]" type="text"></input></td>
		<td><input name="row[{$item->id}][unit]" type="text"></input></td>
		<td><input name="row[{$item->id}][case]" type="text"></input></td>
		<td><input name="row[{$item->id}][unitscase]" type="text"></input></td>
		<td><input name="row[{$item->id}][unitcost]" type="text"></input></td>
		<td><input name="row[{$item->id}][casesneeded]" type="text"></input></td>
		<td><input name="row[{$item->id}][roundedup]" type="text"></input></td>
		<td><input name="row[{$item->id}][remainderinunits]" type="text"></input></td>
		<td><input name="row[{$item->id}][guidance]" type="text"></input></td>
		<td><input name="row[{$item->id}][totalcost]" type="text"></input></td>
		{foreach $products as $product}
		<td><input name="row[{$item->id}][product[{$product->id}]]" type="text"></input></td>
		{/foreach}
		{foreach $products as $product}
		<td><input name="row[{$item->id}][product[{$product->id}]]" type="text"></input></td>
		{/foreach}
	</tr>
	{/foreach}
	{$row = count($items) + 1}
	<tr id="{$row}">
		<td><input name="row[{$row}][item]" type="text"></input></td>
		<td><input name="row[{$row}][source]" type="text"></input></td>
		<td><input name="row[{$row}][origin]" type="text"></input></td>
		<td><input name="row[{$row}][unit]" type="text"></input></td>
		<td><input name="row[{$row}][case]" type="text"></input></td>
		<td><input name="row[{$row}][unitscase]" type="text"></input></td>
		<td><input name="row[{$row}][unitcost]" type="text"></input></td>
		<td><input name="row[{$row}][casesneeded]" type="text"></input></td>
		<td><input name="row[{$row}][roundedup]" type="text"></input></td>
		<td><input name="row[{$row}][remainderinunits]" type="text"></input></td>
		<td><input name="row[{$row}][guidance]" type="text"></input></td>
		<td><input name="row[{$row}][totalcost]" type="text"></input></td>
		{foreach $products as $product}
		<td><input name="row[{$row}][product[{$product->id}]]" type="text"></input></td>
		{/foreach}
		{foreach $products as $product}
		<td><input name="row[{$row}][product[{$product->id}]]" type="text"></input></td>
		{/foreach}
	</tr>
</table>
<button id="addrow" style="float:right">Add</button>
<script type="text/javascript">
$(document).ready(function() {
	$("#addrow").click(function() {
 		tableRow = $("#collatingTable tr").last();
		var rowId = parseInt(tableRow.attr('id')) + 1;
		newRow = tableRow.clone();
		newRow.attr('id', rowId);
		
		pattern = /\d+/;
		
		$(newRow.children('td')).each(function() {
			var name = $(this).children('input').attr('name');
			name = name.replace(pattern, rowId);
			$(this).children('input').attr('name', name);
		});

		tableRow.parent().append(newRow);
	})
});
</script>