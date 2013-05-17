var ISLOGGED = false;

/**
 * 
 */
function registerValidate(rsp){
	console.debug(rsp);
	if(rsp.status=='FAIL'){
			$('#register-warning').html(rsp.error);
			$('#register-warning').css('display','block');
			Recaptcha.reload();
	}else{
		$('#register-warning').css('display','none');
		loginShowForm(1);
	}
}

/**
 *
 */
function registerSend(){
	if($('#register-username').val()==''){
		$('#register-warning').html('Empty username');
		$('#register-warning').css('display','block');
		return;
	}else
	if($('#register-password').val()!=$('#register-password2').val()){
		$('#register-warning').html('Passwords are different');
		$('#register-warning').css('display','block');
		return;
	}else{
		$('#register-warning').html('');
		$('#register-warning').css('display','none');
	}
		
		
	var query = {
		method: 'register',
		username: $('#register-username').val(),
		password: loginHash($('#register-password2').val()),
		email: $('#register-email').val(),
		challenge: Recaptcha.get_challenge(),
		response: Recaptcha.get_response()
	};
	
	sendQuery(query, registerValidate);
}

/**
 * Client side hashing according to method used in database
 */
function loginHash(data){
	var shaObj = new jsSHA(data, "TEXT");
	var hash = shaObj.getHash("SHA-256", "HEX");
	return hash;
}

/**
 * Callback function for login query
 */
function loginValidate(rsp){
	if(rsp.status=='FAIL'){
		$('#login-warning').css('display','block');
		$('#login-warning').html(rsp.error);
	}else
	if(rsp.status=='SUCCESS'){
		$('#login-warning').css('display','none');
		$('#login-warning').html('');
		welcomeSend();
	}
}

/**
 * On login box send
 */
function loginSend(){
	var query = {
		method: 'login',
		username: $('#login-username').val(),
		password: loginHash($('#login-password').val()),
		rememberme: $('#login-remember').is(':checked')
	};
	
	console.debug(query);
	
	sendQuery(query, loginValidate);
}

/**
 * Welcome callback
 */
function welcomeValidate(rsp){
	if(rsp.status=='FAIL'){//niezalogowany
		loginShowForm(3);
		$('#login-username').val('username');
		$('#login-password').val('password');
		$('#maps-options').css('display','none');
		$('#map-actions').css('display','none');
	}else{//zalogowany
		ISLOGGED = true;
		loginShowForm(4);
		$('#logout-button').html('Logout ['+rsp.login+']');
		$('#tools-box').css('display','block');
		_mapSetView('browse-maps');
		$('#maps-options').css('display','block');
		$('#map-actions').css('display','block');
	}
}

/**
 * Detect if user is already loaded
 */
function welcomeSend(){
	var query = {
		method: 'welcome'
	};
	sendQuery(query, welcomeValidate);
}

function logoutCallback(rsp){
	if(rsp.status=='SUCCESS'){
		//czysc dane
		$('#login-username').val('username');
		$('#login-password').val('password');
		loginShowForm(3);
		$('#tools-box').css('display','none');
		$('#maps-box').addClass('hidden');
		$('#maps-list').html('');
		$('#maps-options').css('display','none');
		_mapSetView('empty');
		ISLOGGED = false;
	}
}
/**
 * 
 */
function logoutSend(){
	var query = {
		method: 'logout'
	};
	sendQuery(query, logoutCallback);
}

/**
 * Init function
 */
$(function(){
	$('#login-button').click(loginSend);
	$('#logout-button').click(logoutSend);
	$('#register-button').click(registerSend);
	welcomeSend();
	///$('#lf').submit(function(){alert('ssss');});//submit(function(){alert('ss');});
	
});
