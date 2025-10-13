<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Yönlendirme</title>
</head>
<body onload="document.getElementById('paymentForm').submit();">
<form id="paymentForm" action="https://psp.mps.az/process" method="POST">
    @foreach ($data as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach
</form>
<p>Ödeme sayfasına yönlendiriliyorsunuz...</p>
</body>
</html>
