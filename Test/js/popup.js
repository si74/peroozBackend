/*Javascript for Perooz Chrome extension browser action
Author: Sneha Inguva
Date: 7-29-2014*/
$(document).ready(function(){

chrome.cookies.get({'url': 'https://dev.perooz.io/api','name':'session_token'}, function(cookie){
	if (cookie){
		$(".main").html("I am logged in");
		return cookie;
	}else{
		
		var nonce = false; 
		
		console.log(client_id);
		console.log(nonce);

		/*Send web api request to receive nonce value*/
		xhr = new XMLHttpRequest();
		var url = "https://dev.perooz.io/auth/nonce.php"; 
		xhr.open("GET", url, false);
		xhr.setRequestHeader("Content-Type: application/json");
		xhr.setRequestHeader("Client-Id: " + client_id);
		
		xhr.onreadystatechange = function(){
			if (xhr.readyState == 4 && xhr.status == 200){
				var raw_data = callback(xhr.responseText)
				var data=JSON.parse(raw_data);
				nonce = data.nonce; 
			}
		}

		xhr.send(); 

		console.log("xhr.responseText");


		if (nonce){
			/*Set the login form*/
			$(".main").html("<div class='login_container'>\
							<div class='logo'><img src='images/blueperooz.png' alt='Perooz' style='height:3em;width:auto;'></div>\
							<form action='' method='post'>\
								<input type='hidden' name='" + nonce + "' value=''/>\
								<input class='username' type='text' name='username' placeholder='username' /><br/>\
								<input class='pwd' type='password' name='username' placeholder='password' /><br/>\
								<button class='sign_in'>SIGN IN</button><br/>\
							</form>\
							<button class='sign_up'>NEW USER? SIGN UP</button><br/>\
							<div class='forgot_pw'><a href='#'>FORGOT YOUR LOGIN INFO?</a></div>\
							</div>");
		}else{
			$(".main").html("<div class='message'>Error loading login page. Please try again</div>");
		}
	}

});

});

