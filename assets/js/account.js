//Ajax file url
var AJAX = "includes/ajax.php";
//Language
var ACCOUNT_LANG = {
	error: "An unexpected error has occurred. Please try again.",
	errors_found: "One or more errors where found",
	loading: "Loading...",
	enter_username: "Enter your username",
	enter_pass: "Enter your password.",
	lettersonly: "Enter only letters without space.",
	usernamecheck: 'Must contain only alpha-numeric characters',
	username_taken: "This username is already taken",
	enter_name: "Enter your name",
	username_min: "Must be at least 3 characters",
	username_max: "Must be under 50 characters",
	password_req: "Provide a password",
	password_min: "Must be at least 5 characters long",
	password_match: "Enter the same password as above",
	enter_email: "Enter a valid email address",
	email_taken: 'This email is already registered',
	captcha_req: "Enter the verification code",
	captcha_match: "The verification code is invalid",
	email_not_exist: "There's no account with this email",
	invalid_key: 'This link is invalid.'
};

$(document).ready(function () {
	//Disable input autocompletion
	$('input[type="text"]').attr('autocomplete','off');

	$('.alert .close').click(function(){
		$('.alert').removeClass('alert-error alert-loading').fadeOut("fast");
	});

	//Methods for jQuery validate
	jQuery.validator.addMethod("lettersonly", function(value, element) {
		return this.optional(element) || /^[a-z]+$/i.test(value);
	}, Account.lang.lettersonly);
	jQuery.validator.addMethod("usernamecheck", function(value, element) {
		return this.optional(element) || /^[a-zA-Z0-9]+[a-zA-Z0-9\_\.]+[a-zA-Z0-9]+$/i.test(value);
	}, Account.lang.usernamecheck);

	//Login form validation
	$('form#login').validate({
		errorElement: "span",
		rules: {
			user: {
				required: true
			},
			password: {
				required: true
			}
		},
		messages: {
			user: Account.lang.enter_username, 
			password: Account.lang.enter_pass
		},
		success: Account.validate.success,
		errorPlacement: Account.validate.errorPlacement,
		showErrors: Account.validate.showErrors,
		submitHandler: function(form) {
			Account.submit({
				form: form,
				success: function (data, form) {
					if(data.msg && !data.error)
						window.location.reload();
					else Account.errors(data.error, $(form));
				}
			});
			return false;
		}
	});
	
	//Signup form validation
	$('form#signup').validate({
		errorElement: "span",
		rules: {
			username: {
				required: true,
				minlength: 3,
				maxlength: 50,
				usernamecheck: true,
				remote: AJAX+'?action=usernamecheck'
			},
			password: {
				required: true,
				minlength: 5
			},
			confirm_password: {
				required: true,
				minlength: 5,
				equalTo: "#password"
			},
			email: {
				required: true,
				email: true,
				maxlength: 100,
				remote: AJAX+'?action=emailcheck'
			},
			captcha: {
				required: true
			}
		},
		messages: {
			username: {
				required: Account.lang.enter_username,
				minlength: Account.lang.username_min,
				maxlength: Account.lang.username_max,
				remote: Account.lang.username_taken
			},
			email: {
				required: Account.lang.enter_email,
				remote: Account.lang.email_taken
			},
			password: {
				required: Account.lang.password_req,
				minlength: Account.lang.password_min
			},
			confirm_password: {
				required: Account.lang.password_req,
				minlength: Account.lang.password_min,
				equalTo: Account.lang.password_match
			},
			captcha: Account.lang.captcha_req,
		},
		success: Account.validate.success,
		errorPlacement: Account.validate.errorPlacement,
		showErrors: Account.validate.showErrors,
		submitHandler: function(form) {
			Account.submit({
				form: form,
				success: function (data, form) {
					if(data.msg && !data.error) {
						$(form).fadeOut(200,0).remove();
						$('.success-message').fadeIn('slow');
					}
					else {
						Account.errors(data.error, $(form));
						//if(data.error && data.error.captcha)
							Account.refreshCaptcha();
					}
				}
			});
			return false;
		}
	});

	//Resend activation email form validation
	$('form#resend').validate({
		errorElement: "span",
		rules: {
			email: {
				required: true,
				email: true,
				maxlength: 100,
			},
			captcha: {
				required: true
			}
		},
		messages: {
			email: {
				required: Account.lang.enter_email,
				remote: Account.lang.email_not_exist
			},
			captcha: Account.lang.captcha_req
		},
		success: Account.validate.success,
		errorPlacement: Account.validate.errorPlacement,
		showErrors: Account.validate.showErrors,
		submitHandler: function(form) {
			Account.submit({
				form: form,
				success: function (data, form) {
					if(data.msg && !data.error) {
						$(form).fadeOut(200,0).remove();
						$('.success-message').fadeIn('slow');
					}
					else {
						Account.errors(data.error, $(form));
						//if(data.error && data.error.captcha)
							Account.refreshCaptcha();
					}
				}
			});
			return false;
		}
	});

	//Recover password form validation
	$('form#recover').validate({
		errorElement: "span",
		rules: {
			email: {
				required: true,
				email: true,
				maxlength: 100,
			},
			captcha: {
				required: true
			}
		},
		messages: {
			email: {
				required: Account.lang.enter_email,
				remote: Account.lang.email_not_exist
			},
			captcha: Account.lang.captcha_req
		},
		success: Account.validate.success,
		errorPlacement: Account.validate.errorPlacement,
		showErrors: Account.validate.showErrors,
		submitHandler: function(form) {
			Account.submit({
				form: form,
				success: function (data, form) {
					if(data.msg && !data.error) {
						$(form).fadeOut(200,0).remove();
						$('.success-message').fadeIn('slow');
					}
					else {
						Account.errors(data.error, $(form));
						//if(data.error && data.error.captcha)
							Account.refreshCaptcha();
					}
				}
			});
			return false;
		}
	});

	//Change password form validation
	$('form#changepass').validate({
		errorElement: "span",
		rules: {
			password: {
				required: true,
				minlength: 5
			},
			confirm_password: {
				required: true,
				minlength: 5,
				equalTo: "#password"
			}
		},
		messages: {
			password: {
				required: Account.lang.password_req,
				minlength: Account.lang.password_min
			},
			confirm_password: {
				required: Account.lang.password_req,
				minlength: Account.lang.password_min,
				equalTo: Account.lang.password_match
			}
		},
		success: Account.validate.success,
		errorPlacement: Account.validate.errorPlacement,
		showErrors: Account.validate.showErrors,
		submitHandler: function(form) {
			Account.submit({
				form: form,
				success: function (data, form) {
					if(data.msg && !data.error) {
						$(form).fadeOut(200,0).remove();
						$('.success-message').fadeIn('slow');
					}
					else {
						Account.errors(data.error, $(form));
						//if(data.error && data.error.captcha)
							Account.refreshCaptcha();
					}
				}
			});
			return false;
		}
	});
});

