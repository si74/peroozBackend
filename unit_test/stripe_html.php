<?php

include('../config.php');
include('../model/stripepayment.php');
include('../db/mysqldb.php');
include('../stripe-php-master/lib/Stripe.php');

?>

<html>
	<head>
		<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
		<script type="text/javascript">
			Stripe.setPublishableKey('pk_test_k9KXrshKlgdTk4CKLagariDD');
		</script>
		<script type="text/javascript" src="https://dev.perooz.io/js/jquery_1.11.0.js"></script>
		<script type="test/javascript" src="https://dev.perooz.io/unit_test/stripefunc.js"></script>
	</head>
	<body>
		<div id='title'>Stripe Payment Test Form</div><br/>
		<form action="" method="POST" id="payment-form">
		  <span class="payment-errors"></span>
		  
		  <div class="form-row">
		  	<label>
		  		<span>Name</span>
		  		<input type="text" size="40" />
		  	</label>
		  </div> 

		  <div class="form-row">
		    <label>
		      <span>Card Number</span>
		      <input type="text" size="20" data-stripe="number"/>
		    </label>
		  </div>

		  <div class="form-row">
		    <label>
		      <span>CVC</span>
		      <input type="text" size="4" data-stripe="cvc"/>
		    </label>
		  </div>

		  <div class="form-row">
		    <label>
		      <span>Expiration (MM/YYYY)</span>
		      <input type="text" size="2" data-stripe="exp-month"/>
		    </label>
		    <span> / </span>
		    <input type="text" size="4" data-stripe="exp-year"/>
		  </div>

		  <button type="submit">Submit Payment</button>
		</form>
	</body>
</html>
