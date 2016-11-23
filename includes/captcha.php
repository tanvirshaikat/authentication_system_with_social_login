<?php
	session_start();
	if(!empty($_SESSION['_account_captcha_init']))
	{
		$NewImage =imagecreatefromjpeg("../assets/images/captcha.jpg"); 
		$LineColor = imagecolorallocate($NewImage,233,239,239);
		$TextColor = imagecolorallocate($NewImage, 255, 255, 255);
		imageline($NewImage,1,1,40,40,$LineColor); 
		imageline($NewImage,1,100,60,0,$LineColor);
		imagestring($NewImage, 5, 25, 6, $_SESSION['_account_captcha_init'], $TextColor);
		header("Content-type: image/jpeg"); 
		imagejpeg($NewImage);
		$_SESSION['_account_captcha_init'] = FALSE;
	}
?>
