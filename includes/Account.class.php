<?php
  /**
   * Account
   * 
   * @author  Tanvir Shaikat <ta.shaikat@gmail.com>
   */

Class Account {
	public $logged  = FALSE;
	public $errors  = array();
	public static $config  = array();
	public $api     = array();
	public $links   = array();
	public $lang    = array();
	public $pattern = array(
			'username' => '/^[a-zA-Z0-9]+[a-zA-Z0-9\_\.]+[a-zA-Z0-9]+$/i',
			'email'    => '/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i',
			'alpha'    => '/^[A-Za-z ]+$/i',
			'url'      => '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i',
			'dname'    => '/^[a-zA-Z0-9 ]+[a-zA-Z0-9\_\. ]+[a-zA-Z0-9 ]+$/i',
		);
	
	public function __construct()
	{	
		//Check for session and start
		if (!isset($_SESSION))
			session_start();

		//Require the config
		require_once(ACCOUNT_CONFIG_PATH);

		//Load vars from config
		if (!empty($api))
			$this->api    = $api;
		if (!empty($links))
			$this->links  = $links;
		if (!empty($lang))
			$this->lang   = $lang;
		if (!empty($config))
			self::$config = $config;

		//Connect if the user isset
		$db = new Database();
		if ( !empty($database['user']) ) 
			$db->connect( $database );

		//This will help for some cookies.
		ob_start();

		//Check if user is loogged or not
		if ( $this->session('logged') )
			$this->logged = TRUE;

		//Check for cookies and login if cookies were found
		if (!$this->logged) {
			
			if( !empty($_COOKIE['_account_user']) and !empty($_COOKIE['_account_pass'])) {
				$data = array(
						'user'     => $_COOKIE['_account_user'],
						'password' => $_COOKIE['_account_pass'],
						'encrypt'  => FALSE
					);
				if ($this->login($data)) 
					redirect($_SERVER['PHP_SELF']);
				else {
					$this->set_cookie('_account_user', '');
					$this->set_cookie('_account_pass', '');
				}
			}

			else if (!empty($_COOKIE['_account_oauth'])) {

				$cookie = $this->xss( $_COOKIE['_account_oauth'] );
				$cookie = explode('|', $cookie);

				if (!empty($cookie[0]) and !empty($cookie[1])) {
					if ($this->login( array( 'oauth' => $cookie[0], 'uid' => $cookie[1])))
						redirect($_SERVER['PHP_SELF']);
					else $this->set_cookie('_account_oauth', '');
				}
				else $this->set_cookie('_account_oauth', '');
			}

		}
	}

	/**
	 * Login
	 *
	 * @access  public
	 * @param   array
	 * @return  true or false
	 */

	public function login($data)
	{
		//Set some default variables
		$encrypt = TRUE;
		$remember = FALSE;

		//Extract the array data
		extract($data);

		//If there's no rows specified set to default rows
		if (empty($rows))
			$rows = "id, display_name, username, status";

		//We need to check if the password and username are ok only if oauth in not used
		if (empty($oauth)) {
	
			//Check if username is empty and set an error if so
			if (empty($user))
				$this->set_error('empty_user', 'username');

			//If the password is empty set an error
			if (empty($password))
				$this->set_error('empty_pass', 'password');

			//This will encrypt the password, if in $data array is set encrypt to FALSE the password will not be encrypted
			if ($encrypt)
				$password = $this->encrypt($password);
		}

		//If we don't have errors continue
		if (empty($this->errors)) {
			
			$db = new Database();
			
			//When someone's login we need to check if the that user exists
			//If the user logs in with an oAuth method we check if we have that id
			if (!empty($uid)) {
				if ($db->select('usermeta', 'user_id', 'meta_key="'.$oauth.'" AND meta_value="'.$uid.'"', null, 1)) {
					$result = $db->getResult();
					$where = 'id="'.$result[1]['user_id'].'"';
				}
				else {
					//No user found with that id set error and retrun false
					$this->set_error('error', '_error');
					return FALSE;
				}
			}
			//If the login is with username/password and password check if exists
			else $where = '(username = "'. $user .'" OR email = "'.$user.'") AND password = "'.$password.'"';

			//Create a select query
			$query = $db->select('users', $rows, $where, null, 1);
			//Check if user was found
			if ($query) {
				//Get the user data
				$data = $db->getResult();

				//Set the session only if user status is 1 (account acivated)
				if ($data[1]['status']==1) {
					
					//Set the session, you can add more vars here
					$_SESSION['_account'] = array(
							'logged'       => TRUE,
							'user_id'      => $data[1]['id'],
							'display_name' => (!empty($data[1]['display_name'])) ? $data[1]['display_name'] : $data[1]['username'],
						);
					//If the user has checked the remember box then set a cookie
					if ($remember) {
						//Cookie for normal login (a cookie for username(or password) and a cookie for password (the password is encrypted)
						if (empty($oauth)) {
							$this->set_cookie('_account_user', $user);
							$this->set_cookie('_account_pass', $password);
						}
						//Cookie for oauth method, concatenate oauth method and the id (id si encrypted)
						else $this->set_cookie('_account_oauth', $oauth.'|'.$this->encrypt($uid));
					}
					
					//Login callback. If the account_login_callback is exists will be called with $data param
					if (function_exists('account_login_callback'))
						account_login_callback($data[1]);

					//Everything's ok so return true
					return TRUE;
				}
				
				//If the account status is 2 (banned) set an error
				else if ($data[1]['status'] == 2) 
					$this->set_error('account_banned', '_error');

				//Else the account is not activated so set an error
				else $this->set_error( sprintf($this->lang('not_activated'), $this->links['resend']) , '_error', FALSE);
			}
			//No user was found, set error 
			else $this->set_error('user_not_found', '_error');
		}
		//If something was wrong retrun false
		return FALSE;
	}

	/**
	 * Signup - register a new user
	 *
	 * @access  public
	 * @param   array
	 * @return  true or false
	 */

	public function signup($data)
	{
		//Set some default variables	
		$status = 1;
		$password = '';

		//Extract the array data
		extract($data);

		//Check if username is empty and set an error if so
		if (empty($username))
			$this->set_error('empty_username', 'username');
		//Else if the username is not empty then check if it's matching the pattern
		else if (!preg_match($this->pattern['username'], $username)) 
			$this->set_error('valid_username', 'username');
		
		//Check if email is empty
		if (empty($email))
			$this->set_error('empty_email', 'email');
		//Else check if it's matching the pattern
		else if (!preg_match($this->pattern['email'], $email)) 
			$this->set_error('valid_email', 'email'); 

		//We need to check for password and captcha only if the $oauth is empty
		if (empty($oauth)) {

			//If the password is empty set an error
			if (empty($password))
				$this->set_error('empty_pass', 'password');
			//Password must be minim 5 characters
			else if (strlen($password)<5)
				$this->set_error('pass_to_short', 'password');
			//Check if password match
			else if ($confirm_password != $password)
				$this->set_error('pass_not_match', 'confirm_password');
			//Check if the captcha session is the same with the captcha that is submited by user
			if (empty($_SESSION['_account_captcha']) or $captcha != $_SESSION['_account_captcha'])
				$this->set_error('captcha_error' , 'captcha');

			//Encrypt the password and set status to 0
			$password = $this->encrypt($password);
			$status = 0;
		}

		//No errors so continue
		if ( empty($this->errors) ) {
			$db = new Database();
			//New db query, check if someone is already registered with the email/username
			$query = $db->select('users', 'username, email', '(username = "'. $username .'" OR email = "'.$email.'")', null, 1);
			//If so then set some errors
			if ($query) {
				$data = $db->getResult();
				//Set error if the username already exists
				if ($username == $data[1]['username'])
					$this->set_error('username_exists', 'username');
				//Set error if the email exists
				else $this->set_error('email_exists', 'email');
			}
			//Else add the new user in database
			else {
				//Create an activation key
				$activation_key = substr( md5( time().$username ), 0, 20);

				//Insert query
				$db = new Database();
				if ( $db->insert('users', 'username, email, password, activation_key, status',  
					array( $username, $email, $password, $activation_key, $status)) ) {
					//Get the id of the user that we've just created
					$user_id = mysql_insert_id();
					
					//If the signup is not with an Oauth method we need email the user with the activation key
					if (empty($oauth)) {
						//sprintf will replace the first string with the next parameters, here will insert a link 
						$message = sprintf($this->lang('email_reg_message'), $username, '<a href="'.$this->links['activate'].$activation_key.'">'.$this->links['activate'].$activation_key.'</a>');
						//Send the email
						$this->send_email($email, $this->lang('email_reg_subject'), $message);
					} 
					else {
						//Else if the signup is with an oauth method set a meta (usermeta table)
						$this->add_meta($user_id, $oauth, $uid);
					}
					//Signup callback
					if (empty($oauth)) $oauth = FALSE;
					if (function_exists('account_signup_callback'))
						account_signup_callback($user_id, $oauth);

					return TRUE;
				}
				//Something went wron, set error
				else $this->set_error('error', '_error');
			}
		}
		return FALSE;
	}

	/**
	 * Sends an email with a recover link
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @return  true or false
	 */

	public function recover($email, $captcha)
	{
		//Check if email is not set or is not matching the pattern
		if (empty($email))
			$this->set_error('empty_email', 'email');
		else if (!preg_match($this->pattern['email'], $email)) 
			$this->set_error('valid_email', 'email');

		//Check the captcha
		if (empty($_SESSION['_account_captcha']) or $captcha != $_SESSION['_account_captcha'])
			$this->set_error('captcha_error', 'captcha');

		if ( empty($this->errors) ) {
			$db = new Database();
			$query = $db->select('users', 'id', '(email = "'.$email.'")', null, 1);
			//If the user was found send email
			if ($query) {
				//Create a recover key
				$recover_key = substr( md5( time().$email ), 0, 20);
				
				//Update the user key and send a email with that recover link 
				$db = new Database();
				if ($db->update('users', array('activation_key' => $recover_key), 'email="'.$email.'"', 1) ) {
					
					$message = sprintf($this->lang('email_rec_message'), '<a href="'.$this->links['recover'].$recover_key.'">'.$this->links['recover'].$recover_key.'</a>');

					$this->send_email($email, $this->lang('email_rec_subject'), $message);
					$this->unset_value();
					
					//Callback 
					if (function_exists('account_recover_callback'))
						account_recover_callback($email);

					return TRUE;

				}
				else $this->set_error('error', '_error');
			}
			//If no user was found with that email set a error
			else $this->set_error('email_not_found', 'email');
		}
		return FALSE;
	}

	/**
	 * Check if the recover_key is valid
	 *
	 * @access  public
	 * @param   string
	 * @return  true or false
	 */

	public function reset_password($recover_key) {
		if (!empty($recover_key)) {
			$db = new Database();
			//Select query, if the key is valid retrun true
			$query = $db->select('users', 'id', '(activation_key = "'.$this->xss($recover_key).'")', null, 1);
			if ($query) 
				return TRUE;
			//Else set an error
			else $this->set_error( sprintf( $this->lang('recover_not_found'), $this->links['forgot']), '_error', FALSE);
		}
		return FALSE;
	}

	/**
	 * Changes the user password
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @param   string
	 * @return  true or false
	 */

	public function change_password($password, $confirm_password, $recover_key){
		
		//Check passwords
		if (empty($password))
			$this->set_error('empty_pass', 'password');
		else if (strlen($password)<5)
			$this->set_error('pass_to_short', 'password');
		else if ($confirm_password != $password)
			$this->set_error('pass_not_match', 'confirm_password');
		
		if ( empty($this->errors) ) {
			$db = new Database();
			//Select the user
			$query = $db->select('users', 'id', '(activation_key = "'.$this->xss($recover_key).'")', null, 1);
			if ($query) {
				$data = $db->getResult();
				//Update the password
				$db->update('users', array('password' => $this->encrypt( $password ), 'activation_key'=>time()), 'id="'.$data[1]['id'].'"', 1);

				//Callback
				if (function_exists('account_change_password_callback'))
					account_change_password_callback($data[1]['id']);
				
				//Unset posts values
				$this->unset_value();
				return TRUE;
			}
			else $this->set_error('error', '_error');
		}
		return FALSE;
	}
	
	/**
	 * Sends a new activation email
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @return  true or false
	 */

	public function resend($email, $captcha) {
		//Check the email
		if (empty($email))
			$this->set_error('empty_email', 'email');
		else if (!preg_match($this->pattern['email'], $email)) 
			$this->set_error('valid_email', 'email');
		//and captcha
		if (empty($_SESSION['_account_captcha']) or $captcha != $_SESSION['_account_captcha'])
			$this->set_error('captcha_error', 'captcha');

		if ( empty($this->errors) ) {
			$db = new Database();
			//Select the user status
			$query = $db->select('users', 'id, status', '(email = "'.$email.'")', null, 1);
			if ($query) {
				$result = $db->getResult();
				//If status = 1 then no need to send activation email, set an error
				if ($result[1]['status'] == 1)
					$this->set_error( sprintf( $this->lang('already_activated'), $this->links['login']), '_error', FALSE);
				//If the status = 2 then we can't send the acivation email because the account is banned
				else if ($result[1]['status'] == 2)
					$this->set_error('activate_banned', '_error');
				//Else send the activation email
				else {
					//Create a recover key
					$recover_key = substr( md5( time().$email ), 0, 20);
								
					$db = new Database();
					//Update the recover key in database and send the email
					if ($db->update('users', array('activation_key' => $recover_key), 'email="'.$email.'"', 1) ) {
						
						$message = sprintf($this->lang('email_act_message'),
							'<a href="'.$this->links['activate'].$recover_key.'">'.$this->links['activate'].$recover_key.'</a>');
						$this->send_email($email, $this->lang('email_act_subject'), $message);
						$this->unset_value();
						
						//Callback
						if (function_exists('account_resend_activation_callback'))
							account_resend_activation_callback($result[1]['id']);
	
						return TRUE;
	
					}
					else $this->set_error('error', '_error');
			}
			}
			else $this->set_error('email_not_found', 'email');
		}
		return FALSE;
	}

	/**
	 * Activates the account
	 *
	 * @access  public
	 * @param   string
	 * @return  true or false
	 */

	public function activate ($activation_key) {
		if (empty($activation_key))
			$this->set_error('empty_key', 'key');

		if ( empty($this->errors) ) {
			//Select query with the activation key
			$db = new Database();
			$query = $db->select('users', 'id, status', '(activation_key = "'.$this->xss($activation_key).'")', null, 1);
			if ($query) {
				$data = $db->getResult();
				//If the account is already activated set an error
				if ($data[1]['status']==1)
					$this->set_error( sprintf( $this->lang('already_activated'), $this->links['login']), '_error', FALSE);
				//If the status = 2 the account is banned => set error
				else if ($data[1]['status']==2)
					$this->set_error('activate_banned', '_error');
				else {
					//Else update the user, set status = 1
					$db->update('users', array('status' => 1), 'id="'.$data[1]['id'].'"', 1);

					if (function_exists('account_activate_callback'))
						account_activate_callback( $data[1]['id'] );

					return TRUE;
				}
			}
			else $this->set_error( sprintf( $this->lang('activation_not_found'), $this->links['resend']), '_error', FALSE);
		}
		return FALSE;
	}
	
	/**
	 * Check if the username exists
	 *
	 * @access  public
	 * @param   string
	 * @return  true or false
	 */

	public function username_check($username) {
		//Check if the user exists and return true or false
		$db = new Database;
		if ($db->select('users', 'id', 'username="'.$this->xss( $username ).'"'))
			return TRUE;
		else return FALSE;
	}

	/**
	 * Check if the email exists
	 *
	 * @access  public
	 * @param   string
	 * @return  true or false
	 */

	public function email_check($email) {
		//Check if the email exists and return true or false
		$db = new Database;
		if ($db->select('users', 'id', 'email="'.$this->xss( $email ).'"'))
			return TRUE;
		else return FALSE;
	}
	
	/**
	 * Initialize captcha session
	 *
	 * @access  public
	 */

	public function captcha()
	{	
		//Create 2 captcha sessions, one that is used by the captcha.php and one for checking 
		$_SESSION['_account_captcha'] = substr(md5(time()), 0, 4);
		$_SESSION['_account_captcha_init'] = $_SESSION['_account_captcha'];
	}

	/**
	 * Save the user settings
	 *
	 * @access  public
	 * @param   array
	 * @return  true
	 */

	public function save_settings($data)
	{	
		$user_id = $this->session('user_id');
		//Extract the array data
		extract($data);

		//Set some default variables
		$update = array();
		$db = new Database;

		//Do some checks and set errors
		if (isset($username)) {
			if (empty($username))
				$this->set_error('empty_username', 'username');
			else if (!preg_match($this->pattern['username'], $username)) 
				$this->set_error('valid_username', 'username');
			else if ($query = $db->select('users', 'id', '(username = "'. $username .'" AND id != "'.$user_id.'")', null, 1))
				$this->set_error('username_exists', 'username');
			else $update['username'] = $username;
		}
		if (isset($email)) {
			if (empty($email))
				$this->set_error('empty_email', 'email');
			else if (!preg_match($this->pattern['email'], $email)) 
				$this->set_error('valid_email', 'email');
			else if ($query = $db->select('users', 'id', '(email = "'. $email .'" AND id != "'.$user_id.'")', null, 1))
				$this->set_error('email_exists', 'email');
			else $update['email'] = $email;
		}
		if (isset($first_name)) {
			if (!empty($first_name) and !preg_match($this->pattern['alpha'], $first_name)) 
				$this->set_error('valid_fname', 'first_name');
			else $update['first_name'] = $first_name;
 		}
 		if (isset($last_name)) {
			if (!empty($last_name) and !preg_match($this->pattern['alpha'], $last_name)) 
				$this->set_error('valid_lname', 'last_name');
			else $update['last_name'] = $last_name;
 		}
 		if (isset($display_name)) {
			if (!empty($display_name) and !preg_match($this->pattern['dname'], $display_name)) 
				$this->set_error('valid_dname', 'display_name');
			$update['display_name'] = $display_name;
 		}
 		if (isset($url)) {
			if (!empty($url) and !preg_match($this->pattern['url'], $url)) 
				$this->set_error('valid_url', 'url');
			else $update['url'] = $url;
 		}
 		if (isset($password)) {
 			if (empty($password))
				$this->set_error('empty_pass', 'password');
			else if (strlen($password)<5)
				$this->set_error('pass_to_short', 'password');
			else if ($confirm_password != $password)
				$this->set_error('pass_not_match', 'confirm_password');
			else $update['password'] = $this->encrypt($password);
 		}
 		if (isset($status)) {
 			$update['status'] = $status;
 		}
		//Update the user
 		if (!empty($update))
			$db->update('users', $update, 'id="'.$user_id.'"', 1);

		//Update usermeta
		if (!empty($usermeta))
			foreach ($usermeta as $key => $value)
				$this->update_meta($user_id, $key, $value);

		return TRUE;
	}

	/**
	 * Get the user avatar
	 *
	 * @access  public
	 * @param   integer
	 * @return  string
	 */

	public function get_avatar($user_id = FALSE) {

		//If user_id is not set then check if the user is logged and get user_id
		if (empty($user_id) and $this->logged) {
			$user_id = $this->session('user_id');
		}
		//Get the avatar meta
		$avatar = $this->get_meta($user_id, 'avatar');
		
		//If the avatar is facebook. google or twitter wen need the id
		if (in_array($avatar, array('facebook', 'google', 'twitter', 'uploaded')))
			$id = $this->get_meta($user_id, $avatar);
		//Else we need the email for gravatar api
		else if ($avatar == 'gravatar') {
			$db = new Database;
			if ($db->select('users', 'email', 'id="'.$user_id.'"', null, 1) ) {
				$result = $db->getResult();
				$id = $result[1]['email'];
			}
		}
		//If we have the id then switch and get the avatar
		if (!empty($id)) {
			switch ($avatar) {
				case'twitter':
					//Get Twitter profile image (API 1.1)
					$twitter_profile_image = $this->get_meta($user_id, 'twitter_image_url');
					if (!empty($twitter_profile_image))
						return $twitter_profile_image;
					//API 1.1 deprecated
					//return 'http://api.twitter.com/1/users/profile_image?id='.$id.'&size=original';

				break;
				case'facebook':
					//Get Facebook profile image
					return 'https://graph.facebook.com/'.$id.'/picture?width=200&height=200';
				break;
				case'google':
					//Get Google profile image
					 return 'https://www.google.com/s2/photos/profile/'.$id;
				break;
				case'gravatar':
					//Get Gravatar avatar
					return "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $id ) ) ) . "?d=" . urlencode(self::$config['avatar']) . "&s=200";
				break;
				case'uploaded':
					//create filename from substract of md5 of the user id with .ext at the end
					$filename = substr( md5( $user_id ) , 0, 10) . '.' . $id;
					return self::$config['base_url'] . self::$config['upload_path'] . 'avatars/' . $filename;
				break;
			}
		}
		//If no avatar method was found return the default avatar image
		return self::$config['avatar'];
	}

	/**
	 * Get the user data
	 *
	 * @access  public
	 * @param   integer
	 * @param   string
	 * @param   string
	 * @return  array or false
	 */

	public function get_user($user_id, $rows='*', $where = FALSE) {
		$where = ($where) ? $where : 'id="'.$user_id.'"';
		//Select $rows from users table
		$db = new Database;
		if ($db->select('users', $rows, $where, null, 1)) {
			$result = $db->getResult();
			return $result[1];
		}
		return FALSE;
	}

	/**
	 * Get the user meta
	 *
	 * @access  public
	 * @param   integer
	 * @param   string
	 * @param   string
	 * @return  array / string / false
	 */

	public function get_meta($user_id, $meta_key = FALSE) {
		//Get user meta from usermeta table
		$db = new Database;
		//If the meta_key is set return that meta key value
		if ($meta_key) {
			$where  = 'user_id="'.$user_id.'"  AND meta_key="'.$meta_key.'"';
			if ( $db->select('usermeta', 'meta_value', $where, null, 1) ) {
				$result = $db->getResult();
				return $result[1]['meta_value'];
			}
		//Else return all meta for that user
		} else if ( $db->select('usermeta', 'id, meta_key, meta_value', 'user_id="'.$user_id.'"') ) {
			$result = $db->getResult();
			return $result;
		}
		return FALSE;
	}

	/**
	 * Add user meta
	 *
	 * @access  public
	 * @param   integer
	 * @param   string 
	 * @param   string 
	 * @return  true or false
	 */

	public function add_meta($user_id, $meta_key, $meta_value) {
		//Add user meta, if the meta value is an array encode it
		if (is_array($meta_value))
			$meta_value = json_encode($meta_value);
		//Perform the insert
		$db = new Database;
		if($db->insert('usermeta', 'user_id, meta_key, meta_value', array($user_id, $meta_key, $meta_value) ))
			return TRUE;
		else return FALSE;
	}

	/**
	 * Update user meta
	 *
	 * @access  public
	 * @param   integer
	 * @param   string 
	 * @param   string 
	 * @return  true or false
	 */

	public function update_meta($user_id, $meta_key, $meta_value) {
		//If the meta_value is empty delete that meta key from database
		if (empty($meta_value))
			$this->delete_meta($user_id, $meta_key);
		else
		//Else, first check if the meta key exists
		if ($this->get_meta($user_id, $meta_key)) {
			//If exists then update it
			$db = new Database;
			if ($db->update('usermeta', array('meta_value'=>$meta_value), '(user_id="'.$user_id.'" and meta_key="'.$meta_key.'")', 1))
				return TRUE;
			return FALSE;
		} else {
			//If the meta key does not exists then add a new one
			return $this->add_meta( $user_id, $meta_key, $meta_value);
		}
	}

	/**
	 * Delete user meta
	 *
	 * @access  public
	 * @param   integer
	 * @param   string 
	 * @param   integer 
	 * @return  true or false
	 */

	public function delete_meta ($user_id, $meta_key) {
		$db = new Database;
		/*if ($meta_id) {
			if ($db->delete('usermeta', 'id="'.$meta_id.'"', 1))
				return TRUE;
		}*/
		//Delete meta by user_id and meta_key
		if ($db->delete('usermeta', '(user_id="'.$user_id.'" and meta_key="'.$meta_key.'")'))
			return TRUE;
		return FALSE;
	}
	
	/**
	 * Logout, destory session and unset cookies
	 *
	 * @access  public
	 */

	public function logout(){
		//Destroy session and cookie
		session_destroy();
		$this->set_cookie('_account_user', '');
		$this->set_cookie('_account_pass', '');

		//Logout callback
		if (function_exists('account_logout_callback'))
			account_logout_callback();
	}

	/**
	 * Set cookie
	 *
	 * @access  public
	 * @param   string 
	 * @param   string 
	 */

	public function set_cookie($name, $value){
		setcookie($name , $value , 60 * 60 * 24 * 60 + time() , '/');
    	//setcookie($name , $value , 60 * 60 * 24 * 60 + time() , '/' , DOMAIN , false , true);
	}

	/**
	 * Get user session 
	 *
	 * @access  public
	 * @param   string 
	 * @return  true or false
	 */

	public function session($var){
		if ( isset($_SESSION['_account'], $_SESSION['_account'][$var]) )
			return $_SESSION['_account'][$var];
		else return FALSE;
	}
	
	/**
	 * Set an error 
	 *
	 * @access  public
	 * @param   string 
	 * @param   string
	 * @param   boolean
	 */
	
	public function set_error($error, $field, $lang = TRUE) {
		//Set the error to errors array
		//If lang is true the error message is translated
		if (!empty($error))
			$this->errors[$field] = ($lang) ? $this->lang($error) : $error;
	}
	
	/**
	 * Returns errors
	 *
	 * @access  public
	 * @param   array
	 */

	public function errors() {
		return $this->errors;
	}

	/**
	 * md5 encrypt
	 *
	 * @access  public
	 * @param   string 
	 * @param   string
	 * @param   boolean
	 */

	public function encrypt($str) {
		return md5($str);
	}

	/**
	 * Convert special characters to HTML entities
	 *
	 * @access  public
	 * @param   string 
	 * @param   string
	 */

	public function xss($val) {
		if(is_array($val)) {
			foreach ($val as $key=>$value)
				$val[$key] = $this->xss($value);
			return $val;
		}
		else return htmlspecialchars(trim($val), ENT_QUOTES);
	}

	/**
	 * Check if $_POST['var'] exists
	 *
	 * @access  public
	 * @param   string 
	 * @param   string
	 * @return  string
	 */

	public function set_value($value, $default = NULL)
	{
		if (isset ($_POST[$value]) )
			return $this->xss( $_POST[$value] );
		else if ($default!=NULL) 
			return $default;
	}
	
	/**
	 * Unset $_POST vars
	 *
	 * @access  public
	 * @param   string
	 */

	public function unset_value($value = NULL)
	{
		if ($value)
			unset($_POST[$value]);
		else foreach ($_POST as $key => $var)
			unset($_POST[$key]);
	}
	
	/**
	 * Send email with Gmail or server email
	 *
	 * @access  public
	 * @param   string or array
	 * @param   string
	 * @param   string
	 * @return  true or false
	 */

	public function send_email($to, $subject, $message) {
		
		if (!isset(self::$config['PHPMailer']) or self::$config['PHPMailer'] != FALSE) {

			require_once('PHPMailer/phpmailer.php');
			$mail = new PHPMailer();

			//If the gmail api is set then use gmail
			if (!empty($this->api['gmail']) and !empty($this->api['gmail']['username']) and !empty($this->api['gmail']['password'])) {
				$mail->IsSMTP();
				$mail->SMTPAuth   = true;
				$mail->Host       = 'ssl://smtp.gmail.com';
				$mail->Port       = '465';
				$mail->Username   = $this->api['gmail']['username'];
				$mail->Password   = $this->api['gmail']['password'];
				//$mail->SMTPDebug  = 1;
			}
			//Else use the server email
			else {
				$mail->IsSendmail();
			}
			$mail->From     = self::$config['email'];
			$mail->FromName = self::$config['name'];

			if (is_array($to)) {
				foreach ($to as $sendTo) {
					$mail->AddAddress($sendTo);
				}
			} else {
				$mail->AddAddress($to);
			}
			$mail->Subject = $subject;
			$mail->MsgHTML($message);
			if($mail->Send()) {
				return TRUE;
				//echo "Message Sent";
			} else {
				return FALSE;
				 //echo "Mailer Error: " . $mail->ErrorInfo;
			}
		} else {
			//Simple email sender without the PHPMailer library
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: '.self::$config['name'].' <'.self::$config['email'].'>';
			
			if (is_array($to)) {
				foreach ($to as $sendTo) {
					@mail($to, $subject, $message, $headers);
				}
			} else {
				if ( @mail($to, $subject, $message, $headers) )
					return TRUE;
				else return FALSE;
			}
		}
	}

	/**
	 * Returns language term if is found
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */

	public function lang($str) {
		if (!empty($this->lang[$str]))
			return $this->lang[$str];
		else return $str;
	}

	/**
	 * Returns config 
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */

	public function config($str) {
		if (isset(self::$config[$str]))
			return self::$config[$str];
		else return FALSE;
	}
}
?>