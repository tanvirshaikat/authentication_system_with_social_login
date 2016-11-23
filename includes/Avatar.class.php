<?php
/**
   * Account
   * 
   * @author  Tanvir Shaikat <ta.shaikat@gmail.com>
   */

class Avatar extends Account
{
	public function __construct()
    {
        parent::__construct();
    }

    /**
	 * Uploads avatar
	 *
	 * @access  public
	 * @param   array
	 * @return  true or string
	 */

    public function upload_avatar($file)
    {
    	//Get php.ini upload limit
		$max_post     = (int)(ini_get('post_max_size'));
		$max_upload   = (int)(ini_get('upload_max_filesize'));
		$memory_limit = (int)(ini_get('memory_limit'));
		$upload_limit = min($max_upload, $max_post, $memory_limit);

		$image_size = $file['size'];
		$filename   = substr( md5( $this->session('user_id') ) , 0, 10);

    	//Check if the image was uploaded
		if (!is_uploaded_file($file['tmp_name']))
			$this->set_error('error', 'error');
		else if ($image_size > $upload_limit *100*100*100  or $image_size > self::$config['file_size_limit'] *100*100*100)
			$this->set_error( 'size', 'error');
		else {
			$ext = $this->get_file_ext($file['name']); //Get file extension
			$allowed_ext =  explode('|', self::$config['allowed_extensions']); //Get allowed extensions
			
			//Check if extension is allowed
			if (!in_array($ext, $allowed_ext) )
				$this->set_error('ext', 'error');
			else {
				$ext = '.'.$ext;
				$tmp_path = self::$config['base_path'] .'/'. self::$config['upload_path'] . 'tmp/'; //Set temporarily upload path
				
				//Create dirs if not exists
				if (!is_dir($tmp_path)) 
					mkdir($tmp_path);

				$path = $tmp_path . basename( $filename.$ext );

				//Check if the file was uploaded
				if(move_uploaded_file($file['tmp_name'], $path)) {
					
					chmod($path, 0777); //Set dir permissions

					//If the image is bigger than the maximum width resize it
					if($this->imgWidth($path) > self::$config['image_max_width'])
					{
						$image = new SimpleImage();
						$image->load($path);
						$image->resizeToWidth(self::$config['image_max_width']);
						$image->save($path);
					}
					//If the image is bigger than the maximum height resize it
					if($this->imgHeight($path) > self::$config['image_max_height'])
					{
						$image = new SimpleImage();
						$image->load($path);
						$image->resizeToHeight(self::$config['image_max_height']);
						$image->save($path);
					}
					//image file session
					$_SESSION['_tmp_image']  = $filename . $ext;

					//image url
					return self::$config['base_url'] . self::$config['upload_path'] . 'tmp/' . $filename . $ext;
				}
				else $this->set_error('error', 'error');
			}
		}
		return FALSE;
    }

    /**
	 * Save cropped image
	 *
	 * @access  public
	 * @param   array
	 * @return  string
	 */

    public function save_image ($data) {
    	extract($data);
    	$file = $_SESSION['_tmp_image'];
    	$path = self::$config['base_path'] .'/'. self::$config['upload_path']; //upload path
		$tmp_path = $path . 'tmp/'; //temporarily upload path
		$path .= 'avatars/'; //avatars path
		//Create dirs if not exists
		if (!is_dir($tmp_path))
			mkdir($tmp_path);
		if (!is_dir($path))
			mkdir($path);

		//crop image
		$this->cropImage($path.$file, $tmp_path.$file, $width, $height, $x1, $y1, self::$config['image_crop_size'] / $width);
			
		@unlink($tmp_path.$file); //delete temporarily image
		unset($_SESSION['_tmp_image']); //unset the temp session
		
		//save only image extension in database
		$this->update_meta( $this->session('user_id'), 'uploaded', $this->get_file_ext( $file ) );
		$this->update_meta( $this->session('user_id'), 'avatar', 'uploaded' );
		
		return self::$config['base_url'] . self::$config['upload_path'] . 'avatars/' . $file; //return image url
    }

    /**
	 * Uploads image from the webcam
	 *
	 * @access  public
	 * @param   data
	 * @return  false or string
	 */

    public function webcam_upload($image_data) {
    	$filename = substr( md5( $this->session('user_id') ) , 0, 10) . '.jpg'; //set filename
		$tmp_path = self::$config['base_path'] .'/'. self::$config['upload_path'] . 'tmp/'; //Set temporarily upload path
		
		//save image
		if (file_put_contents($tmp_path . $filename, $image_data )) {
			$_SESSION['_tmp_image'] = $filename; //set session
			return self::$config['base_url'] . self::$config['upload_path'] . 'tmp/' . $filename; //return image url
		}
		return FALSE;
    }

    /**
	 * Returns file extension (without .)
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */

    public function get_file_ext($file)
	{
		$ext = strtolower($file[strlen($file)-4].$file[strlen($file)-3].$file[strlen($file)-2].$file[strlen($file)-1]);
		if ($ext[0] == '.')
			$ext = substr($ext, 1, 3);
		return $ext;
	}

	/**
	 * Crop image by width, heightm start width and start height
	 *
	 * @access  public
	 * @param   ....
	 * @return  string
	 */

	public function cropImage($save_path, $image_path, $width, $height, $start_width, $start_height, $scale)
	{
		list($imagewidth, $imageheight, $imageType) = getimagesize($image_path);
		$imageType = image_type_to_mime_type($imageType);
		
		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);

		switch($imageType)
		{
			case "image/gif":
				$source = imagecreatefromgif($image_path); 
				break;
		    case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				$source = imagecreatefromjpeg($image_path); 
				break;
		    case "image/png":
			case "image/x-png":
				$source = imagecreatefrompng($image_path); 
				break;
	  	}

		imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);
		
		switch($imageType)
		{
			case "image/gif":
		  		imagegif($newImage, $save_path); 
				break;
	      	case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
		  		imagejpeg($newImage, $save_path, 90); 
				break;
			case "image/png":
			case "image/x-png":
				imagepng($newImage, $save_path);  
				break;
	    }
		chmod($save_path, 0777);
		return $save_path;
	}

	/**
	 * Returns image height
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */

	public function imgHeight($image)
	{
		$size = getimagesize($image);
		$height = $size[1];
		return $height;
	}

	/**
	 * Returns image width
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */

	public function imgWidth($image)
	{
		$size = getimagesize($image);
		$width = $size[0];
		return $width;
	}
}

?>