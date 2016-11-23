<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
//Check if data was send
if (!empty($_POST['to']) and !empty($_POST['subject']) and !empty($_POST['message']) ) {
	$to = $Account->xss($_POST['to']);
	$subject = $Account->xss($_POST['subject']);
	//If there are more emails create array with emails
	if (strchr($to, ';'))
		$to = explode(';', $to);

	//If we have more emails send an email foreach user
	if (is_array($to)) {
		foreach ($to as $key => $sendTo)
			//Check if email is valid
			if (preg_match($Account->pattern['email'], $sendTo))
				//Send email
				$Account->send_email($sendTo, $subject, $_POST['message']);
	}
	else $Account->send_email($to, $subject, $_POST['message'] );
}
?>