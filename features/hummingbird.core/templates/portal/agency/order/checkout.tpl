			<div id="place">
				<form action="" method="post" id="buy">
					<input type="hidden" name="a" value="doCheckout">					
					<fieldset>
						<legend>Pickup or delivery</legend>
						<span>Select whether you want to pickup your order or have it delivered.</span>
						<div class="options">
							<div>
								<input type="radio" checked="checked" value="true" name="delivery" id="delivery">
								<label for="delivery">I want it delivered ($3)</label><br>
							</div>
							<div>
								<input type="radio" value="false" name="delivery" id="pickup">
								<label for="pickup">I will pickup my order</label>
							</div>
						</div>
						<br>
						<div id="pickup_delivery">
							<div id="deliveryForm">
								<span>The street address you would like the order delivered to.</span>
								<div>
									<label for="dstreet">Street address</label>
									<input type="text" size="35" value="" name="dstreet" id="daddress">
								</div>
								<div class="condensed">
									<label for="dmunicipality">Municipality</label>
									<input type="text" size="35" value="" name="dmunicipality" id="municipality">
									<!--
									<select id="municipality" name="municipality">
										<option value="victoria" selected="selected">Victoria</option>
										<option value="sooke">Sooke</option>
										<option value="sidney">Sidney</option>
										<option value="another">Another</option>
									</select>
									-->
								</div>
								<div class="condensed">
									<label for="dpostal">Postal code</label>
									<input type="text" size="35" value="" name="dpostal" id="postal">
								</div>
							</div>
							<div id="pickupForm" style="display: none; visibility: hidden">
								<span>The location you would like to pickup the order from.</span>
								<label for="pickuploc">Location:</label> 
								<select name="pickuploc" id="pickuploc">
									<option selected="selected" value="fernwood">Fernwood Community Centre</option>
									<option value="another1">Another location 1</option>
									<option value="another2">Another location 2</option>
									<option value="another3">Another location 3</option>
								</select>
							</div>
							<br>
						</div>
					</fieldset>
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
								<input type="text" size="35" value="" name="bline1" id="b-line1">
							</div>
							<div>
								<label for="bline2">Address line 2</label>
								<input type="text" size="35" value="" name="bline2" id="b-line2">
							</div>
							<div class="condensed">
								<label for="bcity">City</label>
								<input type="text" size="35" value="" name="bcity" id="b-city">
							</div>
							<div class="condensed">
								<label for="bzip">Postal code</label>
								<input type="text" size="35" value="" name="bzip" id="b-zip">
							</div>
							<div>
								<label for="bstate">Province:</label>
								<select name="bstate" id="b-state">
									<option value="ab">Alberta</option>
									<option selected="selected" value="bc">British Columbia</option>
									<option value="mb">Manitoba</option>
									<option value="nb">New Brunswick</option>
									<option value="nf">Newfoundland &amp; Labrador</option>
									<option value="nt">Northwest Territories</option>
									<option value="ns">Nova Scotia</option>
									<option value="nu">Nunavut</option>
									<option value="on">Ontario</option>
									<option value="pe">Prince Edward Island</option>
									<option value="qc">Quebec</option>
									<option value="sk">Saskatchewan</option>
									<option value="yk">Yukon</option>
								</select>
							</div>
						</div>
					</fieldset>
					<div class="submit">
						<input type="submit" value="Proceed to confirmation" name="submit" id="submit">
					</div>
				</form>
				<div class="return"><a href="/order-place1.html" class="button">‹ Back</a></div>
			</div>
			
<script type="text/javascript">
$('#buy input[name=delivery]')
	.change(function(e) {
		if(this.value == "false") {
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