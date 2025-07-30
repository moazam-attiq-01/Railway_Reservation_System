<?php
include 'connection.php'; // Ensure your database connection is included
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$passengers = [];
$train = '';
$passengerID = isset($_POST['removePassenger']) ? intval($_POST['removePassenger']) : 0;

// Fetch passengers with refund status and active booking
$sql = "SELECT p.passenger_ID, p.name 
        FROM Passenger p 
        JOIN Booking b ON p.passenger_ID = b.passenger_ID 
        JOIN Payment pay ON b.booking_ID = pay.booking_ID 
        WHERE pay.p_status = 'Refunded' AND b.status = 'Confirmed'";
$result = $conn->query($sql);

if ($result === false) {
  echo "<script>alert('Query failed: " . $conn->error . "');</script>";
} else if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $passengers[] = $row;
    }
} else {
  echo "<script>alert('No passengers found with refund status and active booking.');</script>";
}

if ($passengerID > 0) {
    // Fetch train information for the selected passenger
    if ($stmt = $conn->prepare("
        SELECT t.name 
        FROM Booking b 
        JOIN Seats s ON b.seat_id = s.seat_id 
        JOIN Train_Schedule ts ON s.TS_ID = ts.TS_ID AND s.departure_date = ts.departure_date 
        JOIN Train t ON ts.train_id = t.train_id 
        WHERE b.passenger_ID = ?
    ")) {
        $stmt->bind_param("i", $passengerID);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $train = $row['name'];
            } else {
                echo "<script>alert('No train found for the passenger.');</script>";
            }
        } else {
            echo "<script>alert('Execute failed: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Prepare failed: (" . $conn->errno . ") " . $conn->error . "');</script>";
    }

    // Remove passenger
    if (isset($_POST['confirmRemoval'])) {
        // Update booking status to 'cancelled'
        if ($stmt = $conn->prepare("UPDATE Booking SET status = 'cancelled' WHERE passenger_ID = ?")) {
            $stmt->bind_param("i", $passengerID);
            if ($stmt->execute()) {
                echo "<script>alert('Passenger booking status updated to cancelled.');</script>";
            } else {
                echo "<script>alert('Update failed: " . $stmt->error . "');</script>";
            }
            $stmt->close();

            // Redirect to refresh the page
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "<script>alert('Prepare failed: (" . $conn->errno . ") " . $conn->error . "');</script>";
        }
    }
}


//view passenger list....

$sqlu = "SELECT passenger_ID, name, email, cnic, wallet FROM Passenger";
$resulti = $conn->query($sqlu);
if ($resulti === false) {
    echo "Error: " . $conn->error;
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
                        <li id="remove-passenger"><a href="#remove" class="block py-2 px-5 rounded hover:bg-blue-700">Remove Passengers</a></li>
                        <li id="view-passenger"><a href="#list" class="block py-2 px-5 rounded hover:bg-blue-700">View Passengers List</a></li>
                        <li class="" id="book-passenger"><a href="admin-booking-request.php" class="block py-2 px-5 rounded hover:bg-blue-700">Booking Requests</a></li>
                        <!-- <li id="request"><a href="#request" class="block py-2 px-5 rounded hover:bg-blue-700">Request</a></li> -->
                        <li id="logout" class=""><a href="Home.php" class="block py-2 px-5 mt-96 rounded hover:bg-blue-700">Logout</a></li>
                    </ul>
                </nav>
            </aside>

            <section class="w-full relative">
                <div class="book-passenger-main-section w-full p-20 absolute hidden">
                    <h2 class="text-2xl font-semibold mb-4">Booking Requests</h2>
                    
                <!-- html for add or reject -->

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

                
                      <!-- HTML Form for removing a passenger -->
                      <div class="remove-passenger-main-section w-full p-20 absolute">
  <h2 class="text-2xl font-semibold mb-4">Remove Passengers</h2>
  <form id="removeForm" method="POST" class="space-y-6 bg-gray-100 p-6 rounded-lg shadow-lg" onsubmit="return confirm('Are you sure you want to remove this passenger?');">
    <div>
        <label for="removeTrain" class="block text-gray-700 font-medium">From Train:</label>
        <input type="text" id="removeTrain" name="removeTrain" readonly class="w-full p-2 px-4 border border-gray-300 rounded-lg bg-white" value="<?php echo isset($train) ? htmlspecialchars($train) : ''; ?>">
    </div>
    <div>
        <label for="removePassenger" class="block text-gray-700 font-medium">Select Passenger:</label>
        <select id="removePassenger" name="removePassenger" class="w-full p-2 px-4 border border-gray-300 rounded-lg bg-white" onchange="this.form.submit()">
            <option value="">Select a passenger</option>
            <?php foreach ($passengers as $passenger): ?>
                <option value="<?php echo htmlspecialchars($passenger['passenger_ID']); ?>" <?php echo isset($passengerID) && $passengerID == $passenger['passenger_ID'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($passenger['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($passengerID > 0 && !empty($train)): ?>
        <button type="submit" name="confirmRemoval" class="bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700">Remove Passenger</button>
    <?php endif; ?>
  </form>
</div>





                <div class="view-passenger-main-section w-full p-20 absolute hidden">
                    <div class="search-passanger view flex justify-between items-center">
                    <h2 class="text-2xl font-semibold mb-4">View Passengers List</h2>
                    
                </div>
                    <!-- Content for viewing passengers list -->
                    <div class="table-container min-w-full overflow-x-auto md:p-3 p-5">
                    <table class="mx-auto overflow-x-auto border-collapse border border-gray-400 bg-white shadow-lg">
        <thead>
            <tr>
                <th class="border border-gray-300 px-4 py-2">Passenger ID</th>
                <th class="border border-gray-300 px-4 py-2">Name</th>
                <th class="border border-gray-300 px-4 py-2">Email</th>
                <th class="border border-gray-300 px-4 py-2">CNIC</th>
                <th class="border border-gray-300 px-4 py-2">Wallet</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($resulti->num_rows > 0) {
                // Output data of each row
                while($row = $resulti->fetch_assoc()) {
                    echo "<tr class='hover:bg-gray-100'>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $row["passenger_ID"]. "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $row["name"] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $row["email"] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $row["cnic"] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $row["wallet"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='border border-gray-300 px-4 py-2 text-center'>No passengers found</td></tr>";
            }
            ?>
        </tbody>
    </table>
                        <!-- <div class="bookNow-btn w-full flex justify-center items-center my-10">
                          <a
                            href="http://127.0.0.1:5500/Payment.html"
                            class="w-52 py-2 px-4 bg-indigo-600 text-white font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center justify-center">Book Now</a>
                        </div> -->
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
