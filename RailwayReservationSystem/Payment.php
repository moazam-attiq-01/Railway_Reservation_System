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

            // Insert seat information into the table
            $insertSeatQuery = "INSERT INTO seats (seat_num, status, class_id, TS_ID, departure_date) VALUES (?, 'Booked', (SELECT class_id FROM class WHERE type = ?), ?, ?)";
            $stmtSeat = $conn->prepare($insertSeatQuery);
            $stmtSeat->bind_param("ssis", $seat_num, $class_type, $ts_id, $departure_date);
            $stmtSeat->execute();
            $seat_id = $stmtSeat->insert_id;

            // Calculate total fare
            $class_fare_int = (int)$class_fare;
            $fare_int = (int)$fare;
            $total_fare = ($fare_int + $class_fare_int) * (int)$num_of_seats;
            // Store total fare in session
            $_SESSION['total_fare'] = $total_fare;


         // Fetch the payment method from the form submission
         $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Unknown'; // Default to 'Unknown' if not set

         // Insert into bookings table
         $insertBookingSQL = "INSERT INTO booking (seat_id, passenger_id, number_of_seats,status) VALUES (?, ?, ?,'Pending')";
         $stmt2 = $conn->prepare($insertBookingSQL);
         $stmt2->bind_param("iii", $seat_id, $passenger_id, $num_of_seats);

         if ($stmt2->execute()) {
             // Get the last inserted booking ID
             $booking_id = $stmt2->insert_id;

             // Complete the payment process
             $status = 'Paid';
             $insertPaymentSQL = "INSERT INTO payment (booking_id, amount, date, p_status, method) VALUES (?, ?, ?, ?, ?)";
             $stmt3 = $conn->prepare($insertPaymentSQL);
             $stmt3->bind_param("idsss", $booking_id, $total_fare, $departure_date, $status, $payment_method);
             $_SESSION['booking_id'] = $booking_id;

                if ($stmt3->execute()) {
                     // Update wallet balance
                     $updateWalletSQL = "UPDATE passenger SET wallet = wallet - ? WHERE passenger_id = ?";
                     $stmt4 = $conn->prepare($updateWalletSQL);
                     $stmt4->bind_param("di", $total_fare, $passenger_id);
 
                     if ($stmt4->execute()) {
                         // Redirect immediately after successful payment
                         echo "<script>
                             alert('Your payment was successful! Your wallet has been updated.');
                             window.location.href = 'pending.html';
                         </script>";
                     } else {
                         echo "Wallet update failed: " . $stmt4->error;
                     }
                     $stmt4->close();
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
    <title>Payment Card</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="font-sans bg-gray-200 p-8">

    <!-- header -->
    <header class="text-gray-600 body-font ">
        <div class="container mx-auto flex flex-wrap p-5 flex-col md:flex-row items-center">
          <a class="flex title-font font-medium items-center text-gray-900 mb-4 md:mb-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="w-10 h-10 text-white p-2 bg-indigo-500 rounded-full" viewBox="0 0 24 24">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
            </svg>
            <span class="ml-3 text-xl">Railway Reservation System</span>
          </a>
          <nav class="md:ml-auto flex flex-wrap items-center text-base justify-center capitalize">
                <a class="mr-5 hover:text-gray-900" href="Home.php">Home</a>
                <?php if (isset($_SESSION['username'])): ?>
                    <span class="mr-5">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a class="mr-5 hover:text-gray-900" href="?logout=true">Logout</a>
                <?php else: ?>
                    <a class="mr-5 hover:text-gray-900" href="login.php">Sign Up/Login</a>
                <?php endif; ?>
            </nav>
        </div>
      </header>
    
    <!-- paymentForm -->

    <div class="flex justify-center my-16">
        <div class="w-full max-w-4xl bg-white p-8 rounded-lg shadow-lg">
            <form id="paymentForm" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-xl font-semibold mb-4">Billing Form</h3>
                        <label for="fname" class="block text-gray-700 font-medium">Full Name</label>
                        <input type="text" name="firstname" id="fname" placeholder="Full Name" required class="w-full p-2 border border-gray-300 rounded-lg mb-4">

                        <label for="email" class="block text-gray-700 font-medium">Email Address</label>
                        <input type="email" name="email" id="email" placeholder="abc123@xyz.com" required class="w-full p-2 border border-gray-300 rounded-lg mb-4">

                    
                                                    
                        <label for="city" class="block text-gray-700 font-medium">Payment Method:</label>
                        <div class="mt-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="payment_method" value="Jazz Cash" class="form-radio">
                            <span class="ml-2">Jazz Cash</span>
                        </label>
                    <label class="inline-flex items-center ml-4">
                            <input type="radio" name="payment_method" value="Bank" class="form-radio">
                            <span class="ml-2">Bank</span>
                        </label>
                    </div>

                        <div class="grid grid-cols-2 gap-4">
                           
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-4">Payment</h3>
                        <label for="fname" class="block text-gray-700 font-medium">Accepted Cards</label>
                        <div class="flex space-x-4 mb-4">
                            <img src="images/4.png" alt="Card Image 1" class="h-9 w-auto">
                            <img src="images/5.jpeg" alt="Card Image 2" class="h-9 w-auto">
                        </div>

                        <label for="cname" class="block text-gray-700 font-medium">Card Holder Name</label>
                        <input type="text" name="cardname" id="cname" placeholder="Card Holder Name" required class="w-full p-2 border border-gray-300 rounded-lg mb-4">

                        <label for="ccnum" class="block text-gray-700 font-medium">Card Number</label>
                        <input type="text" name="cardnumber" id="ccnum" placeholder="1111-2222-3333-4444" required class="w-full p-2 border border-gray-300 rounded-lg mb-4">

            
                        <div class="grid grid-cols-2 gap-4">
                        </div>
                    </div>
                </div>

                <div class="flex justify-center mt-6">
                    <button type="submit" class="bg-indigo-600 text-white py-3 px-8 rounded-lg hover:bg-indigo-700">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- popup -->
    <div id="popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden w-full h-full">
        <div class="bg-white px-15 border border-gray-300 rounded-md text-center w-96 h-96 flex justify-center items-center flex-col">
            <img id="img1" src="images/6.png" alt="" class="w-20 h-20 mx-auto rounded-full mb-4">
            <p class="mb-4 font-bold text-2xl py-5">Your ticket is confirmed!</p>
            <button class="bg-indigo-600 text-white p-4 rounded-md hover:bg-indigo-700" onclick="closePopup()">Show Receipt</button>
        </div>
    </div>

    <!-- footer -->
    <footer class="text-gray-600 body-font">
        <div class="container px-5 py-8 mx-auto flex items-center sm:flex-row flex-col">
            <p class="text-sm text-gray-500 sm:ml-6 sm:mt-0 mt-4">© 2024 Railway Reservation System</p>
            <span class="inline-flex sm:ml-auto sm:mt-0 mt-4 justify-center sm:justify-start">
                <a class="text-gray-500" href="#">
                    <i class="fa fa-facebook"></i>
                </a>
                <a class="ml-3 text-gray-500" href="#">
                    <i class="fa fa-twitter"></i>
                </a>
                <a class="ml-3 text-gray-500" href="#">
                    <i class="fa fa-linkedin"></i>
                </a>
            </span>
        </div>
    </footer>

    <script>
        function openPopup() {
            document.getElementById("popup").classList.remove("hidden");
            setTimeout(function(){
                window.location.href = 'pending.html'; // Automatically redirect after a delay
            }, 3000); // Delay in milliseconds
        }

        function closePopup() {
            document.getElementById("popup").classList.add("hidden");
            window.location.href = 'pending.html'; // Redirect immediately when popup is closed
        }

    </script>
</body>
</html>
