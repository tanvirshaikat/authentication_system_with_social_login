<h4>oAuth Login</h4>
<?php
if (!isset($_GET['method']))
	redirect('index.php');

if (!empty($Account->api[ $_GET['method'] ])) {

	switch ($_GET['method']) {
		case 'facebook':
			require_once('includes/facebookoauth/facebook.php');
			$facebook = new Facebook(array(
				'appId'  => $Account->api['facebook']['app_id'],
				'secret' => $Account->api['facebook']['secret']
			));
			$user = $facebook->getUser();
			if ($user){
				try {
					echo'<div class="alert">Connecting to Facebook...</div>';
					$user = $facebook->api('/me');
					$_SESSION['facebook']['user_id'] = $user['id'];

					if(!$Account->logged) {
						
						if ($Account->login(array('oauth' => 'facebook', 'uid' => $user['id'],'remember'=>TRUE)))
							redirect('index.php');
						else redirect('?page=fb_oauth');
						
					}
					else {
						$db = new Database();
						if ($db->select('usermeta', 'id', '(meta_key="facebook" and meta_value="'.$user['id'].'" and user_id!="'.$Account->session('user_id').'")', null, 1)) {
							echo '<div class="alert alert-error">This Facebook account is already connected to another account. '.
							( (!$Account->logged) ? 'Use another <a href="?page=login">login</a> method.' : '' )
							.' </div>';
							unset($_SESSION['facebook']);
						}
						else {
							if ($Account->update_meta($Account->session('user_id'), 'facebook', $user['id']) )
								redirect('index.php');
							else echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</div>';
						}
					}
				} catch (FacebookApiException $e) {
					echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</div>';
				}
			}
			else {
			 	$url = $facebook->getLoginUrl(	array(
			  		'scope' => 'publish_stream, email, status_update'
				));
				redirect($url);
			}
		break;

		case 'twitter':
			require_once('includes/twitteroauth/twitteroauth.php');
			
			if (isset($_GET['cb'])){
				if (isset($_REQUEST['oauth_token']) and $_SESSION['twitter']['oauth_token'] !== $_REQUEST['oauth_token']) {
					
					$_SESSION['twitter']['oauth_status'] = 'oldtoken';
					unset($_SESSION['twitter']);
					redirect('?page=oauth&method=twitter');

				}
				if (isset($_GET['denied']))
					echo '<div class="alert alert-error">You have denied permission to access your account. <a href="?page=oauth&method=twitter">Try again</a></div>';
				else { 
					echo'<div class="alert">Connecting to Twitter...</div>';				
					$connection = new TwitterOAuth(
							$Account->api['twitter']['consumer_key'],
							$Account->api['twitter']['consumer_secret'],
							$_SESSION['twitter']['oauth_token'],
							$_SESSION['twitter']['oauth_token_secret']
						);
					$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);	
					$_SESSION['twitter'] = $access_token;
					
					if ($connection->http_code == 200) {
						$_SESSION['twitter']['status'] = 'verified';
				  		redirect('?page=oauth&method=twitter');
					}
					else {
						unset($_SESSION['twitter']);
						redirect('?page=oauth&method=twitter');
					}
				}
			}
			else if (empty($_SESSION['twitter']) or empty($_SESSION['twitter']['oauth_token']) or empty($_SESSION['twitter']['oauth_token_secret']) or empty($_SESSION['twitter']['user_id'])) {

					unset($_SESSION['twitter']);
					$connection = new TwitterOAuth(
							$Account->api['twitter']['consumer_key'],
							$Account->api['twitter']['consumer_secret']
						);
					$request_token = $connection->getRequestToken( $Account->api['twitter']['callback'] );
					$_SESSION['twitter']['oauth_token'] = $token = $request_token['oauth_token'];
					$_SESSION['twitter']['oauth_token_secret'] = $request_token['oauth_token_secret'];
					
					if($connection->http_code == 200){
					  		echo'<div class="alert">Connecting to Twitter...</div>';
							$url = $connection->getAuthorizeURL($token);
							redirect($url); 
					}
					else {
						unset($_SESSION['twitter']);
						echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</div>';
					}
			}
			else if(isset($_SESSION['twitter'], $_SESSION['twitter']['oauth_token'], $_SESSION['twitter']['oauth_token_secret'], $_SESSION['twitter']['user_id'])) {
					
				$user_id = $_SESSION['twitter']['user_id'];

				if(!$Account->logged) {
				
					if ($Account->login(array('oauth' => 'twitter', 'uid' => $user_id, 'remember'=>TRUE))) {
						
						/* --- Added in v1.4.8 --- */
						$access_token = $_SESSION['twitter'];
						$connection = new TwitterOAuth($Account->api['twitter']['consumer_key'], $Account->api['twitter']['consumer_secret'],$access_token['oauth_token'], $access_token['oauth_token_secret']);
						$content = $connection->get('account/verify_credentials');
						$user = get_object_vars($content);
						$profile_image_url = str_replace('_normal', '', $user['profile_image_url']);
						$Account->update_meta($Account->session('user_id'), 'twitter_image_url', $profile_image_url);
						/* ---- */
						
						redirect('index.php');
					}
					else redirect('?page=tw_oauth');
					
				}
				else {
					$db = new Database();
					if ($db->select('usermeta', 'id', '(meta_key="twitter" and meta_value="'.$user_id.'" and user_id!="'.$Account->session('user_id').'")', null, 1)) {
						echo '<div class="alert alert-error">This Twitter account is already connected to another account. '.
							( (!$Account->logged) ? 'Use another <a href="?page=login">login</a> method.' : '' )
							.'</div>';
						unset($_SESSION['facebook']);
					}
					else {
						if ($Account->update_meta($Account->session('user_id'), 'twitter', $user_id) )
							redirect('index.php');
						else echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</div>';
					}
				}
			}
			else {
				echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</div>';
				unset($_SESSION['twitter']);
			}
		break;	

		case 'google':
			if (isset($_GET['error'])) {
				if($_GET['error'] == 'access_denied')
					echo '<div class="alert alert-error">You have denied permission to access your account. <a href="?page=oauth&method=google">Try again</a></div>';
				else echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</div>';
			}
			else {
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
				
				if (isset($_GET['code'])) {
					$client->authenticate();
					$_SESSION['google']['token'] = $client->getAccessToken();
					redirect('?page=oauth&method=google');
				}
				
				if (isset($_SESSION['google']['token']))
					$client->setAccessToken($_SESSION['google']['token']);
				if ($client->getAccessToken()) {
					try {
						$user = $plus->people->get('me');
						echo'<div class="alert">Connecting to Google+...</div>';
						$_SESSION['google']['user_id']= $user['id'];
						
						if(!$Account->logged) {
						
							if ($Account->login(array('oauth' => 'google', 'uid' => $user['id'],'remember'=>TRUE)))
								redirect('index.php');
							else redirect('?page=go_oauth');
							
						}
						else {
							$db = new Database();
							if ($db->select('usermeta', 'id', '(meta_key="google" and meta_value="'.$user['id'].'" and user_id!="'.$Account->session('user_id').'")', null, 1)) {
								echo '<div class="alert alert-error">This Google account is already connected to another account. '.
							( (!$Account->logged) ? 'Use another <a href="?page=login">login</a> method.' : '' )
							.'</div>';
								unset($_SESSION['facebook']);
							}
							else {
								if ($Account->update_meta($Account->session('user_id'), 'google', $user['id']) )
									redirect('index.php');
								else echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</div>';
							}
						}
						$_SESSION['google']['token'] = $client->getAccessToken();

					} catch (apiServiceException $e) {
						echo '<div class="alert alert-error">An unexpected error has occurred. Please try again.</div>';
						unset($_SESSION['google']);
						
					}
				}
				else {
					$url = $client->createAuthUrl();
					redirect($url);
				}
		}
		break;
		
		default:
			redirect('index.php');
		break;
	}
}
else echo '<div class="alert alert-error">Feature disabled.</div>';
?>