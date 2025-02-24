<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php'; 
require_once 'settings_loader.php'; 

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['dark_mode'] = isset($_POST['dark_mode']) ? true : false;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['dark_mode'])) {
    $_SESSION['dark_mode'] = ($_GET['dark_mode'] == '1');
}

function adjust_color_brightness($hex, $steps) {
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            background-color:rgba(207, 203, 203, 0.73);
            background-color: <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? '#333' : '#fff'; ?>;
            color: <?php echo $text_color; ?>; 
        }

        .container {
            padding: 2rem;
            max-width: 800px;
            margin: 2rem auto; 
            background-color: <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? '#444' : '#f9f9f9'; ?>;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden; 
            margin-bottom: 2rem;
            background-color:rgba(207, 203, 203, 0.73);
            min-height: 400px;
        }

        h3 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? '#fff' : $text_color; ?>; 
        }

input[type="text"], textarea, input[type="email"] {
            font-family: arial;
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1rem;
            background-color: <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? '#555' : '#fff'; ?>;
            color: <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? '#fff' : $text_color; ?>;
            box-sizing: border-box; 
        }

        #close-enquiry-popup,
        #submit-enquiry {
            background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
            color: <?php echo $text_color; ?>; 
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            border: none;
            text-decoration: none; 
        }

        #close-enquiry-popup:hover,
        #submit-enquiry:hover {
            background-color: <?php echo adjust_color_brightness($button_color, -15); ?>;
        }

        ::placeholder {
            font-family: arial;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: <?php echo isset($_SESSION['footer_color']) ? $_SESSION['footer_color'] : '#333'; ?>;
            padding: 1rem 2rem;
            width: 94%;
            position: relative;
            bottom: 0;
        }

        .contact-info {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .mainloginlogo {
            width: 13%;
        }

      

        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Close Link -->
    <a class="button" href="searchfrontend.php" id="close-enquiry-popup">Close</a>

    <h3>Enquire About This Location</h3>

    <form action="submit_enquiry.php" method="POST">
        <!-- Hidden Fields to Store loctitle, loc_id, email, and searchers_id -->
        <input type="hidden" id="selected-loctitle" name="loctitle">
        <input type="hidden" id="selected-loc_id" name="loc_id">
        <input type="hidden" id="selected-email" name="email">
        <input type="hidden" id="selected-searchers_id" name="searchers_id">

        <label for="email">Your Email</label>
        <input type="email" id="email" name="email" placeholder="Your Email" required>

        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" placeholder="Subject" required>

        <label for="enquiry-message">Your Message</label>
        <textarea id="enquiry-message" name="message" placeholder="Your Message" required></textarea>

        <button type="submit" id="submit-enquiry">Submit Enquiry</button>

    </form>

</div>

<!-- Footer -->
<div class="footer">
    <div class="contact-info">
        <span>Email: contact@moviesite.com</span><br>
        <span>Phone: +44 123 456 789</span>
    </div>
    <img class="mainloginlogo" src="./poweredbyms.png" alt="MS_logo">
</div>

<script>
    document.getElementById('dark-mode-toggle').addEventListener('click', function () {
        document.body.classList.toggle('dark-mode');
        const isDarkMode = document.body.classList.contains('dark-mode') ? 1 : 0;
        window.location.search = `?dark_mode=${isDarkMode}`;
    });
</script>

</body>
</html>
