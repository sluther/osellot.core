{assign var=type value=$visit->get('import.last.type')}
<form action="{devblocks_url}{/devblocks_url}" method="POST" id="frmImport">
<input type="hidden" name="c" value="invoices">
<input type="hidden" name="a" value="doImport">
<input type="hidden" name="context" value="{$context}">
<input type="hidden" name="view_id" value="{$view_id}">

<fieldset>
	<table cellpadding="2" cellspacing="0" border="0">
        <tr>
            <th>Plan</th>
            <th>Remember Plan?</th>
            {foreach $columns as $column}
            <th>{$column}</th>
            {/foreach}
        </tr>
        {foreach $rows as $line_number => $row}
        {$station_id = $row.0}
        {$station_product = $station_products.$station_id}
        <tr>
            <td>
                <select name="transmitters[{$line_number}][transmitter_product]">
                    {foreach $products as $product_id => $product}
                    <option value="{$product->id}"{if isset($station_product.$product_id)} selected="selected"{/if}>{$product->name}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select name="transmitters[{$line_number}][save_plan]">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </td>
            {foreach $row as $key => $col}
            {if $key == 0}
                <input type="hidden" name="transmitters[{$line_number}][station_number]" value="{$col}">
            {else}
            <td>{$col}</td>
            {/if}
            {/foreach}
        </tr>
        {/foreach}
	</table>
</fieldset>

<div class="buttons">
	<button type="button" class="submit"><span class="cerb-sprite2 sprite-tick-circle"></span> {$translate->_('common.continue')|capitalize}</button>
	<button type="button" class="preview"><span class="cerb-sprite2 sprite-gear"></span> {$translate->_('common.preview')|capitalize}</button>
	<button type="button" class="cancel"><span class="cerb-sprite2 sprite-cross-circle"></span> {$translate->_('common.cancel')|capitalize}</button>
</div>

<div id="divImportPreview" style="margin:10px 0 0 0;border:1px solid rgb(230,230,230);padding:5px;height:200px;overflow-y:auto;display:none;"></div>
</form>

<script type="text/javascript">
	$popup = genericAjaxPopupFind('#frmImport');
	$frm = $popup.find('FORM#frmImport');
	
 	$frm.find('button.submit').click(function(event) {
 		$frm = $(this).closest('form');
 		if(!$frm.validate().form())
 			return;

 		$('#divImportPreview').html('Importing... please wait');

 		$div = $(this).closest('div');
 		$div.fadeOut();
 		
 		genericAjaxPost('frmImport', '', null, function(o) {
 			genericAjaxGet('view{$view_id}','c=internal&a=viewRefresh&id={$view_id}');
 			genericAjaxPopupDestroy('{$layer}');
 		});
 	});
 	
 	$frm.find('button.preview').click(function() {
 		$frm = $(this).closest('form');
 		if(!$frm.validate().form())
			return;
 		
 		$('#divImportPreview').html('Loading...');
 		genericAjaxPost('frmImport', '', 'c=invoices&a=doImport&context={$context}&is_preview=1', function(o) {
 			$('#divImportPreview').html(o).fadeIn();
 		});
 	});
 	
 	$frm.find('button.cancel').click(function() {
		genericAjaxPopupDestroy('{$layer}');
 	});
 	
 	$frm.validate();
 	
 	$frm.find('textarea').elastic();
 	
	$popup.one('popup_open',function(event,ui) {
		event.stopPropagation();
		$(this).dialog('option','title',"{'common.import'|devblocks_translate|capitalize}");
	});
	
	$popup.one('diagogclose', function(event) {
		event.stopPropagation();
		genericAjaxPopupDestroy('{$layer}');
	});
</script>