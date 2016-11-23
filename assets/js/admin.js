$(function() {
	//Disable input autocompletion
	$('input[type="text"]').attr('autocomplete','off');
	
	/* Menu script with cookies*/
	var cookieName='_admin_menu';
	$('#sidebar .submenu ul').hide();
	var cookie = $.cookie(cookieName);
	if(cookie && Admin.isJson(cookie)) {
		cookie = jQuery.parseJSON(cookie);
		if(cookie.menu) {	
			var submenu =$('.submenu[data-id="'+cookie.menu+'"]');
			submenu.addClass('open');
			submenu.find('ul').show();
		}
	}
	$('.submenu > a').click(function(e){
		e.preventDefault();
		var submenu = $(this).siblings('ul');
		var li = $(this).parents('li');
		var submenus = $('#sidebar li.submenu ul');
		var submenu_parent = $('#sidebar li.submenu');
		var id = li.attr('data-id');
		var cookie = $.cookie(cookieName);
		if(li.hasClass('open')){
			submenu.slideUp();
			li.removeClass('open');
			if(cookie && Admin.isJson(cookie)) {
				cookie = jQuery.parseJSON(cookie);
				cookie.menu = '';
			}
			$.cookie(cookieName, JSON.stringify(cookie), { expires: 30, path: '/' });
		} else {
			submenus.slideUp();			
			submenu.slideDown();
			submenu_parent.removeClass('open');		
			li.addClass('open');
			if(cookie && Admin.isJson(cookie)) {
				cookie = jQuery.parseJSON(cookie);
				cookie.menu = id;
			}
			else cookie = {menu:id};
			$.cookie(cookieName, JSON.stringify(cookie), { expires: 30, path: '/' });
		}
	});
});
//admin functions
var Admin = {
	isJson : function(str) {
		var IS_JSON = true;
		try {
	    	var json = $.parseJSON(str);
	    } catch(err) {
	        IS_JSON = false;
	    }
	    return IS_JSON;
	},
	confirm_delete_user : function (user_id, username) {
		$('#deleteUserModal #user_id').val(user_id);
		$('#deleteUserModal #username').text(username);
		$('#deleteUserModal').modal('show');
	},
	delete_user : function (user_id) {
		//ajax post request
		$.post('index.php?page=delete_user', {user_id: user_id});
		//remove row from table
		$('#users [data-id="'+user_id+'"]').parent().remove();
		//hide modal
		$('#deleteUserModal').modal('hide');
	},
	compose : function (email) {
		//if is email add the email to input #to
		if (email)
			$('#composeModal #to').val(email);
		//show compose email modal
		$('#composeModal').modal('show');
	},
	send_email : function () {
		//Create vars
		var to = $('#to'),
			subject = $('#subject'),
			message = $('#message');

		//check if fields are not empty
		if (to.val().length <= 0) 
			to.focus();
		else if (subject.val().length <= 0) 
			subject.focus();
		else if (message.val().length <= 0) 
			message.focus();
		else {
			//ajax post request
			$.post('index.php?page=send_email', {to: to.val(), subject: subject.val(), message : message.val()});
			//hide compose email modal
			$('#composeModal').modal('hide');
			//clear inputs
			to.val('');
			subject.val('');
			message.val('');
		}
	}
};

/*!
 * jQuery Cookie Plugin v1.3
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2011, Klaus Hartl
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 */
(function(a,b,c){function e(a){return a}function f(a){return decodeURIComponent(a.replace(d," "))}var d=/\+/g;var g=a.cookie=function(d,h,i){if(h!==c){i=a.extend({},g.defaults,i);if(h===null){i.expires=-1}if(typeof i.expires==="number"){var j=i.expires,k=i.expires=new Date;k.setDate(k.getDate()+j)}h=g.json?JSON.stringify(h):String(h);return b.cookie=[encodeURIComponent(d),"=",g.raw?h:encodeURIComponent(h),i.expires?"; expires="+i.expires.toUTCString():"",i.path?"; path="+i.path:"",i.domain?"; domain="+i.domain:"",i.secure?"; secure":""].join("")}var l=g.raw?e:f;var m=b.cookie.split("; ");for(var n=0,o=m.length;n<o;n++){var p=m[n].split("=");if(l(p.shift())===d){var q=l(p.join("="));return g.json?JSON.parse(q):q}}return null};g.defaults={};a.removeCookie=function(b,c){if(a.cookie(b)!==null){a.cookie(b,null,c);return true}return false}})(jQuery,document);