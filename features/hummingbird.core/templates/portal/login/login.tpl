			<div id="order-choice">
				<div id="signin">
					<h2>Sign-in to your account to order</h2>

					<form id="ilogin" method="post" action="{devblocks_url}c=login&a=authenticate{/devblocks_url}">
						<input type="hidden" name="original_path" value="{$original_path}">
						<div>
							<label for="email">Email</label>
							<input type="text" id="email" name="email" value="" size="35">
						</div>
						<div>
							<label for="password">Password</label>
							<input type="password" id="password" name="password" value="" size="35">

						</div>
						<div class="submit">
							<input type="checkbox" id="remember" name="remember" value="1" checked="">
							<label for="iremember">Remember me</label><br>
							<input class="button" type="submit" id="ilogin" name="ilogin" value="Log in">
						</div>
					</form>
					<a href="">Forgot your password?</a>

				</div>
				<div id="register">
					<h2>New to the Good Food Box?</h2>
					<p>To order from the Good Food Box an account is required. If you do not already have one please create a new account to place an order.</p>
					<a href="{devblocks_url}c=login&a=register{/devblocks_url}" class="button">Create an account with us to order <span>&#8250;</span></a>
				</div>
			</div>

			<div id="order-other">
				<h2>Other options</h2>
				<p>Prefer to order by phone or in person?</p>
			</div>