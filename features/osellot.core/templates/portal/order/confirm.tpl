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
					{foreach $order.items as $item}
					<li><strong>{$item.quantity}x</strong> {$item.name} (${number_format($item.price, 2)} ea)</li>
					{/foreach}
				</ul>

			</td>
			<td>{if $order.attributes.delivery}Yes ($3){else}No{/if}</td>
			<td>${$order.amount}</td>
		</tr>
	</table>
	<table class="details" summary="Details of your order">
		<tr>
			<th>Deliver my order to:</th>

			<!--
			<th>Pickup my order from:</th>
			-->
			<th>Payment method:</th>
			<th>Bill to:</th>
		</tr>
		<tr>
			<td>
				{if $order.attributes.pickup}
				{$order.attributes.pickup_location.line1}<br>
				{if !empty($order.attributes.pickup_location.line2)}{$order.attributes.pickup_location.line2}<br>{/if}
				{$order.attributes.pickup_location.city}, {$order.attributes.pickup_location.province} {$order.attributes.pickup_location.postal}
				{else}
				{$active_profile->getPrimaryAddress()->first_name} {$active_profile->getPrimaryAddress()->last_name}<br>
				{$order.attributes.delivery_address.line1}<br>
				{if !empty($order.attributes.delivery_address.line2)}{$order.attributes.delivery_address.line2}<br>{/if}
				{$order.attributes.delivery_address.city}, {$order.attributes.delivery_address.province} {$order.attributes.delivery_address.postal}
				{$order.attributes.pickup_location}
				{/if}
			</td>
			<!--
			<td>Fernwood Community Centre</td>
			-->
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