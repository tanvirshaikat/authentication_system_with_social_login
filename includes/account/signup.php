<?php
//If the user is logged redirect to Profile
if ( $Account->logged ) redirect('?page=profile');
?>

<h4>Sign Up</h4>

<!-- This div is hidden but will be shown on success -->
<div class="success-message hidden">
	<div class="alert alert-success">
      <strong>Well done!</strong> You successfully created an account.
    </div>
	<p>We emailed you to make sure we have the right email address.<br>
		Once you click the activation link in that email, you'll be ready to <a href="?page=login">login</a>.
	</p>
	If you did not recived the activation email request a new one <a href="?page=resend">here</a>.
</div>

<!-- BEGIN SIGNUP FORM -->
<form action="" method="post" id="signup">
	<p>To create an account fill out the form below.</p>
	
	<!-- This div is for the Ajax response. When the form is submited here will be displayed a message -->
	<div class="alert hidden ajax-response"><button type="button" class="close">×</button> <span></span></div>

	<?php
	//When form is submited
	if (isset($_POST['signup']))
	{
		//Create an array with data for signup function
		$data = array(
				'username'         => $_POST['username'],
				'email'            => $_POST['email'],
				'password'         => $_POST['password'], 
				'confirm_password' => $_POST['confirm_password'],
				'captcha'          => $_POST['captcha']
			);
		//Call signup function
		$Account->signup($data);

		//Get errors
		$errors = $Account->errors();
		//If we have errors display them
		if (!empty($errors))
		{
			echo '<div class="alert alert-error"><strong>Oh snap! </strong> There are some errors:<br>
			<ul class="form-errors">';
			foreach ($errors as $key => $error)
				echo "<li>$error</li>";
			echo '</ul></div>';
		}
		//This css will hide the form and show the success message div
		else echo "<style>.success-message{display:block;} form#signup{display:none;}</style>";
	}
	//Init captcha
	$Account->captcha();
	?>
	<!-- The action input is hidden and it's telling to jQuery Validate what function will have to call in the Ajax request -->
	<input type="hidden" name="action" value="signup">
	
	<div class="control-group">
		<label class="control-label" for="username">Username</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-user"></i></span>
				<input type="text" name="username" id="username" placeholder="Username" value="<?php echo $Account->set_value('username'); ?>">
				<span class="help-inline"></span>
			</div>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="email">Email Address</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-envelope"></i></span>
				<input type="text" name="email" id="email" placeholder="Email" value="<?php echo $Account->set_value('email'); ?>">
				<span class="help-inline"></span>
			</div>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="password">Password</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-lock"></i></span>
		    	<input type="password" name="password" id="password" placeholder="Password">
		    	<span class="help-inline"></span>
		    </div>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="confirm_password">Repeat Password</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-lock"></i></span>
		    	<input type="password" name="confirm_password" id="confirm_password" placeholder="Repeat Password">
		    	<span class="help-inline"></span>
		    </div>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label">Captcha Image</label>
		<div class="controls">
			<img src="includes/captcha.php" class="captcha-image">
		    <i class="icon-refresh refresh-captcha" onclick="Account.refreshCaptcha();" title="Reload Captcha"></i>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="captcha">Enter Captcha</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-lock"></i></span>
		    	<input type="text" name="captcha" id="captcha" placeholder="Captcha">
		    	<span class="help-inline"></span>
		    </div>
		</div>
	</div>

	<!-- For terms & conditions checkbox
	<div class="control-group">
		<div class="controls">
			<div>
				<style> #terms { margin-top: 0px; }</style>
		    	<input type="checkbox" name="terms" id="terms" value="" class="required"> I accept <a href="#">terms & conditions</a>
		    	<span class="help-inline"></span>
		    </div>
		</div>
	</div>
	-->

	<div class="control-group">
		<div class="controls">
			<input type="submit" name="signup" class="btn" value="Sign Up">
		</div>
	</div>

	<p>Or you can use any of these social logins:</p>
    <p>
    	<a href="?page=oauth&method=facebook" class="fb-login" title="Login with Facebook"></a>
   	 	<a href="?page=oauth&method=twitter" class="tw-login" title="Login with Twitter"></a>
    	<a href="?page=oauth&method=google" class="go-login" title="Login with Google"></a>
    </p>

</form>
<!-- end form #signup -->