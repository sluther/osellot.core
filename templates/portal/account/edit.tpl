			<div class="leftcolumn">
				<h2>Change account details</h2>
				<div class="lcontentbox">
					<form id="edit" method="post" action="">
						<input type="hidden" name="a" value="doEdit">
						<input type="hidden" name="current_email" value="{$active_profile->getPrimaryAddress()->email}">
						<input type="hidden" name="current_phone" value="{$active_profile->phone}">
						<span>Enter your current password to verify</span>
						<div>
							<label for="current_password">Current password</label>
							<input type="password" id="current_password" name="current_password" class="insettext fullwidth">
						</div>
						<fieldset>
							<span>The best number to contact you about your order.</span>
							<div>
								<label for="phone">Phone</label>
								<input type="text" id="phone" name="phone" value="{$active_profile->phone}" class="insettext fullwidth">
							</div>
							<br />
							<span>Change your email. A valid email address is required to order.</span>
							<div>
								<label for="change_email">Email</label>
								<input type="text" id="change_email" name="email" value="{$active_profile->getPrimaryAddress()->email}" class="insettext fullwidth">
							</div>
							<span>Confirm your changed email.</span>
							<div>
								<label for="cchange_email">Confirm email</label>
								<input type="text" id="cchange_email" name="cemail" value="{$active_profile->getPrimaryAddress()->email}" class="insettext fullwidth">
							</div>
							<br />
							<span>Change your password. Passwords must be at least eight characters.</span>
							<div>
								<label for="password">Password</label>
								<input type="password" id="password" name="password" class="insettext fullwidth">
							</div>
							<span>Confirm your changed password. Passwords must be at least eight characters.</span>
							<div>
								<label for="cpassword">Confirm password</label>
								<input type="password" id="cpassword" name="cpassword" class="insettext fullwidth">
							</div>
						</fieldset>
						<div class="submit">
							<button type="submit" id="submit" name="submit" class="fright" >Edit account</button>
						</div>
					</form>
				</div>
			</div>