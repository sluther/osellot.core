			<div class="leftcolumn">
			<div class="lcontentbox">
				<form action="" method="post" id="buy">
					<input type="hidden" name="a" value="doCheckout">
					<fieldset>
						<legend>Payment details</legend>
						<span>Select a payment method.</span>
						<div class="options">
							{foreach $checkout_plugins as $plugin}
							<div>
								<input type="radio" value="{$plugin->id}" name="plugin" id="checkout_option_{$plugin->id}"{if $plugin === $default_plugin} checked="checked"{/if}>
								<label for="{$plugin->id}"><img alt="{$plugin->manifest->name}" src="./layout/images/paypal.png"></label>
							</div>
							{/foreach}
						</div>
						<div id="checkoutForm">
							<div id="checkoutPlugin">
							{$default_plugin->checkout()}
							</div>
							<span>Your billing address.</span>
							<div>
								<label for="bline1">Address line 1</label>
								<input type="text" class="insettext fullwidth" name="bline1" id="b-line1">
							</div>
							<div>
								<label for="bline2">Address line 2</label>
								<input type="text" class="insettext fullwidth" name="bline2" id="b-line2">
							</div>
							<div class="condensed">
								<label for="bcity">City</label>
								<input type="text" class="insettext fullwidth" name="bcity" id="b-city">
							</div>
							<div>
								<label for="bstate">State:</label>
								<select name="bstate" id="b-state">
									<option value="AL">Alabama</option>
									<option value="AK">Alaska</option>
									<option value="AZ">Arizona</option>
									<option value="AR">Arkansas</option>
									<option value="CA">California</option>
									<option value="CO">Colorado</option>
									<option value="CT">Connecticut</option>
									<option value="DE">Delaware</option>
									<option value="DC">District of Columbia
									<option value="FL">Florida</option>
									<option value="GA">Georgia</option>
									<option value="HI">Hawaii</option>
									<option value="ID">Idaho</option>
									<option value="IL">Illinois</option>
									<option value="IN">Indiana</option>
									<option value="IA">Iowa</option>
									<option value="KS">Kansas</option>
									<option value="KY">Kentucky</option>
									<option value="LA">Louisiana</option>
									<option value="ME">Maine</option>
									<option value="MD">Maryland</option>
									<option value="MA">Massachusetts</option>
									<option value="MI">Michigan</option>
									<option value="MN">Minnesota</option>
									<option value="MS">Mississippi</option>
									<option value=""MO>Missouri</option>
									<option value="MT">Montana</option>
									<option value="NE">Nebraska</option>
									<option value="NV">Nevada</option>
									<option value="NH">New Hampshire</option>
									<option value="NJ">New Jersey</option>
									<option value="NM">New Mexico</option>
									<option value="NY">New York</option>
									<option value="NC">North Carolina</option>
									<option value="ND">North Dakota</option>
									<option value="OH">Ohio</option>
									<option value="OK">Oklahoma</option>
									<option value="OR">Oregon</option>
									<option value="PA">Pennsylvania</option>
									<option value="RI">Rhode Island</option>
									<option value="SC">South Carolina</option>
									<option value="SD">South Dakota</option>
									<option value="TE">Tennessee</option>
									<option value="TX">Texas</option>
									<option value="UT">Utah</option>
									<option value="VT">Vermont</option>
									<option value="VA">Virginia</option>
									<option value="WA">Washington</option>
									<option value="WV">West Virginia</option>
									<option value="WI">Wisconsin</option>
									<option value="WY">Wyoming</option>
								</select>
							</div>
							<div class="condensed">
								<label for="bpostal">Postal code</label>
								<input type="text" class="insettext fullwidth" name="bpostal" id="b-postal">
							</div>
						</div>
					</fieldset>
					<div class="submit">
						<input type="submit" value="Proceed to confirmation" name="submit" id="submit">
					</div>
				</form>
				<div class="return"><a href="/order-place1.html" class="button">‹ Back</a></div>
			</div>
			</div>
			
<script type="text/javascript">
$('#buy input[name=delivery]')
	.change(function(e) {
		if(this.value == "0") {
			$("div#pickup_delivery div#deliveryForm").hide().css('visibility', 'hidden');
			$("div#pickup_delivery div#pickupForm").show().css('visibility', 'visible');
		} else {
			$("div#pickup_delivery div#deliveryForm").show().css('visibility', 'visible');
			$("div#pickup_delivery div#pickupForm").hide().css('visibility', 'hidden');
		}
	})
;
$('#buy input[name=plugin]')
	.change(function(e) {
		$.ajax({
			type: 'GET',
			url: '{devblocks_url}c=ajax&a=getCheckoutForm&{/devblocks_url}?id='+this.value,
			cache: false,
			success: function(html) {
				$('div#checkoutForm div#checkoutPlugin').html(html);
			}
		});
	})
;
</script>