<?php
//If the user is logged redirect to Profile
if ( $Account->logged ) redirect('?page=profile');
?>


<h4>Request New Activation Email</h4>

<!-- This div is hidden but will be shown on success -->
<div class="success-message hidden">
	<div class="alert alert-success"> Activation link sent ! </div>
	<p>We emailed you with a new activation link.<br>
		Once you click the activation link in that email, you'll be ready to <a href="?page=login">login</a>.
	</p>
</div>

<!-- BEGIN RESEND FORM -->
<form action="" method="post" id="resend">
	<p>If you have not received the registration confirmation email, you can request another one here. Please be sure to check your SPAM box also, sometimes the emails arrive there.</p>

	<!-- This div is for the Ajax response. When the form is submited here will be displayed a message -->
	<div class="alert hidden ajax-response"><button type="button" class="close">×</button> <span></span></div>
	
	<?php
	//When form is submited
	if (isset($_POST['submit'])) 
	{
		//Call resend function
		$Account->resend($_POST['email'], $_POST['captcha']);

		//Get the errors
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
		else echo "<style>.success-message{display:block;} form#resend{display:none;}</style>";
	}
	//Init captcha
	$Account->captcha();

	?>
	<!-- The action input is hidden and it's telling to jQuery Validate what function will have to call in the Ajax request -->
	<input type="hidden" name="action" value="resend">

	<div class="control-group">
		<label class="control-label" for="email">Email Address</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-envelope"></i></span>
				<input type="text" name="email" id="email" placeholder="Email" value="<?php echo $Account->set_value('email'); ?>">
				<!-- A placehoder where the error will be displayed -->
				<span class="help-inline"></span>
			</div>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label">Captcha Image</label>
		<div class="controls">
			<!-- The captcha image -->
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
		    	<!-- A placehoder where the error will be displayed -->
		    	<span class="help-inline"></span>
		    </div>
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
			<!-- The submit button -->
			<input type="submit" name="submit" class="btn" value="Submit">
		</div>
	</div>

</form>
<!-- end form #resend -->