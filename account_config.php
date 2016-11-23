<?php
  /**
   * Account
   * 
   * @author  Tanvir Shaikat <ta.shaikat@gmail.com>
   */
/* General configuration */

//Your website base url with a slash at the end (eg. http://yourwebsite.com/)
$config['base_url'] = '';

//The email address that will send emails
$config['email'] = '';

//The name that will be used in emails 
$config['name'] ='Shaikat Authentication';

//Disable PHPMailer and use mail() function
//$config['PHPMailer'] = FALSE;

//The path to default avatar
$config['avatar'] = $config['base_url'] . 'assets/images/avatar.jpg';

//Avatar uploader config

//Where images should be uploaded
$config['upload_path'] = 'uploads/';

//Allowed extensions
$config['allowed_extensions'] = 'jpg|png|jpeg|gif|bmp';

//The maximum width for the image when is saved to temporarily dir
$config['image_max_width'] = 600;

//The maximum height for the image when is saved to temporarily dir
$config['image_max_height'] = 400;

//The crop size of the image
$config['image_crop_size'] = 250;

//The file size limit in MB
$config['file_size_limit'] = 5;

//Script base path, do not edit
$config['base_path'] = dirname(__FILE__);

/* Database connection details. If your website is allready connected you do not need to specify these variables */
$database = array(
		'pass' => '',
		'user' => '',
		'name' => '',
		'host' => ''
	);

/* Twitter API for oAuth Login | Visit https://dev.twitter.com/ to get your api */
$api['twitter'] = array(
			'consumer_key'    => '',
			'consumer_secret' => '',
			'callback'        => $config['base_url'] . '?page=oauth&method=twitter&cb'  
		);

/* Facebook API for oAuth Login | Visit https://developers.facebook.com/ to get your api */
$api['facebook'] = array(
			'app_id'  => '',
 		 	'secret'  => ''
		);

/* Google API for oAuth Login Visit https://code.google.com/apis/console/ to get your api */
$api['google'] = array(
			'client_id'     => '',
			'client_secret' => '',
			'api_key'       => '',
			'redirect_uri'  =>  $config['base_url'] . '?page=oauth&method=google'   
		);

/* Gmail API to send emails with a gmail account | Visit http://gmail.com/ to create a gmail account  */
$api['gmail'] = array(
			'username' => '',
			'password' => ''
		);

/*
	Links that are used only in the Account.class.php
	If you want to customize your website account links change these links
*/

$links = array(
		'resend'   => $config['base_url'] . '?page=resend',
		'login'    => $config['base_url'] . '?page=login',
		'forgot'   => $config['base_url'] . '?page=recover',
		'activate' => $config['base_url'] . '?page=activate&key=',
		'recover'  => $config['base_url'] . '?page=changepass&key=',
		'register' => $config['base_url'] . '?page=signup',
	);
		
/*
	Language configuration
	This is also used only by the Account.class.php
	%s means that will be replaced with some html or a link
*/
$lang = array(
		'empty_user' => 'The User field is required.',
		'empty_pass' => 'The Password field is required.',
		'empty_username' => 'The Username field is required.',
		'empty_email' =>'The Email field is required.',
		'empty_key' => 'Invalid activation link',
		'valid_username'=>'The Username field may only contain alpha-numeric characters.',
		'valid_email'=>'The Email field must contain a valid email address.',
		'valid_fname'=>'The First Name field may only contain alphabetical characters.',
		'valid_lname'=>'The Last Name field may only contain alphabetical characters.',
		'valid_dname'=>'The Display Name field may only contain alphabetical characters.',
		'valid_url' =>'The Website field is not a valid url.',
		'account_banned'=> "You can't login because your account has been banned.",
		'not_activated'=>'Your account is not activated. Check your inbox/spam or <a href="%s">request</a> a new activation email.',
		'user_not_found'=>'Invalid User or Password.',
		'pass_to_short'=>'The Password field must be at least 5 characters in length.',
		'pass_not_match'=>'The Repeat Password does not match the Password field.',
		'captcha_error'=>'Invalid Captcha.',
		'username_exists'=>'This username is already taken.',
		'email_exists'=>'This email is already registered.',
		'error'=>'Unexpected Error',
		'email_not_found'=>'No account found with this email address.',
		'recover_not_found'=>'Invalid recover link. Send a new <a href="%s">one</a>.',
		'already_activated'=>'Your account is already activated. You can <a href="%s">login</a>.',
		'activate_banned'=>"You can't activate an account that is banned.",
		'activation_not_found'=>'This activation link is not valid. Please <a href="%s">request</a> a new activation email.',
		'email_reg_message'=>'Your Username is %s <br> Please confirm your account by clicking this link:<br> %s',
		'email_reg_subject'=>'Confirm your account',
		'email_rec_message'=>'You if you have requested to change your password click the link below, otherwise ignore this email.<br> %s',
		'email_rec_subject'=>'Recover Password',
		'email_act_message'=>'Please confirm your account by clicking this link:<br> %s',
		'email_act_subject'=>'Activate your account',
	);
?>