<?php
session_start();
include 'connection.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_unset(); // Clear all session variables
    session_destroy(); // Destroy the session
    header('Location: Home.php'); // Redirect to the same page to refresh the header
    exit();
}

// Get source and destination from session
$source = isset($_SESSION['source']) ? $_SESSION['source'] : 'Not Set';
$destination = isset($_SESSION['destination']) ? $_SESSION['destination'] : 'Not Set';
// $departure_date = isset($_SESSION['departure_date']) ? $_SESSION['departure_date'] : 'Not Set';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_train'])) {
    // Split train_id, fare, and ts_id
    list($selected_train_id, $selected_fare, $selected_ts_id) = explode('|', $_POST['selected_train']);
    
    // Store selected train, fare, and ts_id in the session
    $_SESSION['selected_train_id'] = $selected_train_id;
    $_SESSION['selected_fare'] = $selected_fare;
    $_SESSION['selected_ts_id'] = $selected_ts_id;

    // Redirect to the payment page
    header('Location: Payment.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Train Schedule</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet" />
    <style>
        table {
            margin-top: 100px;
            padding: 8px;
        }
        table, tr, td {
            border: 1px solid black;
        }
        tr, td {
            padding: 8px;
        }
    </style>
</head>
<body>
    <!-- header -->
    <header class="text-gray-600 body-font">
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

    <!-- Schedule-Banner -->
    <section class="text-gray-600 body-font bg-gray-200 w-full flex justify-center items-center">
        <div class="container flex px-5 py-6 md:flex-row flex-col justify-center items-center">
            <div class="lg:flex-grow md:w-full lg:pr-24 md:pr-16 flex flex-col md:items-start md:text-center mb-16 md:mb-0 items-center justify-center text-center">
                <h1 class="title-font sm:text-4xl text-3xl mb-4 font-medium text-gray-900">
                    Train <span class="text-blue-500">Schedule</span>
                </h1>
                <p class="mb-8 leading-relaxed text-justify">
                    Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                    <br> Eos quae possimus inventore fugit repudiandae? Atque
                    <br> adipisci odio eaque nam molestiae?
                </p>
            </div>
        </div>
    </section>

    <!-- Schedule table -->
    <?php
    // Fetch schedule data from the database with JOIN
    $sql = "SELECT ts.ts_id, ts.train_id, t.name, ts.source, ts.destination, ts.arrival_time, ts.departure_time, ts.departure_date, ts.arrival_date, ts.fare
            FROM train_schedule ts
            JOIN train t ON ts.train_id = t.train_id
            WHERE ts.source = ? AND ts.destination = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $source, $destination);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    ?>

    <div class="table-container min-w-full overflow-x-auto md:p-3 p-5">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <table class="mx-auto overflow-x-auto border-collapse border border-gray-400 bg-white shadow-lg">
                <thead>
                    <tr>
                        <th class="border border-gray-300 px-4 py-2">Select</th>
                        <th class="border border-gray-300 px-4 py-2">Train Number</th>
                        <th class="border border-gray-300 px-4 py-2">Train Name</th>
                        <th class="border border-gray-300 px-4 py-2">Source</th>
                        <th class="border border-gray-300 px-4 py-2">Destination</th>
                        <th class="border border-gray-300 px-4 py-2">Departure</th>
                        <th class="border border-gray-300 px-4 py-2">Arrival</th>
                        <th class="border border-gray-300 px-4 py-2">Departure Date</th>
                        <th class="border border-gray-300 px-4 py-2">Arrival Date</th>
                        <th class="border border-gray-300 px-4 py-2">Fare</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are results
                    if ($result && $result->num_rows > 0) {
                        // Output data for each row
                        while ($row = $result->fetch_assoc()) {
                            $train_id_fare_ts_id = htmlspecialchars($row['train_id']) . '|' . htmlspecialchars($row['fare']) . '|' . htmlspecialchars($row['ts_id']);
                            echo '<tr class="hover:bg-gray-100">';
                            echo '<td class="border border-gray-300 px-4 py-2 text-center"><input type="radio" name="selected_train" value="' . $train_id_fare_ts_id . '" required></td>';
                            echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($row['train_id']) . '</td>';
                            echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($row['name']) . '</td>';
                            echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($row['source']) . '</td>';
                            echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($row['destination']) . '</td>';
                            echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($row['departure_time']) . '</td>';
                            echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($row['arrival_time']) . '</td>';
                            echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($row['departure_date']) . '</td>';
                            echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($row['arrival_date']) . '</td>';
                            echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($row['fare']) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="9" class="border border-gray-300 px-4 py-2 text-center">No schedules available</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <div class="bookNow-btn w-full flex justify-center items-center my-10">
                <button type="submit" class="w-52 py-2 px-4 bg-indigo-600 text-white font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center justify-center">Book Now</button>
            </div>
        </form>
    </div>

    <?php $conn->close(); ?>
    <footer class="text-gray-600 body-font">
    <div class="container px-5 py-24 mx-auto">
        <div class="flex flex-wrap md:text-left text-center order-first">
            <div class="lg:w-1/4 md:w-1/2 w-full px-4">
                <h2 class="title-font font-medium text-gray-900 tracking-widest text-sm mb-3">CATEGORIES</h2>
                <nav class="list-none mb-10">
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">First Link</a>
                    </li>
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">Second Link</a>
                    </li>
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">Third Link</a>
                    </li>
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">Fourth Link</a>
                    </li>
                </nav>
            </div>
            <div class="lg:w-1/4 md:w-1/2 w-full px-4">
                <h2 class="title-font font-medium text-gray-900 tracking-widest text-sm mb-3">CATEGORIES</h2>
                <nav class="list-none mb-10">
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">First Link</a>
                    </li>
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">Second Link</a>
                    </li>
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">Third Link</a>
                    </li>
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">Fourth Link</a>
                    </li>
                </nav>
            </div>
            <div class="lg:w-1/4 md:w-1/2 w-full px-4">
                <h2 class="title-font font-medium text-gray-900 tracking-widest text-sm mb-3">CATEGORIES</h2>
                <nav class="list-none mb-10">
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">First Link</a>
                    </li>
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">Second Link</a>
                    </li>
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">Third Link</a>
                    </li>
                    <li>
                        <a class="text-gray-600 hover:text-gray-800">Fourth Link</a>
                    </li>
                </nav>
            </div>
            <div class="lg:w-1/4 md:w-1/2 w-full px-4">
                <h2 class="title-font font-medium text-gray-900 tracking-widest text-sm mb-3">SUBSCRIBE</h2>
                <div class="flex xl:flex-nowrap md:flex-nowrap lg:flex-wrap flex-wrap justify-center items-end md:justify-start">
                    <div class="relative w-40 sm:w-auto xl:mr-4 lg:mr-0 sm:mr-4 mr-2">
                        <label for="footer-field" class="leading-7 text-sm text-gray-600">Placeholder</label>
                        <input type="text" id="footer-field" name="footer-field" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:bg-transparent focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    </div>
                    <button class="lg:mt-2 xl:mt-0 flex-shrink-0 inline-flex text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">Button</button>
                </div>
                <p class="text-gray-500 text-sm mt-2 md:text-left text-center">Bitters chicharrones fanny pack
                    <br class="lg:block hidden">waistcoat green juice
                </p>
            </div>
        </div>
    </div>
    <div class="bg-gray-100">
        <div class="container px-5 py-6 mx-auto flex items-center sm:flex-row flex-col">
            <a class="flex title-font font-medium items-center md:justify-start justify-center text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="w-10 h-10 text-white p-2 bg-indigo-500 rounded-full" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                </svg>
                <span class="ml-3 text-xl">MIRAN Corporation</span>
            </a>
            <p class="text-sm text-gray-500 sm:ml-6 sm:mt-0 mt-4">© 2024 MIRAN Corporation —
                <a href="https://twitter.com/knyttneve" rel="noopener noreferrer" class="text-gray-600 ml-1" target="_blank">@miran.com</a>
            </p>
            <span class="inline-flex sm:ml-auto sm:mt-0 mt-4 justify-center sm:justify-start">
                <a class="text-gray-500">
                    <svg fill="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="w-5 h-5" viewBox="0 0 24 24">
                        <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path>
                    </svg>
                </a>
                <a class="ml-3 text-gray-500">
                    <svg fill="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="w-5 h-5" viewBox="0 0 24 24">
                        <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path>
                    </svg>
                </a>
                <a class="ml-3 text-gray-500">
                    <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="w-5 h-5" viewBox="0 0 24 24">
                        <rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01"></path>
                    </svg>
                </a>
                <a class="ml-3 text-gray-500">
                    <svg fill="currentColor" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0" class="w-5 h-5" viewBox="0 0 24 24">
                        <path stroke="none" d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"></path>
                        <circle cx="4" cy="4" r="2" stroke="none"></circle>
                    </svg>
                </a>
            </span>
        </div>
    </div>
</footer>
</body>
</html>
