<?php
session_start();
include 'connection.php'; // Include your database connection file

// Handle logout
if (isset($_GET['logout'])) {
    session_unset(); // Clear all session variables
    session_destroy(); // Destroy the session
    header('Location: Home.php'); // Redirect to the home page
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>
        alert('Please log in to continue.');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Ensure class type is set in session
if (!isset($_SESSION['class'])) {
    echo "<script>
        alert('Class type was not selected.');
        window.location.href = 'Home.php'; // Redirect to class selection or relevant page
    </script>";
    exit();
}

// Generate random seat number and store in session if not already set
if (!isset($_SESSION['seat_num'])) {
    $_SESSION['seat_num'] = generateRandomSeatNum($_SESSION['class']); // Pass class type to function
}

// Function to generate a random seat number with class type prefix
function generateRandomSeatNum($class) {
    // Use the first letter of class type as the prefix
    $seatPrefix = strtoupper(substr($class, 0, 1)); // Convert to uppercase
    $seatNumber = rand(1, 100); // Random number between 1 and 100
    return $seatPrefix . str_pad($seatNumber, 3, '0', STR_PAD_LEFT); // Format as A001, B002, etc.
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch session data
    $seat_num = $_SESSION['seat_num']; // Get the seat number from session
    $fare = $_SESSION['selected_fare'];
    $ts_id = $_SESSION['selected_ts_id'];
    $num_of_seats = $_SESSION['num_of_seats']; // Assume this value is set in the session
    $class_type = $_SESSION['class']; // Class type stored in session

    // Fetch the passenger ID using the logged-in user's name
    $username = $_SESSION['username'];
    $query = "SELECT passenger_id FROM passenger WHERE name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $passenger_id = $row['passenger_id'];

        // Fetch the class fare based on the class type
        $classQuery = "SELECT fare FROM class WHERE type = ?";
        $stmtClass = $conn->prepare($classQuery);
        $stmtClass->bind_param("s", $class_type);
        $stmtClass->execute();
        $resultClass = $stmtClass->get_result();

        // Fetch departure date based on ts_id
        $d_dateQuery = "SELECT departure_date FROM train_schedule WHERE TS_ID = ?";
        $stmtDDate = $conn->prepare($d_dateQuery);
        $stmtDDate->bind_param("i", $ts_id);
        $stmtDDate->execute();
        $resultDDate = $stmtDDate->get_result();

        if ($resultClass->num_rows > 0 && $resultDDate->num_rows > 0) {
            $rowClass = $resultClass->fetch_assoc();
            $class_fare = (int)$rowClass['fare'];

            $rowDDate = $resultDDate->fetch_assoc();
            $departure_date = $rowDDate['departure_date'];

            // Calculate total fare
            $class_fare_int = (int)$class_fare;
            $fare_int = (int)$fare;
            $total_fare = ($fare_int + $class_fare_int) * (int)$num_of_seats;

            // Insert seat information into the table
            $insertSeatQuery = "INSERT INTO seats (seat_num, status, class_id, TS_ID, departure_date) VALUES (?, 'Booked', (SELECT class_id FROM class WHERE type = ?), ?, ?)";
            $stmtSeat = $conn->prepare($insertSeatQuery);
            $stmtSeat->bind_param("ssis", $seat_num, $class_type, $ts_id, $departure_date);
            $stmtSeat->execute();
            $seat_id = $stmtSeat->insert_id;

            // Insert into bookings table
            $insertBookingSQL = "INSERT INTO booking (seat_id, passenger_id, number_of_seats) VALUES (?, ?, ?)";
            $stmt2 = $conn->prepare($insertBookingSQL);
            $stmt2->bind_param("iii", $seat_id, $passenger_id, $num_of_seats);

            if ($stmt2->execute()) {
                // Get the last inserted booking ID
                $booking_id = $stmt2->insert_id;
                $_SESSION['booking_id'] = $booking_id;
                $_SESSION['total_fare'] = $total_fare;

                // Complete the payment process
                $status = 'Paid';
                $method = 'Card';
                $insertPaymentSQL = "INSERT INTO payment (booking_id, amount, date, status, method) VALUES (?, ?, ?, ?, ?)";
                $stmt3 = $conn->prepare($insertPaymentSQL);
                $stmt3->bind_param("idsss", $booking_id, $total_fare, $departure_date, $status, $method);

                if ($stmt3->execute()) {
                    // Redirect immediately after successful payment
                    header("Location: Ticket.php");
                    exit();
                } else {
                    echo "Payment failed: " . $stmt3->error;
                }
                
            } else {
                echo "Booking failed: " . $stmt2->error;
            }

            $stmt2->close();
            $stmt3->close();
        } else {
            echo "Class fare or departure date not found.";
        }

        $stmtClass->close();
        $stmtDDate->close();
    } else {
        echo "Passenger not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Jazzcash</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-200 flex items-center justify-center min-h-screen">
    
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <img src="images/7.png" alt="JazzCash" class="w-32 h-32 mx-auto rounded-full shadow-md mb-4">
            <h3 class="text-xl font-semibold">Ticket Payment</h3>
        </div>
        <form id="paymentForm" class="space-y-4" method="POST" action="">
            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700">Booking Number</label>
                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($_SESSION['booking_id']); ?></h3>
            </div>

            <div>
                <label for="totalFare" class="block text-sm font-medium text-gray-700">Total Fare</label>
                <h3 class="text-lg font-semibold">Rs.<?php echo htmlspecialchars(number_format($_SESSION['total_fare'], 2)); ?></h3>
            </div>

            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700">Mobile Account</label>
                <input type="number" id="reference" name="reference" maxlength="11" placeholder="+92-XXX-XXXXXXX" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>

            <div>
                <label for="otp" class="block text-sm font-medium text-gray-700">Enter Six-Digit OTP</label>
                <input type="number" id="otp" name="otp" placeholder="X-X-X-X-X-X" maxlength="6" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>

            <div>
                <input type="submit" value="BOOK YOUR TICKET" class="w-full py-2 px-4 bg-indigo-600 text-white font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
            </div>
        </form>
    </div>
</body>
</html>
