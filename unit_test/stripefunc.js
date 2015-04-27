var stripeResponseHandler = function(status, response){
	  var form = $('#payment-form');

  if (response.error) {
    // Show the errors on the form
    form.find('.payment-errors').text(response.error.message);
    form.find('button').prop('disabled', false);
  } else {
    // token contains id, last4, and card type
    var token = response.id;
    // Insert the token into the form so it gets submitted to the server
    $form.append($('<input type="hidden" name="stripeToken" />').val(token));
    // and submit
    $form.get(0).submit();
}

function check_valid{

}

jQuery(function($){
	$('#payment-form').submit(function(event){

		var form = $(this);

		//disable submit button to have repeat clicks
		$form.find('button').prop('disbabled',true);

		//do some card validation and determine if card is valid
		if (check_valid(form)){
			//create stripe response token 
			Stripe.card.createToken($form, stripeResponseHandler);
		}else{
			form.find('')
		}

		//prevent form from submitting with default action
		return false; 

	});
});

