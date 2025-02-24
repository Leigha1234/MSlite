<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$conn = mysqli_connect("localhost", "root", "", "businessbox_local");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email_error = $password_error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

    if (empty($email)) {
        $email_error = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format.";
    }

    if (empty($password)) {
        $password_error = "Password is required.";
    } elseif (strlen($password) < 6) {
        $password_error = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $password_error = "Passwords do not match.";
    }

    if (empty($email_error) && empty($password_error)) {
        $check_email_query = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($check_email_query);

        if ($result->num_rows > 0) {
            $email_error = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql_users = "INSERT INTO users (email, password) VALUES ('$email', '$hashed_password')";

            if ($conn->query($sql_users) === TRUE) {
                echo "Registration successful! You can now <a href='index.php'>log in</a>.";
                exit;
            } else {
                echo "Error inserting user: " . $conn->error . "<br>";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - MovieSite Locations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 2rem;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 5rem;
        }
        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 0.8rem 1.2rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
        .form-group .error {
            color: red;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Register</h2>
    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <?php if (!empty($email_error)) { echo "<div class='error'>$email_error</div>"; } ?>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <?php if (!empty($password_error)) { echo "<div class='error'>$password_error</div>"; } ?>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
        </div>
        <div class="form-group">
            <button type="submit">Register</button>
        </div>
    </form>
</div>

</body>
</html>
