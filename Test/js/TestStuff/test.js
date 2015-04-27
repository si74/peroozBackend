

$(document).ready(function(){

	var test = function(){
		return 3+3; 
	}

	var ex='{"employees": [' + 
			'{"name":"Shaniqua","gender":"female","species":"cat"},' + 
			'{"name":"Gatsby","gender":"male","species":"cat"}] }';
	var sample= JSON.parse(ex);
	console.log(sample.employees[0].name);
	$("#main").text(sample.employees[0].name);


});