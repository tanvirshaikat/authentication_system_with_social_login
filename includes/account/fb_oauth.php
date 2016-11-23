<h4>Sign Up with Facebok</h4>
<?php
if ( $Account->logged ) redirect('?page=profile');

if (!empty($Account->api['facebook'])) {

	require_once('includes/facebookoauth/facebook.php');

	$facebook = new Facebook(array(
		'appId'  => $Account->api['facebook']['app_id'],
		'secret' => $Account->api['facebook']['secret']
	));
	$user = $facebook->getUser();
	if($user) {
		try {
			$user = $facebook->api('/me');
			$data = array(
					'username' => (isset($_POST['username'])) ? $_POST['username'] : $user['username'],
					'email'    => $user['email'],
					'oauth'    => 'facebook',
					'uid'      => $user['id']
				);
			
			$Account->signup($data);
			$errors = $Account->errors;
			if(empty($errors)) {
				$Account->login( array( 'oauth' => 'facebook', 'uid' => $user['id'], 'remember'=>TRUE ));
				$errors = $Account->errors;
				if (empty($errors))
					redirect('index.php');
				else {
					echo '<div class="alert alert-error">If this error persists use another <a href="?page=login">login</a> method.</div>';
					echo '<ul class="form-errors">';
					foreach ($errors as $key => $error)
						echo "<li>$error</li>";
					echo '</ul>';
					unset($_SESSION['facebook']);
				}
			}
			else {

				if (!empty($errors['username'])) {
					?>
					<div class="alert alert-error">Your Facebook username already exists in our database. Please chose another username.</div>
					<form action="" method="post">
						<label for="username">Username</label>
						<div class="input-prepend" style="margin-bottom: 0px;">
					    	<span class="add-on"><i class="icon-user"></i></span>
							<input type="text" name="username" id="username" placeholder="Username" value="<?php echo $user['username']; ?>">
						</div>
						<input type="submit" name="continue" class="btn btn-primary" value="Continue">
					</form>
					<?php
				}
				else if (!empty($errors['email']))
					echo '<div class="alert alert-error">Your Facebook email already exists in our database. Use another <a href="?page=login">login</a> method.</div>';
				else echo '<div class="alert alert-error">An unexpected error has occurred. Try again or use another <a href="?page=login">login</a> method.</div>';
			}

		} catch(FacebookApiException $e){
			echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</div>';
			unset($_SESSION['facebook']);
		}
	}
	else redirect('?page=oauth&method=facebook');
}
else echo '<div class="alert alert-error">Feature disabled.</div>';
?>