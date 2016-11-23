<h4>Account Activation</h4>

<?php
/**
   * Account
   * 
   * @author  Tanvir Shaikat <ta.shaikat@gmail.com>
   */
//If the user is logged redirect to Profile
if ( $Account->logged ) redirect('?page=profile');

//If we have a key continue
else if (!empty($_GET['key'])) 
{

	//Call activation function
	$Account->activate($_GET['key']);
	//Get the errors
	$errors = $Account->errors();
	
	//If we have errors display just the first one using reset()
	if (!empty($errors))
		echo '<div class="alert alert-error">' . reset($errors) . '</div>';
	//And if we don't have errors display a success message
	else echo '<div class="alert alert-success">
        	<strong>Well done!</strong> You have successfully activated your account. You may <a href="?page=login">login</a> now.
    	</div>';
}
//We don't have an activation key so display a message
else echo '<div class="alert alert-error">
  		<strong>Oh snap!</strong> This activation link is not valid. Please <a href="?page=resend">request</a> a new activation email.
	</div>';

?>