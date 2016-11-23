<?php
/**
   * Account
   * 
   * @author  Tanvir Shaikat <ta.shaikat@gmail.com>
   */
session_start();

define('ACCOUNT_CONFIG_PATH', '../account_config.php');

require_once('functions.php');
require_once('Database.class.php');
require_once('Account.class.php');

$Account = new Account();
$msg=$error='';

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'usernamecheck':
			if (!empty($_GET['username']))
				if ( !$Account->username_check( $_GET['username'] ) )
					echo "true";
				else echo "false";
			else echo "false";
		break;
		case'emailcheck':
			if (!empty($_GET['email']))
				if ( !$Account->email_check( $_GET['email'] ) )
					echo "true";
				else echo "false";
			else echo "false";
		break;
	}
}

//If the acction is not specified set it to webcam
if (!isset($_POST['action']) and !isset($_GET['action']))
	$_POST['action'] = 'webcam';

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case'login':
			$data = array(
					'user'     => $_POST['user'],
					'password' => $_POST['password'],
					'remember' => (isset($_POST['remember'])) ? TRUE : FALSE
				);
			$Account->login($data);
			$errors = $Account->errors();
			if (!empty($errors))
				$error = $errors;
			else $msg = TRUE;
			echo json_encode(array('msg'=>$msg, 'error'=>$error));
		break;
		case'signup':
			$data = array(
				'username'         => $_POST['username'],
				'email'            => $_POST['email'],
				'password'         => $_POST['password'], 
				'confirm_password' => $_POST['confirm_password'],
				'captcha'          => $_POST['captcha']
			);
			$Account->signup($data);
			$errors = $Account->errors();
			if (!empty($errors))
				$error = $errors;
			else $msg = TRUE;
			echo json_encode(array('msg'=>$msg, 'error'=>$error));
		break;
		case'refreshcaptcha':
			$Account->captcha();
		break;
		case'resend':
			$Account->resend($_POST['email'], $_POST['captcha']);
			$errors = $Account->errors();
			if (!empty($errors))
				$error = $errors;
			else $msg = TRUE;
			echo json_encode(array('msg'=>$msg, 'error'=>$error));
		break;
		case'recover':
			$Account->recover($_POST['email'], $_POST['captcha']);
			$errors = $Account->errors();
			if (!empty($errors))
				$error = $errors;
			else $msg = TRUE;
			echo json_encode(array('msg'=>$msg, 'error'=>$error));
		break;
		case'changepass':
			$Account->change_password($_POST['password'], $_POST['confirm_password'], $_POST['key']);
			$errors = $Account->errors();
			if (!empty($errors))
				$error = $errors;
			else $msg = TRUE;
			echo json_encode(array('msg'=>$msg, 'error'=>$error));
		break;

		/*For avatar upload*/
		case 'upload':
			require_once("Avatar.class.php");
			require_once("SimpleImage.php");
			require_once("BMP.php");
			$Account = new Avatar();

			if (isset($_FILES['uploadimage']['tmp_name'])) {
				$upload = $Account->upload_avatar( $_FILES['uploadimage'] );
				if ($upload) 
					$msg = $upload;
				else {
					$errors = $Account->errors();
					if (!empty($Account->errors))
						$error = $errors['error'];
					else $error = 'error';
				}
			}
			echo json_encode(array('msg'=>$msg, 'error'=>$error));	
		break;
		case 'save_image':
			require_once("Avatar.class.php");
			$Account = new Avatar();
			//Check if the image session and cropping values are set
			if(isset($_SESSION['_tmp_image'], $_POST['x1'], $_POST['y1'], $_POST['w'], $_POST['h']))
			{
				$data = array(
					'x1' => $_POST['x1'],
					'y1' => $_POST['y1'],
					'width'  => $_POST['w'],
					'height'  => $_POST['h'],
				);
				$msg = $Account->save_image($data);
			}
			else $error = 'error';
			echo json_encode(array('msg'=>$msg, 'error'=>$error));
		break;
		case 'webcam':
			require_once("Avatar.class.php");
			$Account = new Avatar();
			$image_data = file_get_contents('php://input');
			if (!empty($image_data)) {
				$result = $Account->webcam_upload($image_data);
				if ($result)
					$msg = $result;
				else $error = 'error';
			}
			else $error = 'error';
			echo json_encode(array('msg'=>$msg, 'error'=>$error));				
		break;
		/* ------ */
	}
}
?>