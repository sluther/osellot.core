<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formProductPeek" name="formProductPeek">
<input type="hidden" name="c" value="billing">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="products.tab.billing.osellot">
<input type="hidden" name="action" value="saveProductPanel">
<input type="hidden" name="product_id" value="{$product->id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">

<fieldset>
	<legend>{'product.details'|devblocks_translate}</legend>
	
	<table cellpadding="0" cellspacing="2" border="0" width="98%">
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('product.name')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="name" id="productname" value="{$product->name}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('product.sku')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="sku" id="productsku" value="{$product->sku}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('product.price')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="price" id="productprice" value="{$product->price}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('product.price_setup')|capitalize}: </td>
			<td width="100%">
				<input type="text" name="price_setup" id="productpricesetup" value="{$product->price_setup}" class="required" style="border:1px solid rgb(180,180,180);padding:2px;width:98%;">
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('product.recurring')|capitalize}: </td>
			<td width="100%">
				<select name="recurring"  id="productrecurring">
					<option value="0">No</option>
					<option value="1"{if $product->recurring} selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td width="0%" nowrap="nowrap" align="right" valign="top">{$translate->_('product.description')|capitalize}: </td>
			<td width="100%">
				<textarea name="description" id="productdescription">{$product->description}</textarea>
			</td>
		</tr>
	</table>
</fieldset>
{if !empty($custom_fields)}
<fieldset>
	<legend>{'common.custom_fields'|devblocks_translate}</legend>
	{include file="devblocks:cerberusweb.core::internal/custom_fields/bulk/form.tpl" bulk=false}
</fieldset>
{/if}

{* Comment *}
{if !empty($last_comment)}
	{include file="devblocks:cerberusweb.core::internal/comments/comment.tpl" readonly=true comment=$last_comment}
{/if}


<button type="button" onclick="if($('#formProductPeek').validate().form()) { genericAjaxPopupPostCloseReloadView('peek','formProductPeek','{$view_id}',false,'product_save'); } "><span class="cerb-sprite2 sprite-tick-circle-frame"></span> {$translate->_('common.save_changes')}</button>
{if !empty($product)}<button type="button" onclick="if(confirm('Are you sure you want to permanently delete this product?')) { this.form.do_delete.value='1';genericAjaxPopupClose('peek');genericAjaxPost('formProductPeek', 'view{$view_id}'); } "><span class="cerb-sprite2 sprite-cross-circle-frame"></span> {$translate->_('common.delete')|capitalize}</button>{/if}
<br>

{if !empty($product)}
<div style="float:right;">
	<a href="{devblocks_url}c=billing&a=products&id={$product->id}{/devblocks_url}">view full record</a>
</div>
<br clear="all">
{/if}
</form>

<script type="text/javascript">
	$popup = genericAjaxPopupFetch('peek');
	$popup.one('popup_open',function(event,ui) {
		$(this).dialog('option','title', '{'Product'|devblocks_translate}');
		ajax.emailAutoComplete('#emailinput');
		$("#formProductPeek").validate()
		$('#formProductPeek :input:text:first').focus();
	});
	$('#formProductPeek button.chooser_worker').each(function() {
		ajax.chooser(this,'cerberusweb.contexts.worker','worker_id', { autocomplete:true });
	});
	$('#formProductPeek button.chooser_notify_worker').each(function() {
		ajax.chooser(this,'cerberusweb.contexts.worker','notify_worker_ids', { autocomplete:true });
	});
</script>