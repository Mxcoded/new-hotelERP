<!DOCTYPE html>
<html>
<head>
    <title>Reservation Confirmation</title>
</head>
<body>
    <h1>Reservation Confirmation</h1>

    <p>
        Thank you for making a reservation with us. Below are the details:
    </p>

    <p><strong>Reservation Details:</strong></p>
    <ul>
        <li>Reservation ID: {{ $reservation['id'] }}</li>
        <li>Reservation ID: {{ $reservation['reservation_number'] }}</li>
        <li>Check-in Date: {{ $reservation['check_in_date'] }}</li>
        <li>Check-out Date: {{ $reservation['check_out_date'] }}</li>
        <li>Reservation Date: {{ $reservation['reservation_date'] }}</li>
        <!-- Include other reservation details as needed -->
    </ul>

    <p><strong>Reservation Policies:</strong></p>
    <p>{{ $policies }}</p>

    <p>{{ $customMessage }}</p>

</body>
</html>
