<?php
session_start();
include 'connection.php'; // Include your database connection file

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch the passenger's ID based on the username
$username = $_SESSION['username'];
$query = "SELECT passenger_id FROM passenger WHERE name = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $passenger_id = $row['passenger_id'];
} else {
    echo "No user found.";
    exit();
}

// Fetch all booking details for the passenger
$query = "SELECT b.booking_id, b.status, b.number_of_seats, ts.departure_date, s.seat_num, t.name AS train_name, t.train_id, ts.source, ts.destination, p.name AS passenger_name, ts.departure_time, ts.arrival_time, c.type, pay.amount, pay.payment_id
          FROM booking b
          JOIN seats s ON b.seat_id = s.seat_id
          JOIN class c ON c.class_id = s.class_id
          JOIN train_schedule ts ON s.TS_ID = ts.TS_ID
          JOIN train t ON ts.train_id = t.train_id
          JOIN passenger p ON b.passenger_id = p.passenger_id
          JOIN payment pay ON b.booking_id = pay.booking_id
          WHERE b.passenger_id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $passenger_id);
$stmt->execute();
$bookings = $stmt->get_result();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        h6{
            font-size: 12px !important;
        }
    </style>
</head>
<body>
    <div class="px-10 py-5 capitalize font-bold flex items-center">
        <a class="" href="Home.php"><i class="fa-solid fa-arrow-left pr-3"></i>Back to Home</a>
    </div>
    
    <div class="ticket-wrapper w-full justify-center items-center mt-10">
        <?php if ($bookings->num_rows > 0): ?>
            <?php while ($row = $bookings->fetch_assoc()):
                $b_status = $row['status'];

                // Compare current date with departure date
                $currentDate = new DateTime();
                $departureDate = new DateTime($row['departure_date']);
                $canCancel = $currentDate < $departureDate;

                // Check payment status
                $payment_status = $row['payment_id'];
                $query_payment = "SELECT p_status FROM payment WHERE payment_id = ?";
                $stmt_payment = $conn->prepare($query_payment);
                if ($stmt_payment === false) {
                    die('Prepare failed: ' . htmlspecialchars($conn->error));
                }
                $stmt_payment->bind_param("i", $payment_status);
                $stmt_payment->execute();
                $result_payment = $stmt_payment->get_result();

                if ($result_payment->num_rows > 0) {
                    $payment_data = $result_payment->fetch_assoc();
                    $status = $payment_data['p_status'];
                } else {
                    $status = 'Unknown';
                }

                if ($status === 'Refunded') {
                    continue;
                }

                if ($b_status === 'Confirmed'): ?>
                    <div class="ticket w-7/12 mx-auto my-4 flex justify-between shadow-2xl border rounded-xl mb-14">
                        <div class="left w-3/4 p-3 flex flex-col">
                            <!-- Ticket Details Code Here -->
                            <div class="left w-3/4 p-3 flex flex-col">
    <div class='class flex justify-between mb-2'>
        <h1 class='font-semibold text-lg'>Railway Reservation</h1>
        <h3 class='font-semibold text-gray-400 text-xl'><?php echo htmlspecialchars($row['type']); ?> Class</h3>
    </div>

    <div class='destination flex justify-between'>
        <div class='train'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>Train</h6>
            <h4 class='font-bold text-lg'><?php echo htmlspecialchars($row['train_name']); ?></h4>
        </div>
        <div class='from'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>From</h6>
            <h4 class='font-bold text-lg'><?php echo htmlspecialchars($row['source']); ?></h4>
        </div>
        <div class='to'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>To</h6>
            <h4 class='font-bold text-lg'><?php echo htmlspecialchars($row['destination']); ?></h4>
        </div>
    </div>

    <div class='passenger flex justify-between mt-3'>
        <div class='name'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>Passenger</h6>
            <h4 class='font-bold text-lg capitalize'><?php echo htmlspecialchars($row['passenger_name']); ?></h4>
        </div>
        <div class='fare'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>Total Fare</h6>
            <h4 class='font-bold text-lg'>Rs. <?php echo htmlspecialchars($row['amount']); ?></h4>
        </div>
        <div class='board-time'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>Board Time</h6>
            <h4 class='font-bold text-lg'><?php echo htmlspecialchars($row['departure_time']); ?></h4>
        </div>
    </div>

    <div class='departure-arrival mt-3 flex justify-between'>
        <div class='departure'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>Departure</h6>
            <h4 class='font-bold text-lg'><?php echo htmlspecialchars($row['departure_date']); ?></h4>
            <h5 class='font-bold text-lg'><?php echo htmlspecialchars($row['departure_time']); ?></h5> 
        </div>
        <div class='arrival'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>Arrival</h6>
            <h4 class='font-bold text-lg'><?php echo htmlspecialchars($row['departure_date']); ?></h4>
            <h5 class='font-bold text-lg'><?php echo htmlspecialchars($row['arrival_time']); ?></h5> 
        </div>
        <div class='train-no'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>Train No</h6>
            <h4 class='font-bold text-lg'><?php echo htmlspecialchars($row['train_id']); ?></h4>
        </div>
        <div class='seat-no'>
            <h6 class='text-gray-300 text-sm font-semibold mb-2'>Seat No</h6>
            <h4 class='font-bold text-lg'><?php echo htmlspecialchars($row['seat_num']); ?></h4>
        </div>
    </div>
</div>

                        </div>
                        <div class="right capitalize flex flex-col justify-center items-center bg-blue-500 rounded-l-none rounded-xl text-white p-3">
                            <h1 class="text-lg font-bold mt-0">Railway Reservation</h1>
                            <img src="images/train.png" alt="image" height="90%" width="90%">
                            <p class="text-center text-sm font-semibold">Thank you for choosing us</p>
                            <?php if ($canCancel && $status !== 'Refunded'): ?>
                                <form action="paymentRefund.php" method="get" class="mt-4">
                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($row['booking_id']); ?>">
                                    <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($row['payment_id']); ?>">
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Cancel Ticket
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($b_status === 'Rejected'): ?>
                    <div class="ticket w-7/12 mx-auto my-4 flex justify-between shadow-2xl border rounded-xl mb-14">
                        <div class="left w-3/4 p-3 flex flex-col">
                            <h3 class="font-semibold text-red-500 text-xl">Your booking has been rejected.</h3>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="ticket w-7/12 mx-auto my-4 flex justify-between shadow-2xl border rounded-xl mb-14">
                        <div class="left w-3/4 p-3 flex flex-col">
                            <h3 class="font-semibold text-orange-500 text-xl">Your booking is not confirmed yet.</h3>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-lg">No bookings found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
