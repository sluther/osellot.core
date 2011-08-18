			<div id="signup">
				<form id="register" method="post" action="">
					<input type="hidden" name="a" value="doRegister">
					<fieldset>

						<legend>Account details</legend>
						<span>A valid email address is required to order.</span>
						<div>
							<label for="email">Email</label>
							<input type="text" id="email" name="email" value="" size="35">
						</div>
						<div>

							<label for="cemail">Confirm email</label>
							<input type="text" id="cemail" name="cemail" value="" size="35">
						</div>
						<br>
						<span>Passwords must be at least twelve characters long.</span>
						<div>
							<label for="password">Password</label>

							<input type="password" id="password" name="password" value="" size="35">
						</div>
						<div>
							<label for="cpassword">Confirm password</label>
							<input type="password" id="cpassword" name="cpassword" value="" size="35">
						</div>
					</fieldset>
					<fieldset>

						<legend>Contact information</legend>
						<span>Your first and last name.</span>
						<div>
							<label for="name">Full name</label>
							<input type="text" id="name" name="name" value="" size="35">
						</div>
						<br>

						<span>The best number to contact you about your order.</span>
						<div>
							<label for="phone">Phone</label>
							<input type="text" id="phone" name="phone" value="" size="35">
						</div>
					</fieldset>
					<div class="submit">
						<input type="submit" id="submit" name="submit" value="Register account">

					</div>
				</form>
			</div>