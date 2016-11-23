<h4>Change Password</h4>

<?php
/**
   * Account
   * 
   * @author  Tanvir Shaikat <ta.shaikat@gmail.com>
   */
//If the user is logged redirect to Profile
if ( $Account->logged ) redirect('?page=profile');

//If we have a key continue
else if (!empty($_GET['key']) and $Account->reset_password($_GET['key'])) 
{
	
	?>

	<!-- This div is hidden but will be shown on success -->
	<div class="success-message hidden">
		<div class="alert alert-success">
        	The password has been changed. You may <a href="?page=login">login</a> now.
        </div>
	</div>
	<!-- BEGIN CHANGEPASS FORM -->
	<form action="" method="post" id="changepass">
		<p>To change your password fill the form below.</p>
		<!-- This div is for the Ajax response. When the form is submited here will be displayed a message -->
		<div class="alert hidden ajax-response"><button type="button" class="close">×</button> <span></span></div>
		
		<?php
		//When the button form was submited 
		if (isset($_POST['changepass'])) 
		{
			
			//Call change_password function 
			$Account->change_password($_POST['password'], $_POST['confirm_password'], $_GET['key']);
			
			//Get the errors
			$errors = $Account->errors();
			
			//If we have errors display them
			if (!empty($errors)) 
			{
				echo '<div class="alert alert-error"><strong>Oh snap! </strong> There are some errors:<br>
				<ul class="form_errors">';
				//Display each error from the $errors array
				foreach ($errors as $key => $error)
					echo "<li>$error</li>";

				echo '</ul></div>';
			}
			//Otherwise add some css that will hide the form and will show that success message from above
			else echo "<style>.success-message{display:block;} form#changepass{display:none;}</style>";
		}
		?>

		<!-- The action input is hidden and it's telling to jQuery Validate what function will have to call in the Ajax request -->
		<input type="hidden" name="action" value="changepass">
		<!-- Also we need to send the key and we need another hidden input -->
		<input type="hidden" name="key" value="<?php echo $Account->xss( $_GET['key'] ); ?>">
		
		<div class="control-group">
			<!-- Input label -->
			<label class="control-label" for="password">New Password</label>
			<div class="controls">
				<div class="input-prepend">
			    	<span class="add-on"><i class="icon-lock"></i></span>
			    	<!-- The password input -->
			    	<input type="password" name="password" id="password" placeholder="New Password">
			    	<!-- A placehoder where the error will be displayed -->
			    	<span class="help-inline"></span>
			    </div>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="confirm_password">Repeat Password</label>
			<div class="controls">
				<div class="input-prepend">
			    	<span class="add-on"><i class="icon-lock"></i></span>
			    	<!-- The repeat password input -->
			    	<input type="password" name="confirm_password" id="confirm_password" placeholder="Repeat Password">
			    	<!-- A placehoder where the error will be displayed -->
			    	<span class="help-inline"></span>
			    </div>
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<!-- The submit button -->
				<input type="submit" name="changepass" class="btn" value="Change Password">
			</div>
		</div>

	</form>
	<!-- end form #changepass -->
	<?php			        
}
//If there's no key display a error message
else echo '<div class="alert alert-error">
  		This recover link is not valid. Please <a href="?page=recover">request</a> a new recover email.
	</div>';

?>