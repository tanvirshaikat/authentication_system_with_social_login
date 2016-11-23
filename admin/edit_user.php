<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h1 class="title">Edit User</h1>
<?php
if (isset($_GET['id']) and is_numeric($_GET['id']) and $_GET['id'] > 0 ) {
	
	//If the form is submited
	if (isset($_POST['save'])) {
		//Create an array with data
		$data = array(
			'user_id'      => $_GET['id'],
			'username'     => $_POST['username'],
			'first_name'   => $_POST['first_name'],
			'last_name'    => $_POST['last_name'],
			'email'        => $_POST['email'],
			'display_name' => $_POST['display_name'],
			'url'          => $_POST['url'],
			'usermeta'     => array('about' => $Account->xss($_POST['about']), 'role' => $Account->xss($_POST['role']) ),
		);

		//Do some checks and add more data
		//Check for status
		if (in_array($_POST['status'], array(1, 2, 3)))
			$data['status'] = $_POST['status'];

		//Check for avatar 
		if (in_array($_POST['avatar'], array('facebook', 'google', 'twitter', 'gravatar', '')))
			$data['usermeta']['avatar'] = $_POST['avatar'];

		//Check for password
		if ( !empty($_POST['password']) ) {
			$data['password'] = $_POST['password'];
			$data['confirm_password'] = $_POST['confirm_password'];
		}

		//Call save_settings function
		$Account->save_settings($data);
		//Get the errors
		$errors = $Account->errors();
		//If we have errors display them
		if (!empty($errors))
		{
			echo '<div class="alert">The changes have been saved, however there are some errors:';
			echo '<br><ul class="form-errors">';
			foreach ($errors as $key => $error) {
				echo "<li>$error</li>";
			}
			echo '</ul></div>';
		}
		//Else display success message
		else echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button> The changes have been saved.</div>';

	}

	//Get user data
	$user = $Account->get_user ( $_GET['id']  );

	//If the user exists 
	if ($user and !empty($user)) {
		
		//Get about meta
		$about = $Account->get_meta( $user['id'], 'about' );
		//Get the avatar type
		$avatar = $Account->get_meta($user['id'], 'avatar');
		//Get user role meta
		$role = $Account->get_meta($user['id'], 'role');
		//Create an array with socials 
		$socials = array(
				'facebook' => $Account->get_meta($user['id'], 'facebook'),
				'twitter' => $Account->get_meta($user['id'], 'twitter'),
				'google' => $Account->get_meta($user['id'], 'google')
			);

		extract($user);

		//Disply the form
		?>
		<form action="" method="post" id="settings">
			<p>
				<input type="submit" name="save" class="btn btn-primary save" value="Save Changes">
			</p>
			<div>
				<label for="username">Username</label>
				<input type="text" name="username" id="username" value="<?php echo $username; ?>">
				<span class="help-inline"></span>
			</div>
			<div>
				<label for="email">Email</label>
				<input type="text" name="email" id="email" value="<?php echo $email; ?>">
				<span class="help-inline"></span>
			</div>
			<div>
				<label for="status">Account Status</label>
				<select name="status" id="status">
					<option value="1" <?php echo ($user['status']==1) ? 'selected' : '' ?>>Activated</option>
					<option value="2" <?php echo ($user['status']!=1 and $user['status']!=2) ? 'selected' : '' ?>>Unactivated</option>
					<option value="0" <?php echo ($user['status']==2) ? 'selected' : '' ?>>Banned</option>
				</select>
			</div>
			<div>
				<label for="role">User Role</label>
				<select name="role" id="role">
					<option value="user" <?php echo ($role=='user') ? 'selected' : '' ?>>user</option>
					<option value="admin" <?php echo ($role=='admin') ? 'selected' : '' ?>>admin</option>
				</select>
			</div>
			<div>
				<label for="first_name">First Name</label>
				<input type="text" name="first_name" id="first_name" value="<?php echo $first_name; ?>">
				<span class="help-inline"></span>
			</div>
			<div>
				<label for="last_name">Last Name</label>
				<input type="text" name="last_name" id="last_name" value="<?php echo $last_name; ?>">
			</div>
			<div>
				<label for="social-avatar">Social Options</label>
				<select name="avatar" id="social-avatar">
					<option value="" <?php echo (empty($avatar) or $avatar=='') ? 'selected' : ''; ?>>None</option>
					<?php
					foreach ($socials as $key => $v)
					{
						if (!empty($v)) {
							echo '<option value="'.$key.'" '.(($avatar==$key) ? 'selected' : '').'>'.ucfirst($key).'</option>';
						}
						else echo '<option value="'.$key.'" disabled>'.ucfirst($key).' (not connected)</option>';
					}
					?>
					<option value="gravatar" <?php echo ($avatar=='gravatar') ? 'selected' : ''; ?>>Gravatar</option>
				</select>
			</div>
			<div>
				<label for="url">Website</label>
				<input type="text" name="url" id="url" value="<?php echo $url; ?>">
			</div>
			<div>
				<label for="display_name">Display Name</label>
				<select name="display_name" id="display_name">
					<?php
					echo '<option value="'.$username.'" '.( ($display_name == $fl) ? 'selected' : '' ).'>'.$username.'</option>';

					$fl = trim($first_name.' '.$last_name);
					$lf = trim($last_name.' '.$first_name);
					if (!empty($fl))
						echo '<option value="'.$fl.'" '.( ($display_name == $fl) ? 'selected' : '' ).'>'.$fl.'</option>';
					if (!empty($lf))
						echo '<option value="'.$lf.'" '.( ($display_name == $lf) ? 'selected' : '' ).'>'.$lf.'</option>';
					?>
				</select>
			</div>
			<p> <em>If you want to change the password complete the following two fields otherwhise leave them blank. </em> </p>
			<div>
				<label for="password">New password</label>
				<input type="password" name="password" id="password" value="">
			</div>
			<div>
				<label for="confirm_password">Repeat new password</label>
				<input type="password" name="confirm_password" id="confirm_password">
			</div>
			<div>
				<label for="about">About</label>
				<textarea name="about" id="about" style="width: 350px; height: 90px;"><?php echo $about; ?></textarea>
			</div>
			<input type="submit" name="save" class="btn btn-primary save" value="Save Changes">
		</form>
	<?php
	}
	else echo '<div class="alert alert-error">User not found.</div> <a href="?page=users" class="btn btn-small">&larr; Go Back</a>';
}
else echo '<div class="alert alert-error">User not found.</div> <a href="?page=users" class="btn btn-small">&larr; Go Back</a>';
?>