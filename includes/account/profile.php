<h4>Profile</h4>

<?php

//If the user is logged and the user id is not empty set $user_id to the id of the logged user
if ($Account->logged and empty($_GET['id']))
	$user_id = $Account->session('user_id');

//Else if the id is not empty set $user_id to that id 
else if ($_GET['id'] and is_numeric($_GET['id']) and $_GET['id']>0)
	$user_id = $_GET['id'];

//If we have and user_id call the get_user function to get our user data
if (!empty($user_id))
	$user = $Account->get_user($user_id);

//Again check to see if we have a user
if (!empty($user_id) and !empty($user)) 
{

	//Get some user meta using get_meta function
	$facebook = $Account->get_meta($user_id, 'facebook');
	$twitter = $Account->get_meta($user_id, 'twitter');
	$google = $Account->get_meta($user_id, 'google');
	$about = $Account->get_meta($user_id, 'about');

	?>

	<div class="profile">
		<!-- User profile image using get_avatar -->
		<div class="thumbnail pull-left">
			<img src="<?php echo $Account->get_avatar($user_id); ?>" width="200" height="200">
		</div>

		<div class="pull-left details">
			<p><i class="icon-user" title="Email"></i> 
				<strong><?php echo (!empty($user['display_name'])) ? $user['display_name'] : $user['username']; ?></strong>
			</p>

			<p> <i class="icon-envelope" title="Email"></i> <a href="mailto:<?php echo $user['email']; ?>"> <?php echo $user['email']; ?></a></p>
			
			<p> <i class="icon-time" title="Joined"></i> Joined <?php echo date('M j, Y', strtotime($user['registered'])); ?></p>	
			
			<?php
			//If user has completed the website field display it's website
			if(!empty($user['url']))
				echo '<p> <i class="icon-eye-open" title="Website"></i> <a href="'.$user['url'].'" target="_blank">'.$user['url'].'</a></p>';
			
			//If the Twitter meta is found display a lin to the profile
			if (!empty($twitter))
				echo '<p> <i class="icon-tw"></i> <a href="https://twitter.com/account/redirect_by_id?id='.$twitter.'" target="_blank">Twitter</a></p>';
			
			//If the Google meta is found display a lin to the profile
			if (!empty($google))
				echo '<p> <i class="icon-go"></i> <a href="https://profiles.google.com/'.$google.'" target="_blank">Google+</a></p>';
			
			//If the Facebook meta is found display a lin to the profile
			if (!empty($facebook))
				echo '<p> <i class="icon-fb"></i> <a href="http://www.facebook.com/profile.php?id='.$facebook.'" target="_blank">Facebook</a></p>';
			?>
		</div>
		<br clear="all"> <br>
		<?php

		//If the use has completed is about information display that
		if (!empty($about)) 
		{
			echo '<strong>About '.((!empty($user['first_name'])) ? $user['first_name'] : $user['username']).'</strong>';
			echo '<div class="about">'.$about.'</div>';
		}

		?>

	</div>

	<?php
}
//No user was found, display error
else echo '<div class="alert alert-error">Profile not found :(</div>';