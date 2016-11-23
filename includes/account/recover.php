<?php
//If the user is logged redirect to Profile
if ( $Account->logged ) redirect('?page=profile');
?>

<h4>Recover Password</h4>

<!-- This div is hidden but will be shown on success -->
<div class="success-message hidden">
	<div class="alert alert-success"> Recover link sent! </div>
	<p>We emailed you with a recover link.<br>
		Once you click the recover link in that email, you'll be able to change your password.
	</p>
</div>

<!-- BEGIN RECOVER FORM -->
<form action="" method="post" id="recover">
	<p>If you have lost or forgotten your password you can reset it via the form below. Simply enter your email below and we will send you an email with a recover link so you can  change your password.</p>
	
	<!-- This div is for the Ajax response. When the form is submited here will be displayed a message -->
	<div class="alert hidden ajax-response"><button type="button" class="close">×</button> <span></span></div>
	
	<?php
	//If the form was submited
	if (isset($_POST['submit'])) 
	{
		//Call recover function
		$Account->recover($_POST['email'], $_POST['captcha']);
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
	//Init the captcha
	$Account->captcha();

	?>
	<!-- The action input is hidden and it's telling to jQuery Validate what function will have to call in the Ajax request -->
	<input type="hidden" name="action" value="recover">

	<div class="control-group">
		<!-- Email label -->
		<label class="control-label" for="email">Email Address</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-envelope"></i></span>
				<!-- The email input -->
				<input type="text" name="email" id="email" placeholder="Email">
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
		<!-- Captcha label -->
		<label class="control-label" for="captcha">Enter Captcha</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-lock"></i></span>
		    	<!-- The repeat captcha input -->
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
<!-- end form #recover -->