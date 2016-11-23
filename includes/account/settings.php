<?php
//If the user is not logged redirect to home
if ( !$Account->logged ) redirect('index.php');

//Create an array with our tab names
$tabs = array(
		'general'  =>'General',
		'options'  =>'Options',
		'password' =>'Password',
		'avatar'   =>'Avatar',
		'connect'  =>'Connect'
	);

//If the tab it's empty set it to the general tab
if (empty($_GET['tab']) or !array_key_exists($_GET['tab'], $tabs)) 
	$_GET['tab'] = 'general';

?>

<h4>Account Settings</h4>

<!-- BEGIN NAV -->
<ul class="nav nav-pills">
	<?php
	//Display each tab
	foreach ($tabs as $key => $tab)
		echo '<li'.( ($_GET['tab']==$key) ? ' class="active"' : '' ).'>
			<a href="?page=settings&tab='.$key.'">'.$tab.'</a>
		</li>';
	?>
</ul>
<!-- end div .nav -->

<?php
//When the form is submited save data
if (isset($_POST['save']))
{
	$data = array();
	//Switch by the action 
	switch ($_POST['action'])
	{
		case 'general':
			//create the data array with the submited data
			$data = array(
					'first_name'   => $_POST['first_name'],
					'last_name'    => $_POST['last_name'],
					'email'        => $_POST['email'],
					'display_name' => $_POST['display_name'],
					'url'          => $_POST['url'],
					'usermeta'     => array( 'about' => $Account->xss($_POST['about']) ),
				);
		break;
		
		case 'options':
			//You can add your own options here
			/*$data['usermeta'] = array(
				'phone' => $Account->xss($_POST['phone'])
			);*/
		break;
		
		case 'password':
			//create the data array with the submited data
			$data = array(
					'password'=>$_POST['password'],
					'confirm_password'=>$_POST['confirm_password']
				);
		break;
	}
	//Call save_settings function
	$Account->save_settings($data);
	//Get the errors
	$errors = $Account->errors();
	//If we have errors display them
	if (!empty($errors))
	{
		if ($_POST['action']=='password') 
			echo '<div class="alert alert-error"><strong>Oh snap! </strong> There are some errors:';
		else echo '<div class="alert">Your changes have been saved, however there are some errors:';
			echo '<br><ul class="form-errors">';
			foreach ($errors as $key => $error) {
				echo "<li>$error</li>";
			}
			echo '</ul></div>';
	}
	//Else display success message
	else echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button> Your changes have been saved.</div>';
		
}
//Call the get_user function to get user data
$data = $Account->get_user( $Account->session('user_id') );
//Extract the array into variables

	
if (is_array($data)) {
	extract($data);

	//Switch the pages by the tab

	switch ($_GET['tab'])
	{
		case 'general': default:
			//Get about meta
			$about = $Account->get_meta( $Account->session('user_id'), 'about' );
			?>
			<!-- BEGIN SETTINGS FORM -->
			<form action="" method="post" id="settings">
				<input type="hidden" name="action" value="general">
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
					<label for="email">Email</label>
					<input type="text" name="email" id="email" value="<?php echo $email; ?>">
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
				<div>
					<label for="url">Website</label>
					<input type="text" name="url" id="url" value="<?php echo $data['url']; ?>">
				</div>
				<div>
					<label for="about">About me</label>
					<textarea name="about" id="about" style="width: 350px; height: 90px;"><?php echo $about; ?></textarea>
				</div>
				<input type="submit" name="save" class="btn btn-primary" value="Save Changes">
			</form>
			<!-- end form #settings -->
			<?php
		break;
		case'options':
			//$phone = $Account->get_meta( $Account->session('user_id'), 'phone' );
			//Here you can add more options 
			?>
			<form action="" method="post">
				<input type="hidden" name="action" value="options">
				<?php /* ?>
				<div>
					<label for="phone">Phone</label>
					<input type="text" name="phone" id="phone" value="<?php echo $phone; ?>">
				</div>
				<?php */ ?>
				<p><em>Add more meta fields..</em> </p>
				<input type="submit" name="save" class="btn btn-primary" value="Save Changes">
			</form>
			<?php
		break;
		case 'password':
			?>
			<p>If you want to change your password, fill the form below.</p>
			<form action="" method="post">
				<input type="hidden" name="action" value="password">
				<div>
					<label for="password">New password</label>
					<input type="password" name="password" id="password">
				</div>
				<div>
					<label for="confirm_password">Repeat new password</label>
					<input type="password" name="confirm_password" id="confirm_password">
				</div>
				<input type="submit" name="save" class="btn btn-primary" value="Change Password">
			</form>
			<?php
		break;	
		case'avatar':
			if (isset($_POST['save']))
				if (in_array($_POST['avatar'], array('facebook', 'google', 'twitter', 'gravatar', 'uploaded'))) 
					$Account->update_meta($Account->session('user_id'), 'avatar', $_POST['avatar']);
				
			//Get the avatar type
			$avatar = $Account->get_meta($Account->session('user_id'), 'avatar');

			//Create an array with socials 
			$socials = array(
					'facebook' => $Account->get_meta($Account->session('user_id'), 'facebook'),
					'twitter' => $Account->get_meta($Account->session('user_id'), 'twitter'),
					'google' => $Account->get_meta($Account->session('user_id'), 'google'),
					'uploaded'=> $Account->get_meta($Account->session('user_id'), 'uploaded'),
				);
			?>

			<form action="" method="post">
				<input type="hidden" name="action" value="avatar">
				<p>To change your avatar you have the following options:</p>
				<div>
					<select name="avatar" id="social-avatar">
						<option value="" <?php echo (empty($avatar) or $avatar=='') ? 'selected' : ''; ?>>None</option>
						<?php
						foreach ($socials as $key => $v)
						{
							if (!empty($v)) {
								echo '<option value="'.$key.'" '.(($avatar==$key) ? 'selected' : '').'>'.ucfirst($key).'</option>';
							}
							else echo '<option value="'.$key.'" disabled>'.ucfirst($key).' '.(($key=='uploaded') ? '(upload image first)' : '(not connected)').'</option>';
						}
						?>
						<option value="gravatar" <?php echo ($avatar=='gravatar') ? 'selected' : ''; ?>>Gravatar</option>
					</select>
					<input type="submit" name="save" class="btn btn-primary" value="Save Changes">
					&nbsp;&nbsp;
					<div class="btn-group">
					  <button type="button" class="btn" id="uploadimage">Upload Image</button>
					  <button type="button" class="btn" onclick="webcamSnapshot()">Webcam Snapshot</button>
					</div>

				</div>
			</form>

			<!-- Some css for this page -->
			<style type="text/css">
				img { max-width: none; }
				.thumbnail { display:inline-block; }
				.thumbnail .img { max-width: 750px; }
				#upload_container, #webcam_container { margin-top: 15px; }
				#social-avatar {margin-bottom: 0px;}
			</style>
			<!-- Scripts for avatar uploader, webcam and crop -->
			<script type="text/javascript" src="assets/js/ajaxupload.3.5.js"></script>
			<script type="text/javascript" src="assets/js/jquery.imgareaselect.min.js"></script>
			<script type="text/javascript" src="assets/webcam/webcam.js"></script>
			<script type="text/javascript" src="assets/js/avatar-upload.js"></script>

			<!-- BEGIN UPLOAD CONTAINER -->
			<div id="upload_container" class="hidden">
				<div class="alert hidden"> <button type="button" class="close" data-dismiss="alert">&times;</button> <span></span> </div>
				<div class="crop">
				</div>
			</div>
			<!-- end div #upload_container -->
			
			<!-- BEGIN UPLOAD CONTAINER -->
			<div id="webcam_container" class="hidden">
				<div class="alert hidden"> <button type="button" class="close" data-dismiss="alert">&times;</button> <span></span> </div>
				<p id="webcam">
				</p>
				<div class="crop">
				</div>
				<p class="control">
					<button type="button" class="btn btn-small cancel"> <i class="icon-remove"></i> Cancel</button>
					<button class="btn btn-primary btn-small" onClick="webcam.snap()"> <i class="icon-camera icon-white"></i> Take Snapshot</button>
				</p>
			</div>
			<!-- end div #webcam_container -->

			<input type="hidden" name="x1" value="" id="x1" />
			<input type="hidden" name="y1" value="" id="y1" />
			<input type="hidden" name="w" value="" id="w" />
			<input type="hidden" name="h" value="" id="h" />

		<?php
		break;
		case'connect':
			if (isset($_GET['disconnect'])) 
			{
				if (in_array($_GET['disconnect'], array('facebook', 'google', 'twitter'))) 
				{
					unset($_SESSION[ $_GET['disconnect'] ]);
					$Account->delete_meta($Account->session('user_id'), $_GET['disconnect']);
					$Account->set_cookie('_account_oauth', '');
				}
			}
			$fb = $Account->get_meta($Account->session('user_id'), 'facebook');
			$tw = $Account->get_meta($Account->session('user_id'), 'twitter');
			$go = $Account->get_meta($Account->session('user_id'), 'google');
			?>
			<p>By connecting with any of this socials you'll be able to sign in with them.</p>
			<ul class="socials">
				<li class="fb">
					<div><i></i> <span>Facebook</span></div>
				  	<?php if (empty($fb)): ?>
				  		<a href="?page=oauth&method=facebook" class="btn btn-info btn-small">Connect</a>
				  	<?php else: ?>
				  		<a href="?page=settings&tab=connect&disconnect=facebook" class="btn btn-success btn-small">Disconnect</a>
				  	<?php endif; ?>
				</li>
				<li class="tw">
					<div><i></i> <span>Twitter</span></div>
					<?php if (empty($tw)): ?>
				  		<a href="?page=oauth&method=twitter" class="btn btn-info btn-small">Connect</a>
				  	<?php else: ?>
				  		<a href="?page=settings&tab=connect&disconnect=twitter" class="btn btn-success btn-small">Disconnect</a>
				  	<?php endif; ?>
				</li>
				<li class="go">
					<div><i></i> <span>Google+</span></div>
				  	<?php if (empty($go)): ?>
				  		<a href="?page=oauth&method=google" class="btn btn-info btn-small">Connect</a>
				  	<?php else: ?>
				  		<a href="?page=settings&tab=connect&disconnect=google" class="btn btn-success btn-small">Disconnect</a>
				  	<?php endif; ?>
				</li>
			</ul>
			<p><span class="label label-warning">Warning</span> If you disconnect all of your social accounts make sure you have set a <a href="?page=settings&tab=password">password</a> and an <a href="?page=settings&tab=general">email</a> so you can use them to login.</p>
			<?php
		break;
	}
} else echo '<div class="alert alert-error">Error. Plase logout and login back.</div>';
?>