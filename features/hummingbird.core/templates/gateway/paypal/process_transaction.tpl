	<form id="paypal" action="https://www.paypal.com/cgi-bin/webscr">
		<input type="hidden" name="cmd" value="_xclick"><br>
		<input type="hidden" name="bn" value="Hummingbird"><br>
		<input type="hidden" name="business" value="{$settings.email}"><br>
		<input type="hidden" name="item_name" value="The Good Food Box - Invoice #{$order.number}"><br>
		<input type="hidden" name="amount" value="{$order.amount}"><br>
		<input type="hidden" name="return" value="{$return_url}"><br>
		<input type="hidden" name="cancel_return" value="{$cancel_return_url}"><br>
		<input type="hidden" name="notify_url" value="{$notify_url}"><br>
		<input type="hidden" name="currency_code" value="CAD"><br>
		<input type="hidden" name="invoice" value="{$order.number}"><br>
		<input type="hidden" name="first_name" value="{$active_profile->getPrimaryAddress()->first_name}"><br>
		<input type="hidden" name="last_name" value="{$active_profile->getPrimaryAddress()->last_name}"><br>
		<input type="hidden" name="payer_business_name" value=""><br>
		<input type="hidden" name="address_street" value="{$order.attributes.billing_address.line1}{if !empty($order.attributes.billing_address.line2)} {$order.attributes.billing_address.line2}{/if}"><br>
		<input type="hidden" name="address_city" value="{$order.attributes.billing_address.city}"><br>
		<input type="hidden" name="address_state" value="CA"><br>
		<input type="hidden" name="address_zip" value="{$order.attributes.billing_address.zip}"><br>
		<input type="hidden" name="address_country" value="USA"><br>
		<input type="hidden" name="payer_email" value="{$active_profile->getPrimaryAddress()->email}"><br>
		<input type="hidden" name="payer_id" value="{$active_profile->id}"><br>
	</form>
<script type="text/javascript">
$(document).ready(function() {
	$('#paypal').submit();
});
</script>