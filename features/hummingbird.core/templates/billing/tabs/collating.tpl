<h2>Collating</h2>
<form id="collatingFrm" method="POST" action="{devblocks_url}{/devblocks_url}">
	<input type="hidden" name="c" value="billing">
	<input type="hidden" name="a" value="handleTabAction">
	<input type="hidden" name="tab" value="collating.tab.billing.hummingbird">
	<input type="hidden" name="action" value="saveBoxItems">
	<input type="hidden" name="startdate" value="{$startdate}">
	<input type="hidden" name="enddate" value="{$enddate}">
	<div id="item_totals">
	{foreach $item_totals as $id => $item_total}
	<input type="hidden" name="item_totals[{$id}]" value="{$item_total}">
	{/foreach}
	</div>
	<table id="collatingTable">
		<tr>
			<th>Item</th>
			<th>Source</th>
			<th>Origin</th>
			<th>Unit</th>
			<th>Weighed</th>
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
		</tr>
		{foreach $items as $item}
		<tr id="{$item->id}">
			<td style="display: none;" id="{$item->id}_id"><input type="hidden" name="row[{$item->id}][id]" value="{$item->id}"></td>
			<td id="{$item->id}_item"><input name="row[{$item->id}][item]" type="text" value="{$item->item}"></input></td>
			<td id="{$item->id}_source"><select name="row[{$item->id}][source]">{foreach $sources as $source}<option value="{$source->id}">{$source->source}</option>{/foreach}</select></td>
			<td id="{$item->id}_origin"><select name="row[{$item->id}][origin]"><option value="local">Local</option><option valu="not local">Not Local</option></select></td>
			<td id="{$item->id}_unit"><input name="row[{$item->id}][unit]" type="text" value="{$item->unit}"></input></td>
			<td id="{$item->id}_weighed"><select name="row[{$item->id}][weighed]"><option value="0">No</option><option value="1">Yes</option></select></td>
			<td id="{$item->id}_casecost"><input name="row[{$item->id}][casecost]" type="text" value="{$item->casecost}"></input></td>
			<td id="{$item->id}_unitspercase"><input name="row[{$item->id}][unitspercase]" type="text" value="{$item->unitspercase}"></input></td>
			<td id="{$item->id}_unitcost"><input name="row[{$item->id}][unitcost]" type="text" value="{$item->unitcost}"></input></td>
			<td id="{$item->id}_casesneeded"><input name="row[{$item->id}][casesneeded]" type="text" value="{$item->casesneeded}"></input></td>
			<td id="{$item->id}_casesrounded"><input name="row[{$item->id}][casesrounded]" type="text" value="{$item->casesrounded}"></input></td>
			<td id="{$item->id}_remainder"><input name="row[{$item->id}][remainder]" type="text" value="{$item->remainder}"></input></td>
			<td id="{$item->id}_guidance"><input name="row[{$item->id}][guidance]" type="text" value="{$item->guidance}"></input></td>
			<td id="{$item->id}_totalcost"><input name="row[{$item->id}][totalcost]" type="text" value="{$item->totalcost}"></input></td>
			{foreach $products as $id => $product}
			<td id="{$item->id}_products_{$id}"><input name="row[{$item->id}][products][{$id}]" type="text" value="{$item->products.$id}"></input></td>
			{/foreach}
			<!--
			{foreach $products as $id => $product}
			<td id="{$item->id}_products_{$product->id}">{$item->products.$id}</input></td>
			{/foreach}-->
		</tr>
		{/foreach}
		{$row = count($items) + 1}
		<tr id="{$row}">
			<td style="display: none;" id="{$row}_id"><input type="hidden" name="row[{$row}][id]" value="{$row}"></td>
			<td id="{$row}_item"><input name="row[{$row}][item]" type="text"></input></td>
			<td id="{$row}_source"><select name="row[{$row}][source]">{foreach $sources as $source}<option value="{$source->id}">{$source->source}</option>{/foreach}</td>
			<td id="{$row}_origin"><select name="row[{$row}][origin]"><option value="local">Local</option><option valu="not local">Not Local</option></select></td>
			<td id="{$row}_unit"><input name="row[{$row}][unit]" type="text"></input></td>
			<td id="{$row}_weighed"><select name="row[{$row}][weighed]"><option value="0">No</option><option value="1">Yes</option></select></td>
			<td id="{$row}_casecost"><input name="row[{$row}][casecost]" type="text"></input></td>
			<td id="{$row}_unitspercase"><input name="row[{$row}][unitspercase]" type="text"></input></td>
			<td id="{$row}_unitcost"><input name="row[{$row}][unitcost]" type="text"></input></td>
			<td id="{$row}_casesneeded"><input name="row[{$row}][casesneeded]" type="text"></input></td>
			<td id="{$row}_casesrounded"><input name="row[{$row}][casesrounded]" type="text"></input></td>
			<td id="{$row}_remainder"><input name="row[{$row}][remainder]" type="text"></input></td>
			<td id="{$row}_guidance"><input name="row[{$row}][guidance]" type="text"></input></td>
			<td id="{$row}_totalcost"><input name="row[{$row}][totalcost]" type="text"></input></td>
			{foreach $products as $id => $product}
			<td id="{$row}_products_{$id}"><input name="row[{$row}][products][{$id}]" type="text"></input></td>
			{/foreach}
		</tr>
	</table>
	<button id="saveRows" style="float:right">Save</button>
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
			$(this).children().attr('name').replace(/\d+/, rowId);
			$(this).children().attr('name', name).val(null);
			var id = $(this).attr('id').replace(/\d+/, rowId)
			$(this).attr('id', id);
		});
		newRow.children('td').first().children('input').val(rowId);
		
		tableRow.parent().append(newRow);
	})
	$("#saveRows").click(function(event) {
		event.preventDefault();
		$(this).parent().submit();		
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
			var input = $(this).children();
			var name = input.attr('name').replace(/row\[\d+\]/, 'row');
			data[name] = input.val();
		});
		

		// console.
		$.ajax({
			type: 'POST',
			data: data,
			url: DevblocksAppPath+'ajax.php?c=billing&a=handleTabAction&tab=collating.tab.billing.hummingbird&action=calculate', 
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