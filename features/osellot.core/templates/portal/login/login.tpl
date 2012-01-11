			<div class="leftcolumn">
				<h2>Log In</h2>
				<div class="lcontentbox">
					<form id="ilogin" method="post" action="{devblocks_url}c=login&a=authenticate{/devblocks_url}">
						<input type="hidden" name="original_path" value="{$original_path}">
							<label for="email">Email</label>
							<input type="text" id="email" name="email" class="insettext fullwidth">
							<br>
							<label for="password">Password</label>
							<input type="password" id="password" name="password" class="insettext fullwidth">
							<input type="checkbox" id="remember" name="remember" value="1" checked="">
							<label for="iremember">Remember me</label><br>
							<button type="submit" name="ilogin" class="fright">Submit</button>
					</form>
					<a href="">Forgot your password?</a>

				</div>
			</div>
			<div class="rightcolumn">
				<h2>Register</h2>
				<div class="rcontentbox">
					<p>Please create a new account if you have not already done so.</p>
					<a href="{devblocks_url}c=login&a=register{/devblocks_url}" class="button">Create an account with us to order <span>&#8250;</span></a>
				</div>
			</div>