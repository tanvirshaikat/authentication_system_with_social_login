<?php
	//Start the session
 	session_start();

 	//Define Account configuration path
 	define('ACCOUNT_CONFIG_PATH', '../account_config.php');

 	//Create a constant and check in the included files to see if are included from here or are accessed direct
	define('BASEPATH', TRUE);

 	//Require the php files
	require_once("../includes/functions.php");
	require_once("../includes/Database.class.php");
 	require_once("../includes/Account.class.php");
 	require_once("includes/Account_admin.class.php");

 	//Create the Account using Admin class (extends Account) object that will be used many times
	$Account = new Admin();

	//Add some extra options for language
	$Account->lang['email_add_subject'] = 'Your account was created';
	$Account->lang['email_add_message'] = 'An account for you was created. <br>Username: %s <br> Password: %s <br> Login here: %s';			

	//If is set the logout url call the logout function and refresh the page
	if (isset($_GET['logout'])) 
	{
 		$Account->logout();
		redirect('../index.php');
 	}

	//If user is not logged require login.php
	if (!$Account->logged) {
		require_once('login.php');
		die();
	}

	//Check user role
	$role = $Account->session('role');
	if ( empty($role) or $role != 'admin' ) {
		header('Location: ../');
		die();
	}

	//Get the current page from url
	$page = (!empty($_GET['page']))  ?  $_GET['page'] : '';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
	    <title>Account Admin </title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <meta name="description" content="">
	    <link rel="stylesheet" type="text/css" href="../assets/bootstrap/css/bootstrap.min.css" media="all">
		<link rel="stylesheet" type="text/css" href="../assets/bootstrap/css/bootstrap-responsive.min.css" media="all">
		<link rel="stylesheet" type="text/css" href="../assets/css/admin.css" media="all">
		<script type="text/javascript" src="../assets/js/jquery-1.8.1.min.js"></script>
		<script type="text/javascript" src="../assets/bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../assets/js/admin.js"></script>
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					    <span class="icon-bar"></span>
					    <span class="icon-bar"></span>
					    <span class="icon-bar"></span>
				  	</a>
				  	<a class="brand" href="../" target="_blank" title="Visit Site">&larr; Visit Site</a>
				  	<div class="nav-collapse collapse">
				    	<ul class="nav">
				      		<li><a href="index.php">Dashboard</a></li>
				      		<li><a onclick="Admin.compose();" style="cursor:pointer;">Compose Email</a></li>
				    	</ul>
				    	<!---<form class="navbar-search pull-left" action="">
				      		<input type="text" class="search-query span2" placeholder="Search">
				    	</form>-->
				    	<ul class="nav pull-right">
				        	<li class="dropdown">
				              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Logged as <?php echo $Account->session('display_name'); ?> <b class="caret"></b></a>
				              <ul class="dropdown-menu">
				                <li><a href="../?page=profile"> <i class="icon-user"></i> Profile</a></li>
				                <li><a href="../?page=settings"> <i class="icon-cog"></i> Settings</a></li>
				                <li><a href="?logout"> <i class="icon-off"></i> Logout</a></li>
				              </ul>
				            </li>
				    	</ul>
				  	</div>
				</div>
			</div>
		</div>
		<div class="container-fluid"> 
			<div class="row-fluid">
				<div id="sidebar">
			  	<div class="well sidebar-nav">
			    	<ul class="nav nav-list">

			    		<li class="submenu<?php echo (in_array($page, array('users','add_user','edit_user'))) ? ' active' : ''; ?>" data-id="users">
			    			<a href="?page=users"><i class="icon-user"></i> Users</a>
			    			<ul class="nav nav-list">
			    				<li<?php echo ( in_array($page, array('users','edit_user'))) ? ' class="subactive"' : ''; ?>><a href="?page=users"> Users</a></li>
			    				<li<?php echo ( $page=='add_user') ? ' class="subactive"' : ''; ?>><a href="?page=add_user"> Add New</a></li>
			    			</ul>
			    		</li>
			    		<li><a href="#"><i class="icon-comment"></i> Other menu</a></li>

			    	</ul>
			  	</div>
			</div>
		<!-- Hidden Modal for sendig emails -->
		<div id="composeModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="message" aria-hidden="true">
		  <div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		    <h3>Compose Email</h3>
		  </div>
		  <style type="text/css"> #subject, #to, #message {width: 515px;} </style>
		  <div class="modal-body">
		    <input type="text" id="to" placeholder="To"><br>
		    <input type="text" name="subject" id="subject" placeholder="Subject"><br>
		    <textarea id="message" style="height: 150px;" placeholder="Message"></textarea><br>
		    <em>You can add multiple emails separated with semicolon (;)</em>
		  </div>
		  <div class="modal-footer">
		    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		    <button class="btn btn-primary" onclick="Admin.send_email();">Send</button>
		  </div>
		</div>

		<div id="content">
			<?php

			//Switch by the page and require the corresponding file
			switch ($page) 
			{
				case '': default:
					?>
					<h1 class="title">Dashboard</h1>
					<?php
				break;

				case 'users':
					require_once('users.php');
				break;

				case 'add_user':
					require_once('add_user.php');
				break;

				case 'edit_user':
					require_once('edit_user.php');
				break;

				case 'delete_user':
					//Check if data was send
					if (isset($_POST['user_id']) and is_numeric($_POST['user_id']) and $_POST['user_id']>0) {
						//Call delete_user function
						$Account->delete_user($_POST['user_id']);
					}
				break;

				case 'send_email':
					require_once('send_email.php');
				break; 
			}
			?>
			<hr>
			<p>&copy; YourWebsite 2013</p>
		</div>
	</body>
</html>