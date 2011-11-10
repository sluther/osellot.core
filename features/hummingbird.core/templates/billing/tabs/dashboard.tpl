<h2>Collating</h2>
<form id="collatingFrm">
	<input type="hidden" name="c" value="billing">
	<input type="hidden" name="a" value="handleTabAction">
	<input type="hidden" name="tab" value="dashboard.tab.billing.hummingbird">
	<input type="hidden" name="action" value="saveItems">
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
			<td><input name="row[{$item->id}][products][{$product->id}]" type="text"></input></td>
			{/foreach}
			{foreach $products as $product}
			<td><input name="row[{$item->id}][products][{$product->id}]" type="text"></input></td>
			{/foreach}
		</tr>
		{/foreach}
		{$row = count($items) + 1}
		<tr id="{$row}">
			<td style="display: none;"><input type="hidden" name="row[{$row}][id]" value="{$row}"></td>
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
			<td><input name="row[{$row}][products][{$product->id}]" type="text"></input></td>
			{/foreach}
			{foreach $products as $product}
			<td><input name="row[{$row}][products][{$product->id}]" type="text"></input></td>
			{/foreach}
		</tr>
	</table>
	<button id="addRow" style="float:right">Add</button>
</form>
<script type="text/javascript">
$(document).ready(function() {
	$("#addRow").click(function(event) {
		event.preventDefault();
		var tableRow = $("#collatingTable tr").last();
		var rowId = parseInt(tableRow.attr('id')) + 1;
		newRow = tableRow.clone();
		newRow.attr('id', rowId);
		
		var pattern = /\d+/;
		
		$(newRow.children('td')).each(function() {
			var name = $(this).children('input').attr('name').replace(pattern, rowId);
			$(this).children('input').attr('name', name).val(null);
		});

		tableRow.parent().append(newRow);
	})
	
	$("#collatingTable tr td input").live('change', function() {
		var data = new Object();
		var lastRow = $("#collatingTable tr").last();
		if($(this).parent().parent().get(0) === lastRow.get(0)) {
			// If the row being edited is the last row of the table, add a new blank row
			$("#addRow").click();
		}
		var rowPattern = /row\[\d+\]/;
		// var productPattern = /product\[\d+\]/;
		// Assemble json data from inputs
		$($(this).parent().parent().children('td').each(function() {
			var input = $(this).children('input');
			var name = input.attr('name').replace(rowPattern, 'row');
						
			data[name] = input.val();
		}));
		$.ajax({
			type: 'POST',
			data: data,
			url: DevblocksAppPath+'ajax.php?c=billing&a=handleTabAction&tab=dashboard.tab.billing.hummingbird&action=calculate', 
			cb: function(json) {
				// $o = $.parseJSON(json);
				console.log(json);	
			}
		});
		
		// console.log($(this).parent().parent().parent().parent().serializeArray());
		
	});
});
</script>