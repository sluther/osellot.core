			<div id="place">
				<form action="" method="post" id="buy">
					<input type="hidden" name="a" value="doCheckout">
					<span>The clients's first and last names.</span>
					<div>
						<label for="name" class="">Full name</label>
						<input type="text" size="35" value="" name="name" id="name">
					</div>
					<span>The clients's e-mail address.</span>
					<div>
						<label for="email" class="">E-mail</label>
						<input type="email" size="35" value="" name="email" id="email">
					</div>
					<span>The client's phone number.</span>
					<div>
						<label for="phone" class="">Phone</label>
						<input type="phone" size="35" value="" name="phone" id="phone">
					</div>
					<fieldset>
						<legend>Pickup or delivery</legend>
						<span>Select whether you want to pickup your order or have it delivered.</span>
						<div class="options">
							<div>
								<input type="radio" checked="checked" value="1" name="delivery" id="delivery">
								<label for="delivery">Client wants it delivered ($3)</label><br>
							</div>
							<div>
								<input type="radio" value="0" name="delivery" id="pickup">
								<label for="pickup">Client will pickup the order</label>
							</div>
						</div>
						<br>
						<div id="pickup_delivery">
							<div id="deliveryForm">
								<span>The client's street address the order will be delivered to.</span>
								<div>
									<label for="dline1">Line 1</label>
									<input type="text" size="35" value="" name="dline1" id="dline1">
								</div>
								<div>
									<label for="dline2">Line 2</label>
									<input type="text" size="35" value="" name="dline2" id="dline2">
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