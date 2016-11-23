<h4>Sign Up with Twitter</h4>
<?php
if ( $Account->logged ) redirect('?page=profile');

if (!empty($Account->api['twitter'])) {

	if(!empty($_SESSION['twitter']) and !empty($_SESSION['twitter']['oauth_token']) and !empty($_SESSION['twitter']['oauth_token_secret'])) {
		require_once('includes/twitteroauth/twitteroauth.php');
		$access_token = $_SESSION['twitter'];
		$connection = new TwitterOAuth($Account->api['twitter']['consumer_key'], $Account->api['twitter']['consumer_secret'],$access_token['oauth_token'], $access_token['oauth_token_secret']);
		$content = $connection->get('account/verify_credentials');
		
		$user = get_object_vars($content);
		
		if (!empty($user['name'])) {
			$data = array(
					'username'  => (isset($_POST['username'])) ? $_POST['username'] : $user['screen_name'],
					'email'     => substr(md5($user['id']), 0, 15).'@no.email',
					'oauth'     => 'twitter',
					'uid'       => $user['id'],
				);
			
			$Account->signup($data);
			$errors = $Account->errors;
			if(empty($errors)) {
				$Account->login( array( 'oauth' => 'twitter', 'uid' => $user['id'], 'remember'=>TRUE ));
				$errors = $Account->errors;
				if (empty($errors)) {
					
					/* --- Added in v1.4.8 --- */
					$profile_image_url = str_replace('_normal', '', $user['profile_image_url']);
					$Account->update_meta($Account->session('user_id'), 'twitter_image_url', $profile_image_url);
					/* ---- */

					redirect('index.php');
				}
				else {
					echo '<div class="alert alert-error">If this error persists use another <a href="?page=login">login</a> method.</div>';
					echo '<ul class="form-errors">';
					foreach ($errors as $key => $error)
						echo "<li>$error</li>";
					echo '</ul>';
				}
			}
			else {
				
				if (!empty($errors['username'])) {
					?>
					<div class="alert alert-error">Your Twitter username already exists in our database. Please chose another username.</div>
					<form action="" method="post">
						<label for="username">Username</label>
						<div class="input-prepend" style="margin-bottom: 0px;">
					    	<span class="add-on"><i class="icon-user"></i></span>
							<input type="text" name="username" id="username" placeholder="Username" value="<?php echo $user['screen_name']; ?>">
							<span class="help-inline"></span>
						</div>
						<input type="submit" name="continue" class="btn btn-primary" value="Continue">
					</form>
					<?php
				}
				else echo '<div class="alert alert-error">An unexpected error has occurred. Try again or use another <a href="?page=login">login</a> method.</div>';
			}
		}
		else {
			echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</a>';
			unset($_SESSION['twitter']);
		}
		
	}
	else redirect('?page=oauth&method=twitter');

}
else echo '<div class="alert alert-error">Feature disabled.</div>';