//Account Functions
var Account = {
	validate: {
		success: function(label){
			label.parent().parent().parent().parent().removeClass('error');
			label.addClass("success");
		},
		errorPlacement: function(error, element) {
			error.appendTo(element.parent().children('.help-inline'));
			element.parent().parent().parent().addClass('error');
		},
		showErrors: function(errorMap, errorList) {
			for (var i = 0; errorList[i]; i++) {
            	var element = this.errorList[i].element;
            	this.errorsFor(element).remove();
        	}
        	this.defaultShowErrors();
		}
	},

	submit: function (context) {
		var data = {};
		
		//Serialize form inputs
		$.each($(context.form).serializeArray(), function(_, kv) {
			if (data.hasOwnProperty(kv.name)) {
				data[kv.name] = $.makeArray(data[kv.name]);
			    data[kv.name].push(kv.value);
			}
			else data[kv.name] = kv.value;
		});
		var formInput = '#'+$(context.form).attr('id')+' input';
		
		//Ajax request
		$.ajax({
			url: AJAX,
			data: $(context.form).serialize(),
			dataType: 'json',
			type: 'POST',
			beforeSend: function(){
				//Disable inputs
				$(formInput).each(function() {
					$(this).attr('disabled', 'disabled');
				});
				Account.alert(Account.lang.loading, 'alert-loading');
			},
			success: function(data) {
				//success callback
				context.success(data, context.form);
			},
			error: function(){
				Account.alert(Account.l.error, 'alert-error');
			},
			complete: function() {
				//Enable inputs back
				$(formInput).each(function() {
					$(this).removeAttr('disabled');
				});
			}
		});
	},

	alert: function(html, klass) {
		var e = $('.ajax-response');
		if(e.hasClass())
			e.fadeOut("fast").fadeIn("slow", function () {e.children('span').html(html); e.removeClass('alert-error alert-loading').addClass(klass);});
		else 
			e.fadeOut("fast", function () {e.children('span').html(html); e.removeClass('alert-error alert-loading').addClass(klass);}).fadeIn("slow");
	},

	errors: function(errors, form){
		if(errors)
			for(error in errors)
				if(errors[error]!='') {
					var e = form.find('[name="' + error + '"]');
					e.parent().parent().parent().addClass('error')
					e.next().children().removeClass('success').html(errors[error]);
				}
		if(!errors._error || errors._error=='') 
			errors._error = Account.lang.errors_found;
		Account.alert(errors._error, 'alert-error');
	},

	refreshCaptcha: function () {
		$.post(AJAX, {action:'refreshcaptcha'}, function (data) {
			//Fix for IE browsers
			var time = new Date().getTime();
			var src = $('.captcha-image').attr('src');
			
			if ( src.indexOf('?time') > 0 )
				src = src.substring(0, src.indexOf('?time'));
			
			$('.captcha-image').attr('src', src + '?time='+ time );
		});
	},
	
	lang: ACCOUNT_LANG,
}