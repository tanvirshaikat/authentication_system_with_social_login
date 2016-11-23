<h4>Sign Up with Google</h4>
<?php
if ( $Account->logged ) redirect('?page=profile');

if (!empty($Account->api['google'])) {

	require_once 'includes/googleoauth/apiClient.php';
	require_once 'includes/googleoauth/contrib/apiPlusService.php';
	require_once ('includes/googleoauth/contrib/apiOauth2Service.php');

	$client = new apiClient();
	$client->setApplicationName("Google+ Authentication");
	$client->setClientId($Account->api['google']['client_id']);
	$client->setClientSecret($Account->api['google']['client_secret']);
	$client->setRedirectUri($Account->api['google']['redirect_uri']);
	$client->setDeveloperKey($Account->api['google']['api_key']);
	$plus = new apiPlusService($client);
	$oauth2 = new apiOauth2Service($client);

	if(isset($_GET['code'])) {
		$client->authenticate();
		$_SESSION['google']['token'] = $client->getAccessToken();
		redirect('?page=oauth&method=google');
	}

	if(isset($_SESSION['google']['token']))
		$client->setAccessToken($_SESSION['google']['token']);

	if($client->getAccessToken()) {
		$user = $oauth2->userinfo->get();
		try {
			$user = $plus->people->get('me');
			$userinfo = $oauth2->userinfo->get();
			$_SESSION['google']['user_id'] = $user['id'];		
			
			$username = str_replace(' ', '', $user['displayName']);
			$data = array(
					'username' => (isset($_POST['username'])) ? $_POST['username'] : $username,
					'email'    => $userinfo['email'],
					'oauth'    => 'google',
					'uid'      => $user['id']
				);
			
			$Account->signup($data);
			$errors = $Account->errors;
			if(empty($errors)) {
				$Account->login( array( 'oauth' => 'google', 'uid' => $user['id'], 'remember'=>TRUE ));
				$errors = $Account->errors;
				if (empty($errors))
					redirect('index.php');
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
					<div class="alert alert-error">Your Google username already exists in our database. Please chose another username.</div>
					<form action="" method="post">
						<label for="username">Username</label>
						<div class="input-prepend" style="margin-bottom: 0px;">
					    	<span class="add-on"><i class="icon-user"></i></span>
							<input type="text" name="username" id="username" placeholder="Username" value="<?php echo $username; ?>">
						</div>
						<input type="submit" name="continue" class="btn btn-primary" value="Continue">
					</form>
					<?php
				}
				else if (!empty($errors['email']))
					echo '<div class="alert alert-error">Your Google email already exists in our database. Use another <a href="?page=login">login</a> method.</div>';
				else echo '<div class="alert alert-error">An unexpected error has occurred. Try again or use another <a href="?page=login">login</a> method.</div>';
			}


		} catch (apiServiceException $e) {
			echo '<div class="alert alert-error">An unexpected error has occurred. Try again or use another <a href="?page=login">login</a> method.</div>';
			unset($_SESSION['google']);
		}
	}
	else redirect('?page=oauth&method=google');
}
else echo '<div class="alert alert-error">Feature disabled.</div>';
?>