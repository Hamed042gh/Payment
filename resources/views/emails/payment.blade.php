<!DOCTYPE html>
<html>
<head>
    <title>Payment Notification</title>
</head>
<body>
    <h1>Payment Received</h1>
    <p>Dear <strong>{{ $payment->user->name }},</strong></p>
    <p>We have received your payment of <strong>{{ $payment->amount }}.</strong></p>
    <p>Thank you for your purchase!</p>
    <p>Best regards,</p>
    <p>Shetabit</p>
</body>
</html>