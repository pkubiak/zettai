/*function loginButton(){
	if($('#login-panel').hasClass('expanded')){
		alert($('#login-username').val()+'|'+$('#login-password').val()+'|'+$('#login-remember').is(':checked'));
	}else{
		$('#login-panel').addClass('expanded');
	}
}*/

function loginShowForm(formId){
	if(formId==1){//login-form
		$('#login-form').css('display', 'block');
		$('#register-form').css('display','none');
		$('#guess-form').css('display', 'none');
		$('#logged-form').css('display','none');
		
		$('#close-button').css('display','block');
	}else
	if(formId==2){//register-form
		$('#login-form').css('display', 'none');
		$('#register-form').css('display','block');
		$('#guess-form').css('display', 'none');
		$('#logged-form').css('display','none');
		
		if($('#register-recaptcha').html()=='')
			Recaptcha.create("6Lf_vNsSAAAAAKk8ARUuiIE5DQ8UBbaPPLeJgTaD", 'register-recaptcha', {
				 theme: "clean",
				 callback: Recaptcha.focus_response_field});
       
         
		$('#close-button').css('display','block');
	}else
	if(formId==3){//guess-form
		$('#login-form').css('display', 'none');
		$('#register-form').css('display','none');
		$('#guess-form').css('display', 'block');
		$('#logged-form').css('display','none');
		
		$('#close-button').css('display','none');
	}else
	if(formId==4){//loged-form
		$('#login-form').css('display', 'none');
		$('#register-form').css('display','none');
		$('#guess-form').css('display', 'none');
		$('#logged-form').css('display', 'block');
		$('#close-button').css('display','none');	
		
	}
}



//Ustaw wszystko
/*$(function(){
	
	//$('#login-button').click(loginButton);

});*/

