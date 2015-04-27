/*Use regex to check email*/
function check_email(value){
	var emailReg = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
	var valid = emailReg.test(value);
	return valid;
}

/*Use regex to check valid alphanumeric characters*/
function check_alphanumeric(value){
	var valReg = new RegExp(/^[a-zA-Z0-9]+$/i);
	var valid = valReg.test(value);
	return valid;
}

function check_alpha(value){
	var valReg = new RegExp(/^[a-zA-Z]+$/i);
	var valid = valReg.test(value);
	return valid;
}

/**
 * Validation form using regex
 * [value] -- field to be validated 
 * [messageDiv] -- div tag to be filled with relevant message
 * [validationType] -- indicates field type to be validated
 *
 * returns true or false upon completion of validation checks
 */
function is_valid(value,$messageDiv,validationType){

	/*Check to see if the validation field type has been set*/
	validationType = (typeof validationType === "undefined") ? "none" : validationType; 

	/*Check if values are blank*/
	if (value == ''){
		$messageDiv.html("Value cannot be blank.");
		return false; 
	}

	/*Check if values have spaces*/
	if (value.indexOf(' ') >= 0){
		$messageDiv.html("Value cannot have spaces.");
		return false;
	}

	/*If field is email, check that it is correct*/
	if (validationType == "email" && (!check_email(value)) ){
		$messageDiv.html("Please enter a valid email address.");
		return false; 
	}

	/*If field is alphabetic characters only*/
	if (validationType == "alpha" && (!check_alpha(value)) ){
		$messageDiv.html("Please enter valid letters only.");
		return false;
	}

	if (validationType == "alphanumeric" && (!check_alphanumeric(value)) ){
		$messageDiv.html("Please enter only letters or numbers. ");
		return false;
	}
	
	$messageDiv.html('');
	return true; 
	
}

$(document).ready(function(){

		
		$('#submit_form').on('click',function(){ 

			/*If form is valid, continue and obtain the parameter values*/
			var fname = $("#fname").val();
			$fname_sel = $("#fname_error"); 

			var lname = $("#lname").val();
			$lname_sel = $("#lname_error");
			
			var email = $("#email").val();
			$email_sel = $("#email_error"); 

			var uname = $("#username").val();
			$uname_sel = $("#username_error"); 

			var pw = $("#pwd").val();
			$pw_sel = $("#pwd_error"); 

			var check_a = is_valid(fname,$fname_sel,"alpha");
			var check_b = is_valid(lname,$lname_sel,"alpha");
			var check_c = is_valid(email,$email_sel,"email");
			var check_d = is_valid(uname,$uname_sel,"alphanumeric");
			var check_e = is_valid(pw,$pw_sel,"alphanumeric");

			if (check_a && check_b && check_c && check_d && check_e){

					/*Run the login fxn - send ajax http request*/
					xhr = new XMLHttpRequest(); 
					var url = "https://dev.perooz.io/api/auth/create_user.php"
					xhr.open("POST",url,false);
					xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					xhr.setRequestHeader("Client-Id",client_id);
					xhr.onreadystatechange = function(){
						if (xhr.readyState == 4 && xhr.status == 200){
							$("#invalid-form-error-message").html("User successfully created.");
							return true; 
						}else{
							$('#invalid-form-error-message').html("Error creating user. Please try again.")
							return false; 
						}
					}
					xhr.send("fname=" + fname + "&lname=" + lname +"&email=" + email + "&username=" + uname + "&password=" + pw); 
			}
			
			return false; 
	    }); 

});