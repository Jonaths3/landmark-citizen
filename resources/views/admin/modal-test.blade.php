</html>
<!DOCTYPE html>
<html>
<head>
  <title>testing</title>
  <link href="http://smappsgroup.com/pay/public/css/checkout.css" rel="stylesheet" type="text/css" />
  <style type="text/css">

  
</style>
</head>
<body>
<div>
<input type="text" id="contact_email" placeholder="email">
<input type="text" id="amount" placeholder="amount">
<br>
</div>
<input type="hidden" id="tranx_ref" value="EWRT56">
<input type="hidden" id="close-url" close-url="smapps-pay">
<input type="hidden" id="success-url" success-url="smapps-pay">

<button id="pay-btn" onclick="checkOut();" class="button">Pay</button>
<div id="payWithLothus"><div>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="http://smappsgroup.com/pay/public/js/checkout.js"></script>
</body>
</html>