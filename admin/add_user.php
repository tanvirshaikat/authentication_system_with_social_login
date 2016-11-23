<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h1 class="title">Add User</h1>
<?php

//If the form is submited
if (isset($_POST['add'])) {

	//Create an array with data
	$data = array(
		'username'     => $_POST['username'],
		'first_name'   => $_POST['first_name'],
		'last_name'    => $_POST['last_name'],
		'email'        => $_POST['email'],
		'url'          => $_POST['url'],
		'usermeta'     => array('role' => $Account->xss($_POST['role']) , 'avatar'=>'gravatar'),
		'password'     => $_POST['password'],
		'confirm_password' => $_POST['confirm_password']
	);
	if (!empty($_POST['send_email']))
		$data['send_email'] = TRUE;

	//Call save_settings function
	$Account->add_user($data);
	//Get the errors
	$errors = $Account->errors();
	//If we have errors display them
	if (!empty($errors))
	{
		echo '<div class="alert alert-error"><strong>Oh snap! </strong> There are some errors:';
		echo '<br><ul class="form-errors">';
		foreach ($errors as $key => $error) {
			echo "<li>$error</li>";
		}
		echo '</ul></div>';
	}
	//Else display success message
	else echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button> The new user wass added.</div>';

}
?>
<form action="" method="post" id="settings">
	<div>
		<label for="username">Username</label>
		<input type="text" name="username" id="username" value="<?php echo $Account->set_value('username'); ?>">
		<span class="help-inline"></span>
	</div>
	<div>
		<label for="email">Email</label>
		<input type="text" name="email" id="email" value="<?php echo $Account->set_value('email'); ?>">
		<span class="help-inline"></span>
	</div>
	<div>
		<label for="role">User Role</label>
		<?php $role =  $Account->set_value('role'); ?>
		<select name="role" id="role">
			<option value="user" <?php echo ($role=='user') ? 'selected' : '' ?>>user</option>
			<option value="admin" <?php echo ($role=='admin') ? 'selected' : '' ?>>admin</option>
		</select>
	</div>
	<div>
		<label for="first_name">First Name</label>
		<input type="text" name="first_name" id="first_name" value="<?php echo $Account->set_value('first_name'); ?>">
		<span class="help-inline"></span>
	</div>
	<div>
		<label for="last_name">Last Name</label>
		<input type="text" name="last_name" id="last_name" value="<?php echo $Account->set_value('last_name'); ?>">
	</div>
	<div>
		<label for="url">Website</label>
		<input type="text" name="url" id="url" value="<?php echo $Account->set_value('url'); ?>">
	</div>
	<div>
		<label for="password">Password</label>
		<input type="password" name="password" id="password" value="">
	</div>
	<div>
		<label for="confirm_password">New password</label>
		<input type="password" name="confirm_password" id="confirm_password">
	</div>
	<div>
		<label> <input type="checkbox" name="send_email" id="send_email"> Send this password to the new user by email.</label>
	</div>
	<input type="submit" name="add" class="btn btn-primary save" value="Add User">
</form>