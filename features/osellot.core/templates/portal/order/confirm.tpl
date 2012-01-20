<div id="confirm">
	<table class="summary" summary="Summary of your order">
		<tr>
			<th>Order date</th>
			<th>Ship date</th>
			<th>Products</th>
			<th>Cost</th>
		</tr>
		<tr>
			<td>{date('F d, Y')}</td>
			<td>September 12, 2011</td>
			<td>
				<ul>
					{$total = 0}



					{foreach $order.items as $item_id => $quantity}
					{$item = DAO_Product::get($item_id)}
					<li><strong>{$quantity}x</strong> {$item->name} (${number_format($item->price, 2)} ea)</li>
					{$total = $total + $item->price * $quantity}
					{/foreach}
				</ul>

			</td>
			<td>${$total}</td>
		</tr>
	</table>
	<table class="details" summary="Details of your order">
		<tr>
			<th>Payment method:</th>
			<th>Bill to:</th>
		</tr>
		<tr>
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
			<td>
				{$active_profile->getPrimaryAddress()->first_name} {$active_profile->getPrimaryAddress()->last_name}<br>
				{$order.attributes.billing_address.line1} {$order.attributes.billing_address.line2}<br>
				{$order.attributes.billing_address.city}, {$order.attributes.billing_address.state} {$order.attributes.billing_address.zip}
			</td>
		</tr>

	</table>
	<form id="buy" method="post" action="">
		<input type="hidden" name="a" value="doConfirm">
		<!-- lots of hidden inputs omg -->
		<div class="submit">
			<input type="submit" id="submit" name="submit" value="Confirm order">
		</div>
	</form>
	<div class="return"><a class="button" href="{devblocks_url}c=order&a=checkout{/devblocks_url}">&#8249; Back</a></div>

</div>