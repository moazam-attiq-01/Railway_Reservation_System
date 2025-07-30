<?php
session_start();
include 'connection.php';

// Handle registration
if (isset($_POST['register'])) {
    $username = $_POST['cUsername'];
    $password = $_POST['cPwd'];
    $email = $_POST['cEmail'];
    $cnic = $_POST['cCNIC'];

    // Check if username already exists
    $checkUser = $conn->prepare("SELECT * FROM passenger WHERE name = ?");
    $checkUser->bind_param('s', $username);
    $checkUser->execute();
    $userResult = $checkUser->get_result();

    if ($userResult->num_rows > 0) {
        echo "<script>alert('Username already exists.');</script>";
    } else {
        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT * FROM passenger WHERE email = ?");
        $checkEmail->bind_param('s', $email);
        $checkEmail->execute();
        $emailResult = $checkEmail->get_result();

        if ($emailResult->num_rows > 0) {
            echo "<script>alert('Email already exists.');</script>";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO passenger (name, password, email, cnic) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $username, $password, $email, $cnic);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<script>alert('Registration successful.');</script>";
            } else {
                echo "<script>alert('Error during registration.');</script>";
            }
        }
    }
}

// Handle login
if (isset($_POST['login'])) {
  $email = $_POST['lEmail'];
  $password = $_POST['lPwd'];

  // Debug: Log the email being checked
  error_log("Login attempt with email: " . $email);

  if ($email === 'admin1@example.com') {
      // Check admin login
      $checkLogin = $conn->prepare("SELECT * FROM admin WHERE email = ?");
      $checkLogin->bind_param('s', $email);
      $checkLogin->execute();
      $result = $checkLogin->get_result();

      if ($result->num_rows > 0) {
          $user = $result->fetch_assoc();
          if ($password === $user['password']) {
              $_SESSION['username'] = $user['name'];
              $_SESSION['passenger_id'] = $user['passenger_ID']; 

              header("Location: admin-dashboard.php");
              exit();
          } else {
              echo "<script>alert('Invalid password.');</script>";
          }
      } else {
          echo "<script>alert('Invalid email.');</script>";
      }
  } else {
      // Check passenger login
      $checkLogin = $conn->prepare("SELECT * FROM passenger WHERE email = ?");
      $checkLogin->bind_param('s', $email);
      $checkLogin->execute();
      $result = $checkLogin->get_result();

      if ($result->num_rows > 0) {
          $user = $result->fetch_assoc();
          if ($password === $user['password']) {
              $_SESSION['username'] = $user['name'];
              $_SESSION['passenger_id'] = $user['passenger_ID']; 

              // Debug: Log the passenger ID being set
              error_log("Passenger ID set: " . $user['passenger_ID']);
              header("Location: Home.php");
              exit();
          } else {
              echo "<script>alert('Invalid password.');</script>";
          }
      } else {
          echo "<script>alert('Invalid email.');</script>";
      }
  }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <title>Login and Registration</title>
  <style>
    a {
      text-decoration: none !important;
      font-weight: bold !important;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="login-page">
    <div class="form">
      <!-- Registration Form -->
      <form class="register-form" method="post">
        <input type="text" placeholder="Name" name="cUsername" required />
        <input type="password" placeholder="Password" name="cPwd" required />
        <input type="text" placeholder="Email" name="cEmail" required />
        <input type="text" placeholder="CNIC" name="cCNIC" required />
        <button type="submit" name="register" class="bg-indigo-600 hover:bg-indigo-700">Create</button>
        <p class="message">Already registered? <a href="#">Sign In</a></p>
      </form>

      <!-- Login Form -->
      <form class="login-form" method="post">
        <input type="email" placeholder="Email" name="lEmail" required />
        <input type="password" placeholder="Password" name="lPwd" required />
        <button type="submit" name="login" class="bg-indigo-600 hover:bg-indigo-700">Login</button>
        <p class="message">Not registered? <a href="#">Create an account</a></p>
      </form>
    </div>
  </div>
  <script>
    $('.message a').click(function(){
       $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
    });
  </script>




</body>
</html>
