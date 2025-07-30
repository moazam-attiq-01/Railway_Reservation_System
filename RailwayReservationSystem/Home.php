<?php
// Start the session
session_start();
include 'connection.php';


// Handle logout
if (isset($_GET['logout'])) {
    session_unset(); // Clear all session variables
    session_destroy(); // Destroy the session
    header('Location: ' . $_SERVER['PHP_SELF']); // Redirect to the same page to refresh the header
    exit();
}

// Fetch wallet amount for the logged-in passenger
$wallet_amount = 0;

// Debug: Log session data
error_log("Session Data: " . print_r($_SESSION, true));

if (isset($_SESSION['passenger_id'])) {
    $passenger_id = $_SESSION['passenger_id'];
    // Debug: Log the passenger ID being used
    error_log("Passenger ID in Home.php: " . htmlspecialchars($passenger_id));

    $sql = "SELECT wallet FROM passenger WHERE passenger_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $passenger_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $wallet_amount = $row['wallet'];
    } else {
        error_log("No wallet data found for passenger ID: " . htmlspecialchars($passenger_id));
    }

    $stmt->close();
} else {
    error_log("Passenger ID not set in session.");
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs
    $num_of_seats = isset($_POST['noOfSeats']) ? $_POST['noOfSeats'] : '';
    $source = isset($_POST['source']) ? $_POST['source'] : '';
    $destination = isset($_POST['destination']) ? $_POST['destination'] : '';
    $departure_date = isset($_POST['date']) ? date('Y-m-d', strtotime($_POST['date'])) : '';
    $class = isset($_POST['class']) ? $_POST['class'] : '';

    // Validate number of seats
    if ($num_of_seats <= 0) {
        echo "<script>alert('Number of seats must be greater than zero.');</script>";
    } else {
        // Store form data in session variables
        $_SESSION['num_of_seats'] = $num_of_seats;
        $_SESSION['source'] = $source;
        $_SESSION['destination'] = $destination;
        $_SESSION['class'] = $class;

        // Optional: Redirect to another page to prevent form resubmission
        header('Location: Schedule.php');
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Reservation System</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet"/>
</head>
<body class="m-0 p-0">

  <!-- header -->
  <header class="text-gray-600 body-font">
    <div class="container mx-auto flex flex-wrap p-5 flex-col md:flex-row items-center">
      <a class="flex title-font font-medium items-center text-gray-900 mb-4 md:mb-0" href="Home.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="w-10 h-10 text-white p-2 bg-indigo-500 rounded-full" viewBox="0 0 24 24">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
        </svg>
        <span class="ml-3 text-xl">Railway Reservation System</span>
      </a>
      <nav class="md:ml-auto flex flex-wrap items-center text-base justify-center capitalize">
        <a class="mr-5 hover:text-gray-900" href="Home.php">Home</a>
        <?php if (isset($_SESSION['username'])): ?>
          <span class="mr-5">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
          <a class="mr-5 hover:text-gray-900" href="MyTickets.php">My Tickets</a>
          <a class="mr-5 hover:text-gray-900" href="?logout=true">Logout</a>


          <span class="mr-5">Wallet Balance: <?php echo number_format($wallet_amount, 2); ?> PKR</span>


        <?php else: ?>
          <a class="mr-5 hover:text-gray-900" href="login.php">Sign Up/Login</a>
          <a class="mr-5 hover:text-gray-900" href="admin-login.html">Admin Portal</a>
        <?php endif; ?>
      </nav>
    </div>
</header>

     
  <!-- hero section -->
  <section class="text-gray-600 body-font bg-gray-200">
    <div class="container mx-auto flex px-5 py-24 md:flex-row flex-col items-center">
      <div class="lg:flex-grow md:w-1/2 lg:pr-24 md:pr-16 flex flex-col md:items-start md:text-left mb-16 md:mb-0 items-center text-center">
        <h1 class="title-font sm:text-4xl text-3xl mb-4 font-medium text-gray-900">Welcome To
          <br class="hidden lg:inline-block"> <span class="text-blue-500">Railways</span>
        </h1>
        <p class="mb-8 leading-relaxed text-justify">Lorem ipsum dolor, sit amet consectetur adipisicing elit. <br> Eos quae possimus inventore fugit repudiandae? Atque <br>adipisci odio eaque nam molestiae?</p>
        <div class="flex justify-center">
          <img src="" alt="" srcset="">
        </div>
      </div>
    </div>
  </section>

  <!-- form /source & destination -->
  <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full my-20 mx-auto" id="booking-form">
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6 w-full">
      <div>
        <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
        <?php
        // Include database connection file
        include 'connection.php';

        // Fetch source options from the database
        $sql = "SELECT DISTINCT source FROM train_schedule";
        $result = $conn->query($sql);
        ?>

        <select id="source" name="source" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <?php
            // Check if there are results
            if ($result->num_rows > 0) {
                // Output data for each row
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['source']) . '">' . htmlspecialchars($row['source']) . '</option>';
                }
            } else {
                echo '<option>No sources available</option>';
            }
            ?>
        </select>

        <?php
        // Close the database connection
        $conn->close();
        ?>
      </div>

      <div>
        <label for="destination" class="block text-sm font-medium text-gray-700">Destination</label>
        <?php
        // Include database connection file
        include 'connection.php';

        // Fetch destination options from the database
        $sql = "SELECT DISTINCT destination FROM train_schedule";
        $result = $conn->query($sql);
        ?>

        <select id="destination" name="destination" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <?php
            // Check if there are results
            if ($result->num_rows > 0) {
                // Output data for each row
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['destination']) . '">' . htmlspecialchars($row['destination']) . '</option>';
                }
            } else {
                echo '<option>No destinations available</option>';
            }
            ?>
        </select>

        <?php
        // Close the database connection
        $conn->close();
        ?>
      </div>

      <!-- <div>
        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
        <input type="date" id="date" name="date" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
      </div> -->
      <div>
      <label for="noOfSeats" class="block text-sm font-medium text-gray-700 capitalize">Number of Seats</label>
      <input type="number" id="noOfSeats" name="noOfSeats" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" min="1" required>
    </div>
      <div>     
        <h1>Select Class</h1>
        <div class="radio-group">
          <input type="radio" id="business" name="class" value="business">
          <label for="business">Business</label>
        </div>
        <div class="radio-group">
          <input type="radio" id="economy" name="class" value="economy">
          <label for="economy">Economy</label>
        </div>
      </div>

      <div>
        <button type="submit" class="w-full py-2 px-4 bg-indigo-600 text-white font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-center">Search</button>
      </div>
    </form>
  </div>

  <!-- testimonial -->
  <section class="text-gray-600 body-font">
    <h1 class="text-blue-500 py-10 text-center text-2xl font-bold">Testimonial</h1>
    <div class="container px-5 py-24 mx-auto">
      <div class="flex flex-wrap -m-4">
        <div class="lg:w-1/3 lg:mb-0 mb-6 p-4">
          <div class="h-full text-center">
            <img alt="testimonial" class="w-20 h-20 mb-8 object-cover object-center rounded-full inline-block border-2 border-gray-200 bg-gray-100" src="images/1.webp">
            <p class="leading-relaxed">Edison bulb retro cloud bread echo park, helvetica stumptown taiyaki taxidermy 90's cronut +1 kinfolk. Single-origin coffee ennui shaman taiyaki vape DIY tote bag drinking vinegar cronut adaptogen squid fanny pack vaporware.</p>
            <span class="inline-block h-1 w-10 rounded bg-indigo-500 mt-6 mb-4"></span>
            <h2 class="text-gray-900 font-medium title-font tracking-wider text-sm">HOLDEN CAULFIELD</h2>
            <p class="text-gray-500">Senior Product Designer</p>
          </div>
        </div>
        <div class="lg:w-1/3 lg:mb-0 mb-6 p-4">
          <div class="h-full text-center">
            <img alt="testimonial" class="w-20 h-20 mb-8 object-cover object-center rounded-full inline-block border-2 border-gray-200 bg-gray-100" src="images/2.jpg">
            <p class="leading-relaxed">Edison bulb retro cloud bread echo park, helvetica stumptown taiyaki taxidermy 90's cronut +1 kinfolk. Single-origin coffee ennui shaman taiyaki vape DIY tote bag drinking vinegar cronut adaptogen squid fanny pack vaporware.</p>
            <span class="inline-block h-1 w-10 rounded bg-indigo-500 mt-6 mb-4"></span>
            <h2 class="text-gray-900 font-medium title-font tracking-wider text-sm">ALPER KAMU</h2>
            <p class="text-gray-500">UI Develeoper</p>
          </div>
        </div>
        <div class="lg:w-1/3 lg:mb-0 p-4">
          <div class="h-full text-center">
            <img alt="testimonial" class="w-20 h-20 mb-8 object-cover object-center rounded-full inline-block border-2 border-gray-200 bg-gray-100" src="images/3.jpg">
            <p class="leading-relaxed">Edison bulb retro cloud bread echo park, helvetica stumptown taiyaki taxidermy 90's cronut +1 kinfolk. Single-origin coffee ennui shaman taiyaki vape DIY tote bag drinking vinegar cronut adaptogen squid fanny pack vaporware.</p>
            <span class="inline-block h-1 w-10 rounded bg-indigo-500 mt-6 mb-4"></span>
            <h2 class="text-gray-900 font-medium title-font tracking-wider text-sm">HENRY LETHAM</h2>
            <p class="text-gray-500">CTO</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- footer -->
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
