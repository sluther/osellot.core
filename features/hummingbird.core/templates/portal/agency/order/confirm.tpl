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
		</tr>
		<tr>
			<td>
				{if $order.attributes.pickup}
				{$active_profile->getPrimaryAddress()->first_name} {$active_profile->getPrimaryAddress()->last_name}<br>
				{$order.attributes.pickup_location.line1}<br>
				{if !empty($order.attributes.pickup_location.line2)}{$order.attributes.pickup_location.line2}<br>{/if}
				{$order.attributes.pickup_location.city}, {$order.attributes.pickup_location.province} {$order.attributes.pickup_location.postal}
				{else}
				{$order.attributes.delivery_address.name}<br>
				{$order.attributes.delivery_address.line1}<br>
				{if !empty($order.attributes.delivery_address.line2)}{$order.attributes.delivery_address.line2}<br>{/if}
				{$order.attributes.delivery_address.city}, {$order.attributes.delivery_address.province} {$order.attributes.delivery_address.postal}
				{$order.attributes.pickup_location}
				{/if}
			</td>
			<!--
			<td>Fernwood Community Centre</td>
			-->
		</tr>
	</table>
	<form id="buy" method="post" action="">
		<input type="hidden" name="a" value="doConfirm">
		<!-- lots of hidden inputs omg -->
		<div class="submit">
			<input type="submit" id="submit" name="submit" value="Confirm order">
		</div>
	</form>
	<div class="return"><a class="button" href="{devblocks_url}c=agency&a=order&action=checkout{/devblocks_url}">&#8249; Back</a></div>

</div>