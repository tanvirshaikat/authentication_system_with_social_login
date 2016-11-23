//Selectors
var webcamC = '#webcam_container';
var uploadC = '#upload_container';

//Updating cropping x, y, width and height values
function updateSelection(img, selection) { 	
	$('#x1').val(selection.x1);
	$('#y1').val(selection.y1);
	$('#w').val(selection.width);
	$('#h').val(selection.height);
}

//Save cropped image
function saveImage(selector) {
	
	var alert = $(selector).find('.alert');
	var crop = $(selector).find('.crop');

	if (crop.html()!='') {
		var x1 = $('#x1').val(),
			y1 = $('#y1').val(),
			w = $('#w').val(),
			h = $('#h').val();

		//Check if the crop selection was made, otherwhise set default values
		if (w == "" || w == 0)
			w  = crop.find('img').width();
		if (h == "" || h == 0)
			h  = crop.find('img').height();
		if (x1 == "" || x1 == 0) x1 = Math.round((w-h)/2);
		if (y1 == "") y1 = 0;
		if (w > h) w = h;
		else if(h > w) h = w;
		
		//Hide alert
		alert.removeClass('alert-error').hide().children('span').text('');
		
		var data = {
			'x1' : x1,
			'y1' : y1,
			'w'  : w,
			'h'  : h,
			'action' : 'save_image'
			};

			//Show loading message
		alert.removeClass('alert-error').show().children('span').text('Saving image...');
		//Disable buttons
		crop.find('button').attr('disabled', 'disabled');
		//Apply image area select 
		crop.find('img').imgAreaSelect({ aspectRatio: '1:1', onSelectChange: updateSelection });

		//Ajax Request to save the cropped image
		$.ajax({
			type: 'POST',
			url: AJAX,
			data: data,
			dataType: 'json',
			success: function(response) {
				//If error exists or no message display erro message
				if (response.error!='' || response.msg=='')
					alert.addClass('alert-error').show().children('span').text('Unexpected Error. Please try again.');
				else {
					//Else remove cropping
					crop.html('');
					removeSelection();

					//Success message
					alert.removeClass('alert-error').addClass('alert-success').show().children('span').html('Your image has been saved.');
					//$('.user-avatar img').attr('src', response.msg);
					//select hack
					$('#social-avatar option[value="uploaded"]').removeAttr('disabled').text('Uploaded');
					$('#social-avatar').val('uploaded');

				}
			},
			error: function() {
				//Ajax fails display image
				alert.addClass('alert-error').show().children('span').text('Unexpected Error. Please try again.');
			},
			complete: function(){
				//Enable buttons
				crop.find('button').removeAttr('disabled');
			}
		});
		
	}
}

//Callback function when webcam image was uploaded
function webcamOnComplete(response) {
	//parse the json format into object
	var response = jQuery.parseJSON(response);
	if (response.error!='')
		alert.addClass('alert-error').show().children('span').text('Unexpected Error. Please try again.');
	else {
		//Create the cropping html elements
		$(webcamC).find('.control').hide();
		$(webcamC).find('#webcam').html('');
		$(webcamC).find('.crop').html('<h4>Crop your image</h4> <div class="thumbnail"><img src="'+response.msg+'"/></div><p><button type="button" class="btn btn-small cancel"> <i class="icon-remove"></i> Cancel</button> <button class="btn btn-primary btn-small" onClick="webcamSnapshot()"> <i class="icon-camera icon-white"></i> New Snapshot</button> <button type="button" class="btn btn-primary btn-small" onclick="saveImage(\''+webcamC+'\')"> <i class="icon-ok-sign icon-white"></i> Save Image</button></p>');
		$(webcamC).find('img').imgAreaSelect({ aspectRatio: '1:1', onSelectChange: updateSelection }); 
	}
	//webcam.reset();
}

//Create html for webcam
function webcamSnapshot() {
	removeSelection();
	$(uploadC).hide();
	$(webcamC).find('.crop').html('');
	webcam.set_api_url(AJAX);
	webcam.set_swf_url('assets/webcam/webcam.swf');
	webcam.set_shutter_sound(true, 'assets/webcam/shutter.mp3');  // play shutter click sound
	webcam.set_quality( 90 ); // JPEG quality (1 - 100)
	$(webcamC).find('#webcam').html( '<div class="thumbnail">'+ webcam.get_html(600, 450) +'</div>' );
	webcam.set_hook( 'onComplete', 'webcamOnComplete' );
	$(webcamC).show();
	$(webcamC).find('.control').show();
}

$(function(){
	
	//On click cancel remove crop or webcam 
	$(uploadC+','+webcamC).on('click', '.cancel', function() {
		$(uploadC+' .crop').html('');
		removeSelection();
		$(webcamC).hide();
		$('.alert').removeClass('alert-error').hide();
	});

	/*Image upload*/
	var alert = $(uploadC).find('.alert');
	//Upload button selector
	var btnUpload = $('#uploadimage');
	//Create new AjaxUpload
	new AjaxUpload(btnUpload, {
		action: AJAX,
		data: {'action': 'upload'},
		name: 'uploadimage',
		responseType: 'json',
		onSubmit: function(file, ext) {
			removeSelection();
			$(webcamC).hide();
			$(uploadC).show();
			//Display a loding message
			alert.removeClass('alert-error').show().children('span').text('Uploading image...');
		},
		onComplete: function(data, response) {
			if(response.error!='')
				if( response.error == 'ext' )
					alert.addClass('alert-error').show().children('span').text('File extension not allowed.');
				else if( response.error == 'size' )
					alert.addClass('alert-error').show().children('span').text('The image file size is to big.');
				else alert.addClass('alert-error').show().children('span').text('Unexpected Error.');
			else {
				alert.hide().children('span').text();
				$(uploadC).find('.crop').html('<h4>Crop your image</h4> <div class="thumbnail"><img src="'+response.msg+'"/></div><p><button type="button" class="btn btn-small cancel"> <i class="icon-remove"></i> Cancel</button> <button type="button" class="btn btn-primary btn-small" onclick="saveImage(\''+uploadC+'\')"><i class="icon-ok-sign icon-white"></i> Save Image</button></p>');
				removeSelection();
				$(uploadC).find('img').imgAreaSelect({ aspectRatio: '1:1', onSelectChange: updateSelection });  
			}
		}
	});

});

//This function will remove the selection elements for cropping (those borders that you select)
function removeSelection(){
	$('.imgareaselect-outer, .imgareaselect-selection, .imgareaselect-border1, .imgareaselect-border2').remove();
}