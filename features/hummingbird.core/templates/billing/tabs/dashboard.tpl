<h2>Collating</h2>
<form id="collatingFrm">
	<input type="hidden" name="c" value="billing">
	<input type="hidden" name="a" value="handleTabAction">
	<input type="hidden" name="tab" value="dashboard.tab.billing.hummingbird">
	<input type="hidden" name="action" value="saveItems">
	<div id="item_totals">
	{foreach $item_totals as $id => $item_total}
	<input type="text" name="item_totals[{$id}]" value="{$item_total}">
	{/foreach}
	</div>
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
			<td style="display: none;" id="{$item->id}_id"><input type="hidden" name="row[{$item->id}][id]" value="{$item->id}"></td>
			<td id="{$item->id}_item"><input name="row[{$item->id}][item]" type="text"></input></td>
			<td id="{$item->id}_source"><input name="row[{$item->id}][source]" type="text"></input></td>
			<td id="{$item->id}_origin"><input name="row[{$item->id}][origin]" type="text"></input></td>
			<td id="{$item->id}_unit"><input name="row[{$item->id}][unit]" type="text"></input></td>
			<td id="{$item->id}_case"><input name="row[{$item->id}][case]" type="text"></input></td>
			<td id="{$item->id}_unitscase"><input name="row[{$item->id}][unitscase]" type="text"></input></td>
			<td id="{$item->id}_unitcost"><input name="row[{$item->id}][unitcost]" type="text"></input></td>
			<td id="{$item->id}_casesneeded"><input name="row[{$item->id}][casesneeded]" type="text"></input></td>
			<td id="{$item->id}_roundedup"><input name="row[{$item->id}][roundedup]" type="text"></input></td>
			<td id="{$item->id}_remainder"><input name="row[{$item->id}][remainder]" type="text"></input></td>
			<td id="{$item->id}_guidance"><input name="row[{$item->id}][guidance]" type="text"></input></td>
			<td id="{$item->id}_totalcost"><input name="row[{$item->id}][totalcost]" type="text"></input></td>
			{foreach $products as $id => $product}
			<td id="{$item->id}_products_{$id}"><input name="row[{$item->id}][productscost][{$id}]" type="text"></input></td>
			{/foreach}
			{foreach $products as $id => $product}
			<td id="{$item->id}_products_{$product->id}">{$item->products.$id}</input></td>
			{/foreach}
		</tr>
		{/foreach}
		{$row = count($items) + 1}
		<tr id="{$row}">
			<td style="display: none;" id="{$row}_id"><input type="hidden" name="row[{$row}][id]" value="{$row}"></td>
			<td id="{$row}_item"><input name="row[{$row}][item]" type="text"></input></td>
			<td id="{$row}_source"><input name="row[{$row}][source]" type="text"></input></td>
			<td id="{$row}_origin"><input name="row[{$row}][origin]" type="text"></input></td>
			<td id="{$row}_unit"><input name="row[{$row}][unit]" type="text"></input></td>
			<td id="{$row}_case"><input name="row[{$row}][case]" type="text"></input></td>
			<td id="{$row}_unitscase"><input name="row[{$row}][unitscase]" type="text"></input></td>
			<td id="{$row}_unitcost"><input name="row[{$row}][unitcost]" type="text"></input></td>
			<td id="{$row}_casesneeded"><input name="row[{$row}][casesneeded]" type="text"></input></td>
			<td id="{$row}_roundedup"><input name="row[{$row}][roundedup]" type="text"></input></td>
			<td id="{$row}_remainder"><input name="row[{$row}][remainder]" type="text"></input></td>
			<td id="{$row}_guidance"><input name="row[{$row}][guidance]" type="text"></input></td>
			<td id="{$row}_totalcost"><input name="row[{$row}][totalcost]" type="text"></input></td>
			{foreach $products as $product}
			<td id="{$row}_products_{$product->id}"><input name="row[{$row}][products][{$product->id}]" type="text"></input></td>
			{/foreach}
			{foreach $products as $product}
			<td id="{$row}_productscost_{$product->id}"><input name="row[{$row}][productscost][{$product->id}]" type="text"></td>
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
		
		$(newRow.children('td')).each(function() {
			var name = $(this).children('input').attr('name').replace(/\d+/, rowId);
			$(this).children('input').attr('name', name).val(null);
			var id = $(this).attr('id').replace(/\d+/, rowId)
			$(this).attr('id', id);
		});
		newRow.children('td').first().children('input').val(rowId);
		
		tableRow.parent().append(newRow);
	})
	
	$("#collatingTable tr td input").live('change', function() {
		var data = {};
		var lastRow = $("#collatingTable tr").last();
		if($(this).parent().parent().get(0) === lastRow.get(0)) {
			// If the row being edited is the last row of the table, add a new blank row
			$("#addRow").click();
		}

		// var productPattern = /product\[\d+\]/;
		// Assemble JSON data from inputs
		
		// Grab the item_totals
		$('#collatingFrm div#item_totals input').each(function() {
			data[$(this).attr('name')] = $(this).val();
		});
		
		// Make row JSON
		$(this).parent().parent().children('td').each(function() {
			var input = $(this).children('input');
			var name = input.attr('name').replace(/row\[\d+\]/, 'row');
			data[name] = input.val();
		});
		

		// console.
		$.ajax({
			type: 'POST',
			data: data,
			url: DevblocksAppPath+'ajax.php?c=billing&a=handleTabAction&tab=dashboard.tab.billing.hummingbird&action=calculate', 
			success: function(json) {
				row = $.parseJSON(json);
				$.each(row, function(key, value) {
					// If value is an object, loop over it...
					if(key != "productscost") {
						// console.info('Targeting #collatingTable tr#'+row.id+' td#'+row.id+'_'+key+' input');
						$('#collatingTable tr#'+row.id+' td#'+row.id+'_'+key+' input').val(value);
					} else {
						$.each(value, function(k, v) {
							// console.info('Targeting #collatingTable tr#'+row.id+' td#'+row.id+'_productscost_'+k+' input');
							$('#collatingTable tr#'+row.id+' td#'+row.id+'_productscost_'+k+' input').val(v);
						});
					}
				});
			}
		});
		
		// console.log($(this).parent().parent().parent().parent().serializeArray());
		
	});
});
</script>