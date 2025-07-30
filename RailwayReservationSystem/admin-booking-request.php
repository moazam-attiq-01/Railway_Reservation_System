<?php
include 'connection.php'; // Ensure your database connection is included

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$passengers = [];
$train = '';
$passengerID = isset($_POST['removePassenger']) ? intval($_POST['removePassenger']) : 0;

// Noor Ka code

$sqln = "SELECT b.booking_id, b.number_of_seats, b.seat_id, b.passenger_ID, b.status, p.payment_id, p.amount,p.p_status 
        FROM booking b 
        JOIN payment p ON b.booking_id = p.booking_id 
        WHERE b.status = 'Pending'";

$resultn = $conn->query($sqln);

// Check if the query executed successfully
if ($resultn === false) {
    echo "Error executing query: " . $conn->error;
    exit;
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        // Get the booking ID and payment ID from the form
        $bookingId = $_POST['booking_id'];
        $paymentId =$_POST['payment_id'];

        // Update the booking and payment status in the database
        $updateBookingSql = "UPDATE booking SET status = 'Confirmed' WHERE booking_id = ?";
        $stmt1 = $conn->prepare($updateBookingSql);
        $status=$_SESSION['status'];
        $stmt1->bind_param("i", $bookingId);

        $updatePaymentSql2 = "UPDATE payment SET p_status = 'Completed' WHERE payment_id = ?";
        $stmt2 = $conn->prepare($updatePaymentSql2);
        $stmt2->bind_param("i", $paymentId);

        if ($stmt1->execute() && $stmt2->execute()) {
            echo "Booking confirmed and payment completed successfully!";
              if($status==='Confirmed'){
              header('Location: Ticket.php');
              exit();
              }
        } else {
            echo "Error confirming booking or completing payment: " . $conn->error;
        }

        $stmt1->close();
        $stmt2->close();
    } elseif (isset($_POST['reject'])) {
        // Get the booking ID and payment ID from the form
        $bookingId = $_POST['booking_id'];
        $paymentId = $_SESSION['payment_id'];

        // Update the booking and payment status in the database
        $updateBookingSql = "UPDATE booking SET status = 'Rejected' WHERE booking_id = ?";
        $stmt1 = $conn->prepare($updateBookingSql);
        $stmt1->bind_param("i", $bookingId);

        $updatePaymentSql2 = "UPDATE payment SET p_status = 'Cancelled' WHERE payment_id = ?";
        $stmt2 = $conn->prepare($updatePaymentSql2);
        $stmt2->bind_param("i", $paymentId);

        if ($stmt1->execute() && $stmt2->execute()) {
            echo "Booking rejected and payment cancelled successfully!";
        } else {
            echo "Error rejecting booking or cancelling payment: " . $conn->error;
        }

        $stmt1->close();
        $stmt2->close();
    }

    // Refresh the page to see the updated list
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }

        aside{
            background-color: blue;
            padding: 10px 5px;
        }

        ul{
            list-style: none !important;
        }
        a{
            text-decoration: none;
        }
        option{
            padding: 5px 3px !important;
        }

        #add{
            background-color: rgb(181, 245, 162);
            width: 70px;
        }
        #reject {
            background-color: rgb(218, 117, 117);
            width: 70px;
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="dashboard w-full h-full">
        <div class="flex w-full h-full">
            <aside id="sidebar" class=" w-64 bg-blue-800 text-white p-6 flex-shrink-0">
                <div class="flex pl-0 justify-between items-center">
                    <a href="#"><i class="fa-solid fa-arrow-left"> back to home</i></a>

                    <h1 class="text-xl font-bold">Admin Dashboard</h1>
                </div>
                <nav>
                    <ul class="space-y-1 mt-10">
                        <li id="remove-passenger"><a href="admin-dashboard.php#remove" class="block py-2 px-5 rounded hover:bg-blue-700">Remove Passengers</a></li>
                        <li id="view-passenger"><a href="admin-dashboard.php#list" class="block py-2 px-5 rounded hover:bg-blue-700">View Passengers List</a></li>
                        <li class="" id="book-passenger"><a href="admin-booking-request.php" class="block py-2 px-5 rounded hover:bg-blue-700">Booking Requests</a></li>
                        <!-- <li id="request"><a href="#request" class="block py-2 px-5 rounded hover:bg-blue-700">Request</a></li> -->
                        <li id="logout" class=""><a href="Home.php" class="block py-2 px-5 mt-96 rounded hover:bg-blue-700">Logout</a></li>
                    </ul>
                </nav>
            </aside>

            <section class="w-full relative">

                
                      <!-- HTML Form for removing a passenger -->
                      <div class="remove-passenger-main-section w-full p-20 absolute">
  <h2 class="text-2xl font-semibold mb-4">Booking Requests</h2>
  <div class="request-main-section w-full p-20 absolute">
                    
                    <div class="table-container min-w-full overflow-x-auto md:p-3 p-5">
                        <table class="mx-auto overflow-x-auto border-collapse border border-gray-400 bg-white shadow-lg">
                            <thead>
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2">Booking ID</th>
                                    <th class="border border-gray-300 px-4 py-2">Payment ID</th>
                                    <th class="border border-gray-300 px-4 py-2">Passenger ID</th>
                                    <th class="border border-gray-300 px-4 py-2">Seat ID</th>
                                    <th class="border border-gray-300 px-4 py-2">Number of Seats</th>
                                    <th class="border border-gray-300 px-4 py-2">Status</th>
                                    <th class="border border-gray-300 px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                               <?php
                                if ($resultn->num_rows > 0 ) {
                                    while ($rown = $resultn->fetch_assoc() )  {
                                        echo "<tr>
                                                 <td class='border border-gray-300 px-4 py-2'>" . $rown['booking_id'] . "</td>
                                                 <td class='border border-gray-300 px-4 py-2'>" . $rown['payment_id'] . "</td>
                                                 <td class='border border-gray-300 px-4 py-2'>" . $rown['passenger_ID'] . "</td>
                                                 <td class='border border-gray-300 px-4 py-2'>" . $rown['seat_id'] . "</td>
                                                 <td class='border border-gray-300 px-4 py-2'>" . $rown['number_of_seats'] . "</td>
                                                 <td class='border border-gray-300 px-4 py-2'>" . $rown['status'] . "</td>
                                                 <td class='border border-gray-300 px-4 py-2'>
                                                     <form method='POST' action='' style='display: inline;'>
                                                         <input type='hidden' name='booking_id' value='" . $rown['booking_id'] . "'>
                                                         <input type='submit' name='add' value='Add' id='add'>
                                                     </form>
                                                     <form method='POST' action='' style='display: inline;'>
                                                         <input type='hidden' name='booking_id' value='" . $rown['booking_id'] . "'>
                                                         <input type='submit' name='reject' value='Reject' id='reject'>
                                                     </form>
                                                 </td>
                                               </tr>";
                                     }
                                 } else {
                                     echo "<tr><td colspan='6' class='border border-gray-300 px-4 py-2 text-center'>No pending bookings</td></tr>";
                                 }
                                 ?>
                            </tbody>
                        </table>
                    </div>
                </div> 
</div>

            </section>
        </div>
    </div>

    <script>
        function bookPassengers() {
            alert('Booking logic here');
        }

        function removePassenger() {
            alert('Remove passenger logic here');
        }

        function logout() {
            alert('Logging out');
        }

        function showSection(sectionClass) {
            const sections = document.querySelectorAll('section > div');
            sections.forEach(section => section.classList.add('hidden'));
            document.querySelector(sectionClass).classList.remove('hidden');
        }

        document.getElementById('book-passenger').addEventListener('click', () => {
            showSection('.book-passenger-main-section');
        });

        document.getElementById('remove-passenger').addEventListener('click', () => {
            showSection('.remove-passenger-main-section');
        });

        document.getElementById('view-passenger').addEventListener('click', () => {
            showSection('.view-passenger-main-section');
        });

        document.getElementById('logout').addEventListener('click', logout);
    </script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js" integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html>
