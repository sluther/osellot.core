<div id="confirm">
	<table class="summary" summary="Summary of your order">
		<tr>
			<th>Order date</th>
			<th>Ship date</th>
			<th>Boxes</th>
			<th>Delivery</th>
			<th>Cost</th>
		</tr>
		<tr>
			<td>{date('F d, Y')}</td>
			<td>September 12, 2011</td>
			<td>
				<ul>
					{foreach $invoice->items as $item}
					<li><strong>{$item->quantity}x</strong> {DAO_Product::get($item->product_id)->name} (${number_format($item->amount, 2)} ea)</li>
					{/foreach}
				</ul>
			</td>
			<td>{if $invoice->attributes.delivery}Yes ($3){else}No{/if}</td>
			<td>${number_format($invoice->amount, 2)}</td>
		</tr>
	</table>
	<table class="details" summary="Details of your order">
		<tr>
			{if $invoice->attributes.pickup}
			<th>Pickup my order from:</th>
			{else}
			<th>Deliver my order to:</th>
			{/if}
			<th>Payment method:</th>
			{if $plugin->getParam('cc')}
			<th>Bill to:</th>
			{/if}
		</tr>
		<tr>
			<td>
				{if $invoice->attributes.pickup}
				{$invoice->attributes.pickup_location.line1}<br>
				{if !empty($invoice->attributes.pickup_location.line2)}{$invoice->attributes.pickup_location.line2}<br>{/if}
				{$invoice->attributes.pickup_location.city}, {$invoice->attributes.pickup_location.province} {$invoice->attributes.pickup_location.postal}
				{else}
				{$active_profile->getPrimaryAddress()->first_name} {$active_profile->getPrimaryAddress()->last_name}<br>
				{$invoice->attributes.delivery_address.line1}<br>
				{if !empty($invoice->attributes.delivery_address.line2)}{$invoice->attributes.delivery_address.line2}<br>{/if}
				{$invoice->attributes.delivery_address.city}, {$invoice->attributes.delivery_address.province} {$invoice->attributes.delivery_address.postal}
				{$invoice->attributes.pickup_location}
				{/if}
			</td>
			<td>
				<dl>
					{if $plugin->getParam('cc')}
					<dt>Credit Card</dt>
					<dd>Visa</dd>

					<dt>Number</dt>
					<dd>1234 5678 9012 3456</dd>
					<dt>Expiry</dt>
					<dd>2016 / 09</dd>
					<dt>CVV</dt>
					<dd>123</dd>
					{else}
					{$plugin->manifest->name}
					{/if}
				</dl>
			</td>
			{if $plugin->getParam('cc')}
			<td>
				{$active_profile->getPrimaryAddress()->first_name} {$active_profile->getPrimaryAddress()->last_name}<br>
				{$invoice->attributes.billing_address.line1}<br>
				{if !empty($invoice->attributes.billing_address.line2)}{$invoice->attributes.billing_address.line2}<br>{/if}
				{$invoice->attributes.billing_address.city}, {$invoice->attributes.billing_address.state} {$invoice->attributes.billing_address.postal}
			</td>
			{/if}
		</tr>

	</table>
	<div class="submit">
		<a class="button" href="javascript:;" onclick="window.print();">Print Order</a>
	</div>
	<div class="return"><a class="button" href="{devblocks_url}c=account{/devblocks_url}">&#8249; Return to account</a></div>

</div>