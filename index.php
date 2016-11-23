<?php
  /**
   * Account
   * 
   * @author  Tanvir Shaikat <ta.shaikat@gmail.com>
   */
	//Start the session
 	session_start();

 	//Define Account configuration path
 	define('ACCOUNT_CONFIG_PATH', 'account_config.php');

 	//Require the php files
	require_once("includes/functions.php");
	require_once("includes/Database.class.php");
 	require_once("includes/Account.class.php");

 	//Create the Account object that will be used many times
	$Account = new Account();

	//If is set the logout url call the logout function and refresh the page
	if (isset($_GET['logout'])) 
	{
 		$Account->logout();
		redirect('index.php');
 	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>Login System</title>
	<!-- Include the Style -->
	<link rel="stylesheet" type="text/css" href="assets/css/account.css">
	<link rel="stylesheet" type="text/css" href="assets/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="assets/bootstrap/css/bootstrap-responsive.min.css">
	<!-- Include the JavaScript -->
	<script src="assets/js/jquery-1.8.1.min.js"></script>
	<script src="assets/bootstrap/js/bootstrap.min.js"></script>
	<script src="assets/js/jquery.validate.min.js"></script>
	<script src="assets/js/account.js?v=1.4.1"></script>
</head>
<body>

	<!-- BEGIN CONTAINER -->
	<div class="container" id="container">
		
		<!-- BEGIN HEADER -->
		<div id="header">
			<h3 class="title pull-left"><a href="<?php echo $Account->config('base_url'); ?>">Login System</a></h3>
			<?php if ( $Account->logged ) { ?>
				<div class="btn-group pull-right logged">
					
					<?php if ($Account->session('role') == 'admin') { ?>
						<div class="btn-group">
					    	<a href="admin/" class="btn btn-small"><i class="icon-wrench"></i> Admin</a>
					    	<button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
								<i class="icon-user"></i> <?php echo $Account->session('display_name'); ?> <span class="caret"></span>
							</button>
						  	<ul class="dropdown-menu">
						    	<li><a href="?page=profile"><i class="icon-eye-open"></i> Profile</a></li>
						    	<li><a href="?page=settings"><i class="icon-cog"></i> Settings</a></li>
						    	<li class="divider"></li>
								<li><a href="?logout"><i class="icon-off"></i> Logout</a></li>
						 	</ul>
						 </div>
				    <?php } else { ?>
				    <button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
						<i class="icon-user"></i> <?php echo $Account->session('display_name'); ?> <span class="caret"></span>
					</button>
				    <ul class="dropdown-menu">
				    	<li><a href="?page=profile"><i class="icon-eye-open"></i> Profile</a></li>
				    	<li><a href="?page=settings"><i class="icon-cog"></i> Settings</a></li>
				    	<li class="divider"></li>
						<li><a href="?logout"><i class="icon-off"></i> Logout</a></li>
				 	</ul>
				    <?php } ?>
				    
				</div>
			<?php } else { ?>
				<div class="pull-right" style="margin-top:5px;">
					<a href="?page=login">Login</a> |
					<a href="?page=signup">Sign Up</a>
				</div>
			<?php } ?>
		</div>
		<!-- end div #header -->

		<!-- BEGIN CONTENT -->
		<div id="content">
		<?php 

		//Get the current page from url
		$page = (!empty($_GET['page']))  ?  $_GET['page'] : '';
		//Switch by the page and require the corresponding file
		switch ($page) 
		{
			case '': default:
				//Remove this with your own home text
				?>
				<p>Welcome to Login System!</p>
				<p>HazzardWeb offers a complete user management script with login, register, social authentificaion, recover password, activation email form and Admin Panel. Also each user have a profile and can edit his profile settings. All forms are validated both with JavaScript and PHP.</p>
				<?php if ( !$Account->logged ) { ?>
					<h3 style="text-align: center;"><a href="?page=login">Login</a> or 
					<a href="?page=signup">Sign Up</a></h3>
				<?php } ?>
				<img src="assets/images/big.png">
				<?php
				//-----
			break;
			
			case'login':
				require_once('includes/account/login.php');
			break;

			case'signup':
				require_once('includes/account/signup.php');
			break;

			case'recover':
				require_once('includes/account/recover.php');
			break;
			case 'changepass':
				require_once('includes/account/changepass.php');
			break;
			case'resend':
				require_once('includes/account/resend.php');
			break;

			case'activate':
				require_once('includes/account/activate.php');
			break;

			case'profile':
				require_once('includes/account/profile.php');
			break;

			case'settings':
				require_once('includes/account/settings.php');
			break;

			case'oauth': case 'account':
				require_once('includes/account/oauth.php');
			break;

			case'fb_oauth':
				require_once('includes/account/fb_oauth.php');
			break;

			case 'tw_oauth':
				require_once('includes/account/tw_oauth.php');
			break;
			
			case'go_oauth':
				require_once('includes/account/go_oauth.php');
			break;
		}
		?>
		</div>
		<!-- end div #content -->
	</div>
	<!-- end div #container -->

	<!-- BEGIN FOOTER -->
	<p id="footer">
		<a href="http://hazzardweb.net/login-system" target="_blank">Login System 1.0</a>
	</p>
	<!-- end div #footer -->
</body>
</html>