<table cellpadding="0" cellspacing="2" border="0" width="98%">
{foreach $product_attributes as $key => $attribute}
	<tr>
		<td width="0%" nowrap="nowrap" align="right" valign="top">{$attribute.label}: </td>
		<td width="100%">
            {if $attribute.type == 'bool'}
            <input type="radio" name="product_attributes[{$key}]" value="0"{if $attribute.value == '0'} checked="checked"{/if}>No
            <input type="radio" name="product_attributes[{$key}]" value="1"{if $attribute.value == '1'} checked="checked"{/if}>Yes
            {else}
            <input type="text" name="product_attributes[{$key}]" value="{$attribute.value}">
            {/if}
		</td>
	</tr>
{/foreach}
</table>