	<div id="order-choice">
		<div id="signin">
			<h2>Sign-in to your agency to manage orders</h2>
			<form id="ilogin" method="post" action="{devblocks_url}c=agency&a=login&action=authenticate{/devblocks_url}">
				<div>
					<label for="email">Email</label>

					<input type="text" id="email" name="email" value="" size="35">
				</div>
				<!--
				<div>
					<label for="aname">Agency</label>
					<select id="aname" name="aname">
						<option value="fernwood" selected="selected">Fernwood Community Centre</option>
						<option value="another1">Another location 1</option>
						<option value="another2">Another location 2</option>
						<option value="another3">Another location 3</option>
					</select>
				</div>
				-->
				<div>
					<label for="password">Password</label>
					<input type="password" id="password" name="password" value="" size="35">
				</div>
				<div class="submit">

					<input type="checkbox" id="remember" name="remember" value="1" checked="">
					<label for="aremember">Remember me</label><br>
					<input class="button" type="submit" id="alogin" name="alogin" value="Log in">
				</div>
			</form>
			<a href="">Forgot your password?</a>
		</div>
	</div>

</section>