<?php
include 'connection.php'; // Include your connection file

$payment_data = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $payment_id = isset($_GET['payment_id']) ? $_GET['payment_id'] : '';
    $booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : '';

    if ($payment_id || $booking_id) {
        $sql = "SELECT * FROM Payment WHERE payment_id = ? OR booking_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $payment_id, $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $payment_data = $result->fetch_assoc();
        } else {
            echo "No record found";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_id = isset($_POST['p_id']) ? $_POST['p_id'] : '';
    $booking_id = isset($_POST['b_id']) ? $_POST['b_id'] : '';
    $amount = isset($_POST['amount']) ? $_POST['amount'] : '';

    if (!empty($payment_id) && !empty($booking_id) && !empty($amount)) {
        $date = date('Y-m-d'); // Current date
        $status = 'Refunded'; // Default status for refund

        // Fetch the seat_id from Booking table
        $sql = "SELECT seat_id FROM Booking WHERE booking_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking_data = $result->fetch_assoc();

        if ($booking_data) {
            $seat_id = $booking_data['seat_id'];

            // Fetch the departure date using seat_id
            $sql = "SELECT departure_date FROM Seats WHERE seat_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $seat_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $seat_data = $result->fetch_assoc();

            if ($seat_data) {
                $departure_date = $seat_data['departure_date'];
                $current_date = new DateTime();
                $departure_date = new DateTime($departure_date);
                $interval = $current_date->diff($departure_date);

                // Check if the cancellation is 3 days before departure
                if ($interval->days >= 3) {
                    // Full refund
                    $refund_amount = $amount;
                } else {
                    // 50% deduction
                    $refund_amount = $amount * 0.5;
                }

                // Insert into Refund table
                $sql = "INSERT INTO Refund (payment_id, amount, status, date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("idss", $payment_id, $refund_amount, $status, $date);

                if ($stmt->execute()) {
                    // Update payment status to Refunded
                    $sql = "UPDATE Payment SET p_status = ? WHERE payment_id = ?";
                    $stmt_update = $conn->prepare($sql);
                    $stmt_update->bind_param("si", $status, $payment_id);
                    if (!$stmt_update->execute()) {
                        echo "Error updating payment status: " . $stmt_update->error;
                        exit;
                    }

                    // Update passenger's wallet
                    $update_wallet = "UPDATE Passenger SET wallet = wallet + ? WHERE passenger_id = (SELECT passenger_id FROM Payment WHERE payment_id = ?)";
                    $stmt_update_wallet = $conn->prepare($update_wallet);
                    $stmt_update_wallet->bind_param("di", $refund_amount, $payment_id);
                    if (!$stmt_update_wallet->execute()) {
                        echo "Error updating wallet: " . $stmt_update_wallet->error;
                        exit;
                    }

                    // Respond with success and show popup
                    echo "<script>
                            window.onload = function() {
                                document.getElementById('popup').classList.remove('hidden');
                            };
                          </script>";
                } else {
                    echo "Error inserting refund: " . $stmt->error;
                }
            } else {
                echo "Seat details not found";
            }
        } else {
            echo "Booking details not found";
        }
    } else {
        echo "All fields are required!";
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Refund Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div id="Refund" class="bg-indigo-600 p-8 rounded-lg shadow-lg max-w-md w-full text-white">
        <div id="header" class="text-center mb-4">
            <h1 class="text-2xl font-bold">Refund</h1>
        </div>
        <h4 class="mb-4">Please fill the form to proceed with the refund process</h4>
        <form action="" method="post" id="form" class="space-y-4">
            <div class="flex justify-between">
                <label for="p_id" class="block">Payment ID</label>
                <label for="b_id" class="block">Booking ID</label>
            </div>
            <div class="flex justify-between space-x-4">
                <input type="text" name="p_id" id="p_id" value="<?php echo htmlspecialchars($payment_data['payment_id'] ?? ''); ?>" readonly class="p-2 rounded-md text-black w-1/2" />
                <input type="text" name="b_id" id="b_id" value="<?php echo htmlspecialchars($payment_data['booking_id'] ?? ''); ?>" readonly class="p-2 rounded-md text-black w-1/2" />
            </div>

            <div class="flex justify-between mt-4">
                <label for="amount" class="block">Paid Amount</label>
                <label for="date" class="block">Date</label>
            </div>
            <div class="flex justify-between space-x-4">
                <input type="number" name="amount" id="amount" value="<?php echo htmlspecialchars($payment_data['amount'] ?? ''); ?>" readonly class="p-2 rounded-md text-black w-1/2" />
                <input type="date" name="date" id="date" value="<?php echo date('Y-m-d'); ?>" readonly class="p-2 rounded-md text-black w-1/2" />
            </div>

            <label for="status" class="block mt-4">Status</label>
            <input type="text" name="status" id="status" value="Pending" readonly class="p-2 rounded-md text-black w-full" />

            <p id="para" class="mt-4">Do you really want to proceed with the refund process? A 30% cancellation charge will be deducted from the total fare. Confirm to proceed.</p>

            <input type="submit" name="submit" id="submit" value="Confirm" class="mt-4 px-4 py-2 font-bold space-x-2 bg-blue-500 text-white p-2 rounded-md hover:bg-blue-700 cursor-pointer" />
        </form>
    </div>

    <div id="popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-5 border border-gray-300 rounded-md text-center w-full max-w-xs">
            <img id="img1" src="images/6.png" alt="Confirmation" class="w-24 h-24 mx-auto rounded-full mb-4" />
            <p class="mb-4">Payment Refund Successfully!</p>
            <a href="Home.php">
                <button class="bg-indigo-600 text-white p-2 capitalize rounded-md hover:bg-indigo-700" onclick="closePopup()">OK</button>
            </a>
        </div>
    </div>

    <script>
        function popup() {
            document.getElementById('popup').classList.remove('hidden');
        }

        function closePopup() {
            document.getElementById('popup').classList.add('hidden');
        }

        document.getElementById('form').addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default form submission

            let formData = new FormData(this);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Check if the response contains a success message
                if (data.includes("Payment Refund Successfully!")) {
                    popup();
                } else {
                    alert(data); // Show error message
                }
            })
            .catch(error => console.error('Error:', error));
        });

        window.onclick = function (event) {
            if (event.target === document.getElementById('popup')) {
                closePopup();
            }
        };
    </script>
</body>
</html>
