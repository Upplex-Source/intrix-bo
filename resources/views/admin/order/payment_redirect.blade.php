<?php 
    $payment_data = $data['payment_data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Payment</title>
</head>
<body onload="document.getElementById('paymentForm').submit();">
    <p>Redirecting to payment gateway...</p>
    <form id="paymentForm" action="https://payment.ipay88.com.my/epayment/entry.asp" method="POST">
        @foreach($payment_data as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <noscript>
            <button type="submit">Click here if you are not redirected</button>
        </noscript>
    </form>
</body>
</html>
