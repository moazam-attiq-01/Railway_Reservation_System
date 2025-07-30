<?php
session_start();
include 'connection.php'; // Include your database connection file

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch the ticket information from the database
$username = $_SESSION['username'];
$booking_id = $_SESSION['booking_id']; // Ensure booking_id is stored in session

// Fetch booking details along with payment amount
$query = "SELECT b.number_of_seats, ts.departure_date, s.seat_num, t.name AS train_name, t.train_id, ts.source, ts.destination, p.name AS passenger_name, ts.departure_time, ts.arrival_time, pay.amount
          FROM booking b
          JOIN seats s ON b.seat_id = s.seat_id
          JOIN train_schedule ts ON s.TS_ID = ts.TS_ID
          JOIN train t ON ts.train_id = t.train_id
          JOIN passenger p ON b.passenger_id = p.passenger_id
          JOIN payment pay ON b.booking_id = pay.booking_id
          WHERE b.booking_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $train_name = htmlspecialchars($row['train_name']);
    $from_location = htmlspecialchars($row['source']);
    $to_location = htmlspecialchars($row['destination']);
    $passenger_name = htmlspecialchars($row['passenger_name']);
    $departure_date = htmlspecialchars($row['departure_date']);
    $departure_time = htmlspecialchars($row['departure_time']);
    $arrival_time = htmlspecialchars($row['arrival_time']);
    $train_no = htmlspecialchars($row['train_id']);
    $seat_num = htmlspecialchars($row['seat_num']);
    $amount = htmlspecialchars($row['amount']); // Fetch the amount from payment
    $class_type = $_SESSION['class']; // Class type stored in session
} else {
    echo "No booking found.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        h6{
            font-size: 12px !important;
        }
    </style>
</head>
<body>

    <div class="ticket-wrapper uppercase w-full justify-center items-center">
        <div class="ticket w-7/12 flex mx-auto justify-start pr-20 mt-20"> 
            <a class="flex justify-center items-center font-bold" href="Home.php"><i class="fa-solid fa-arrow-left pr-3"></i>back to home</a>
        </div>
        <div class="ticket w-7/12 mx-auto my-auto flex justify-between mt-40 shadow-2xl border rounded-xl">
            <div class="left w-3/4 p-3 flex flex-col">
                <div class="class flex justify-between mb-2">
                    <h1 class="font-semibold text-lg">Railway Reservation</h1>
                    <h3 class="font-semibold text-gray-400 text-xl"><?php echo $class_type; ?> Class</h3>
                </div>
                <div class="destination flex justify-between">
                    <div class="train">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">Train</h6>
                        <h4 class="font-bold text-lg"><?php echo $train_name; ?></h4>
                    </div>
                    <div class="from">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">From</h6>
                        <h4 class="font-bold text-lg"><?php echo $from_location; ?></h4>
                    </div>
                    <div class="to">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">To</h6>
                        <h4 class="font-bold text-lg"><?php echo $to_location; ?></h4>
                    </div>
                </div>
                <div class="passenger flex justify-between mt-3">
                    <div class="name">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">Passenger</h6>
                        <h4 class="font-bold text-lg"><?php echo $passenger_name; ?></h4>
                    </div>
                    <div class="fare">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">Total Fare</h6>
                        <h4 class="font-bold text-lg"><?php echo $amount; ?></h4>
                    </div>
                    <div class="board-time">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">Board Time</h6>
                        <h4 class="font-bold text-lg"><?php echo $departure_time; ?></h4>
                    </div>
                </div>
                <div class="departure-arrival mt-3 flex justify-between">
                    <div class="departure">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">Departure</h6>
                        <h4 class="font-bold text-lg"><?php echo $departure_date; ?></h4>
                        <h5 class="font-bold text-lg"><?php echo $departure_time; ?></h5> 
                    </div>
                    <div class="arrival">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">Arrival</h6>
                        <h4 class="font-bold text-lg"><?php echo $departure_date; ?></h4>
                        <h5 class="font-bold text-lg"><?php echo $arrival_time; ?></h5> 
                    </div>
                    <div class="train-no">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">Train No</h6>
                        <h4 class="font-bold text-lg"><?php echo $train_no; ?></h4>
                    </div>
                    <div class="seat-no">
                        <h6 class="text-gray-300 text-sm font-semibold mb-2">Seat No</h6>
                        <h4 class="font-bold text-lg"><?php echo $seat_num; ?></h4>
                    </div>
                </div>
            </div>
            <div class="right capitalize flex flex-col justify-center items-center bg-blue-500 rounded-l-none rounded-xl text-white p-3">
                <h1 class="text-lg font-bold mt-0">Railway Reservation</h1>
                <img src="images/train.png" alt="image" height="90%" width="90%">
                <p class="text-center text-sm font-semibold">Thank you for choosing us <br> Please arrive at the station at departure time</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js" integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html>
