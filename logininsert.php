<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

session_start();

$conn = new mysqli("localhost", "root", "", "businessbox_local");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

function redirectWithMessage($url, $message) {
    $_SESSION['flash_message'] = $message;
    header("Location: $url");
    exit;
}

if (isset($_SESSION['flash_message'])) {
    echo "<p>" . htmlspecialchars($_SESSION['flash_message']) . "</p>";
    unset($_SESSION['flash_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        redirectWithMessage("login.php", "Please provide both email and password.");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage("login.php", "Invalid email format.");
    } else {
        $check_email_query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email_query);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];

                    $check_settings_query = "SELECT * FROM settings WHERE user_id = ?";
                    $stmt = $conn->prepare($check_settings_query);
                    $stmt->bind_param("i", $user['user_id']);
                    $stmt->execute();
                    $settings_result = $stmt->get_result();

                    if ($settings_result->num_rows === 0) {
                        $default_navbar_color = '#007BFF';
                        $default_footer_color = '#333';
                        $default_button_color = '#007BFF';

                        $insert_settings_query = "INSERT INTO settings (user_id, navbar_color, footer_color, button_color) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($insert_settings_query);
                        $stmt->bind_param("isss", $user['user_id'], $default_navbar_color, $default_footer_color, $default_button_color);
                        $stmt->execute();
                    }

                    redirectWithMessage("search-locations.php", "Welcome back!");
                } else {
                    redirectWithMessage("index.php", "Invalid password. Please try again.");
                }
            } else {
                redirectWithMessage("register.php", "No account found with this email. Please register.");
            }

            $stmt->close();
        } else {
            redirectWithMessage("index.php", "Error preparing statement: " . $conn->error);
        }
    }
}

$conn->close();

ob_end_flush();
?>
