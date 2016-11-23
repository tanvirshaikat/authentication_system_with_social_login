<?php

/**
 * Use javascript to redirect to another url
 *
 * @access  public
 * @param   string
 */

function redirect ( $str='' )
{
	echo'<script>window.location=("';
	if($str=='')
		echo 'http' . ((!empty($_SERVER['HTTPS'])) ? 's' : '') . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	else echo $str; 
	echo'");</script>';
}

/**
 * Signup callback, called when user has created new account
 *
 * @access  public
 * @param   integer
 * @param   string
 */

function account_signup_callback ( $user_id , $oauth = FALSE)
{
	$Account = new Account();
	
	//Add default gravatar image
	$avatar = ($oauth) ? $oauth : 'gravatar';
	$Account->update_meta($user_id, 'avatar', $avatar);

	//Asing a default role of user
	$Account->update_meta($user_id, 'role', 'user');
}

/**
 * Login callback, called when user has logged in
 *
 * @access  public
 * @param   array
 */

function account_login_callback ( $data )
{
	$Account = new Account();

	if (!empty($data['id']))
		$role = $Account->get_meta($data['id'], 'role');
	if (!empty($role))
		$_SESSION['_account']['role'] = $role;
}

/**
 * Recover password callback, called when user request's password recover link
 *
 * @access  public
 * @param   string
 */

function account_recover_callback ( $email )
{

}

/**
 * Change password callback, called when user has changed password
 *
 * @access  public
 * @param   integer
 */

function account_change_password_callback ( $user_id )
{

}

/**
 * Resend activation email callback, called when user request's activation email
 *
 * @access  public
 * @param   integer
 */

function account_resend_activation_callback ( $user_id )
{

}

/**
 * Activate account callback, called when user has activated his account
 *
 * @access  public
 * @param   integer
 */

function account_activate_callback ( $user_id )
{

}
?>