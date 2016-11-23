<?php
/**
   * Admin Class for the Account Cclass
   * 
   * @author  Cretu Eusebiu <cretu.eusebiu@gmail.com>
   */

class Admin extends Account
{
	public function __construct()
    {
        parent::__construct();
    }

    /**
	 * Returns users from users table
	 *
	 * @access  public
	 * @param   array
	 * @return  array or false
	 */

	public function get_users($data){
		$db = new Database();
		if ($db->select('users', '*')) {
			$result = $db->getResult();
			return $result;
		}
		return FALSE;
	}

	/**
	 * Delete user by id
	 *
	 * @access  public
	 * @param   integer
	 * @return  true or false
	 */

	public function delete_user($user_id) {
		$db = new Database();
		if ($db->delete('users', 'id="'.$user_id.'"', 1)) {
			$db->delete('usermeta', 'user_id="'.$user_id.'"');
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Add new user to database
	 *
	 * @access  public
	 * @param   array
	 * @return  true or false
	 */

	public function add_user ($data) {
		
		extract($data);

		$db = new Database;

		//Do some checks and set errors
		if (empty($username))
			$this->set_error('empty_username', 'username');
		else if (!preg_match($this->pattern['username'], $username)) 
			$this->set_error('valid_username', 'username');
		else if ($query = $db->select('users', 'id', '(username = "'. $username .'")', null, 1))
			$this->set_error('username_exists', 'username');
		
		if (empty($email))
			$this->set_error('empty_email', 'email');
		else if (!preg_match($this->pattern['email'], $email)) 
			$this->set_error('valid_email', 'email');
		else if ($query = $db->select('users', 'id', '(email = "'. $email .'")', null, 1))
			$this->set_error('email_exists', 'email');
		
		if (!empty($first_name)) {
			if (!empty($first_name) and !preg_match($this->pattern['alpha'], $first_name)) 
				$this->set_error('valid_fname', 'first_name');
 		}
 		if (!empty($last_name)) {
			if (!empty($last_name) and !preg_match($this->pattern['alpha'], $last_name)) 
				$this->set_error('valid_lname', 'last_name');
 		}

 		if (isset($url)) {
			if (!empty($url) and !preg_match($this->pattern['url'], $url)) 
				$this->set_error('valid_url', 'url');
 		}

		if (empty($password))
			$this->set_error('empty_pass', 'password');
		else if (strlen($password)<5)
			$this->set_error('pass_to_short', 'password');
		else if ($confirm_password != $password)
			$this->set_error('pass_not_match', 'confirm_password');
 		
 		if ( empty( $this->errors ) ) {
 			
 			//Insert query
			$db = new Database();
			if ( $db->insert('users', 'username, email, password, status, first_name, last_name, url',  
				array( $username, $email, $this->encrypt($password), 1, $first_name, $last_name, $url)) ) {
				//Get the id of the user that we've just created
				$user_id = mysql_insert_id();
				
				//Add  rome meta
				//Update usermeta
				if (!empty($usermeta))
					foreach ($usermeta as $key => $value)
						$this->add_meta($user_id, $key, $value);

				//sprintf will replace the first string with the next parameters, here will insert a link 
				$message = sprintf($this->lang('email_add_message'), $username, $password, $this->links['login']);

				//Send the email
				if (!empty($send_email))
					$this->send_email($email, $this->lang('email_add_subject'), $message);
				$this->unset_value();
				return TRUE;
			}
			//Something went wron, set error
			else $this->set_error('error', '_error');

 		}
	
		return FALSE;
	}
}
?>