<?php
//If the user is logged redirect to Index
if ( $Account->logged ) redirect('index.php');
?>

<h4>Login</h4>

<p>To login to your account fill out the form below if you signed up using the normal registration form.</p>

<!-- This div is for the Ajax response. When the form is submited here will be displayed a message -->
<div class="alert hidden ajax-response"><button type="button" class="close">×</button> <span></span></div>

<?php

//If the form is submited
if (isset($_POST['login'])) 
{
	//Create an array with the data needed for the login function
	$data = array(
			'user'     => $_POST['user'],
			'password' => $_POST['password'],
			'remember' => (isset($_POST['remember'])) ? TRUE : FALSE
		);
	//Call the login function
	$Account->login($data);

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
	//Else the user is logged and refresh the page
	else redirect('index.php');
}

?>

<!-- BEGIN LOGIN FORM -->
<form action="" method="post" id="login">

	<!-- The action input is hidden and it's telling to jQuery Validate what function will have to call in the Ajax request -->
	<input type="hidden" name="action" value="login">

	<div class="control-group">
		<!-- Input label -->
		<label class="control-label" for="user">Email</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-user"></i></span>
		    	<!-- The user input -->
				<input type="text" name="user" id="user" placeholder="Email or Username" value="<?php echo $Account->set_value('user'); ?>">
				<!-- A placehoder where the error will be displayed -->
				<span class="help-inline"></span>
			</div>
		</div>
	</div>

	<div class="control-group">
		<!-- The password input -->
		<label class="control-label" for="password">Password</label>
		<div class="controls">
			<div class="input-prepend">
		    	<span class="add-on"><i class="icon-lock"></i></span>
		    	<!-- The password input -->
		    	<input type="password" name="password" id="password" placeholder="Password">
		    	<!-- A placehoder where the error will be displayed -->
		    	<span class="help-inline"></span>
		    </div>
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
			<!-- The checkbox for remember me option -->
			<label class="checkbox"><input type="checkbox" name="remember" id="remember"> Remember me</label>
			<!-- The submit button -->
			<input type="submit" name="login" class="btn" value="Login">
		</div>
	</div>

</form>
<!-- end form #login -->

<!-- Display socials links for login -->
<p>Or you can use any of these social logins:</p>
<p>
	<a href="?page=oauth&method=facebook" class="fb-login" title="Login with Facebook"></a>
    <a href="?page=oauth&method=twitter" class="tw-login" title="Login with Twitter"></a>
    <a href="?page=oauth&method=google" class="go-login" title="Login with Google"></a>
</p>

<a href="?page=recover">Forgot password ?</a> | <a href="?page=resend">Request activation email.</a>