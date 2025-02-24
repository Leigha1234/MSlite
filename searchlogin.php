<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();  
$host = "localhost";  
$username = "root";  
$password = "";  
$database = "businessbox_local";  
$message = "";  

try {  
    $connect = new PDO("mysql:host=$host;dbname=$database", $username, $password);  
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
    if(isset($_POST["login"])) {  
        if(empty($_POST["email"]) || empty($_POST["password"])) {  
            $message = '<label>All fields are required</label>';  
        } else {  
            $query = "SELECT * FROM searchers WHERE email = :email";  
            $statement = $connect->prepare($query);  
            $statement->execute(  
                array(  
                    'email' => $_POST["email"]  
                )  
            );  
            $count = $statement->rowCount();  
            if($count > 0) {  
                $user = $statement->fetch(PDO::FETCH_ASSOC);
              
                if (password_verify($_POST["password"], $user['password'])) {
                    $_SESSION["email"] = $_POST["email"];  
                    header("location:searchfrontend.php");  
                } else {
                    $message = '<label>Incorrect email or password</label>';
                }
            } else {  
                $message = '<label>Incorrect email or password</label>';  
            }  
        }  
    }  
} catch(PDOException $error) {  
    $message = $error->getMessage();  
}
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 400px;
            margin: 50px auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 10px;
            flex: 1;

        <?php echo $text_bold; ?>
        }

        h2, h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        input[type="email"], input[type="password"] {
            width: 95%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            <?php echo $text_bold; ?>
        }

        .btn {
            display: inline-block;
            color: black;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-top: 10px;
            border: none;
            background-color: <?php echo isset($_SESSION['button_color']) ? $_SESSION['button_color'] : '#007BFF'; ?>;
        <?php echo $text_bold; ?>
        }

        .btn:hover {
            text-decoration: none;
        }

        .text-danger {
            color: red;
            font-size: 14px;
            text-align: center;
            display: block;
            margin-bottom: 10px;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            font-size: 0.9rem;
            color: #1a1919;
            background-color: <?php echo isset($_SESSION['footer_color']) ? $_SESSION['footer_color'] : '#333'; ?>;
            position: relative;
        }

        .footer .contact-info {
            display: flex;
            gap: 1rem; 
        }

        .mainloginlogo {
            width: 13%;
        }

        .cookie-consent {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #333;
            color: white;
            padding: 1rem;
            text-align: center;
            font-size: 1rem;
            z-index: 1000;
        }

        .cookie-consent button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            margin-left: 20px;
        }

        .cookie-consent button:hover {
            background-color: #0056b3;
        }

        .cookie-consent .close-btn {
            position: absolute;
            top: 10px;
            right: 40px;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .fp {
            font-size: 12px;
        }
    </style>  
</head>
<body>

<div class="container">  
    <form action="searchlogin.php" method="post">
        <div id="loginDIV">
            <h2>Login</h2>
            <div class="form-group">
                <p>
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </p>
            </div>

            <div class="form-group">
                <p>
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </p>
            </div>
            <a onclick="myFunction()" href="javascript:void(0);" class="fp">Forgotten Password?</a><br />
            <input type="submit" class="btn" name="login" value="Submit">
        </div>
        <div class="form-group">
            <a href="searchregister.php">
                <button type="button" class="btn">Register Here</button>
            </a>
        </div>

        <div id="fpDIV" style="display: none;">
            <h3>Forgotten Password?</h3>
            <p>Please enter your email to reset your password</p>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="fpemail" class="form-control"  />
            </div>
            <a href="notify.php" class="btn">Reset Password</a>
        </div>
    </form>
    <?php
    if(!empty($message)) {
        echo '<div class="text-danger">'.$message.'</div>';
    }
    ?>
</div>
<footer class="footer">
    <div class="contact-info">
        <span>Email: contact@moviesite.com</span>
        <span>Phone: +44 123 456 789</span>
    </div>
    <img class="mainloginlogo" src="./poweredbyms.png" alt="MS_logo">
</footer>
<script>
    function myFunction() {
        document.getElementById('loginDIV').style.display = 'none';

        document.getElementById('fpDIV').style.display = 'block';
    }
</script>

</body>
</html>