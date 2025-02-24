<?php
require_once 'db_connection.php';
include 'settings_loader.php'; 

if (isset($_POST['action']) && $_POST['action'] == "add") {
    if (!isset($_POST['email']) || !isset($_POST['password']) || empty($_POST['email']) || empty($_POST['password'])) {
        echo "error: missing email or password";
        exit();
    }

    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $password]);

        echo "success";
    } catch (PDOException $e) {
        echo "error: " . $e->getMessage();
    }
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == "update") {
    if (!isset($_POST['old_email']) || !isset($_POST['new_email']) || !isset($_POST['old_password']) || empty($_POST['old_email']) || empty($_POST['new_email']) || empty($_POST['old_password'])) {
        echo "error: missing fields";
        exit();
    }

    $oldEmail = $_POST['old_email'];
    $newEmail = $_POST['new_email'];
    $oldPassword = $_POST['old_password'];
    $newPassword = !empty($_POST['new_password']) ? password_hash($_POST['new_password'], PASSWORD_DEFAULT) : null;

    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->execute([$oldEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "error: user not found";
            exit();
        }

        if (!password_verify($oldPassword, $user['password'])) {
            echo "error: incorrect old password";
            exit();
        }

        if ($newPassword) {
            $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE email = ?");
            $stmt->execute([$newEmail, $newPassword, $oldEmail]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE email = ?");
            $stmt->execute([$newEmail, $oldEmail]);
        }

        echo "success";
    } catch (PDOException $e) {
        echo "error: " . $e->getMessage();
    }
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == "delete") {
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        echo "error: missing email";
        exit();
    }

    $email = $_POST['email'];

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$email]);

        echo "success";
    } catch (PDOException $e) {
        echo "error: " . $e->getMessage();
    }
    exit();
}

try {
    $stmt = $pdo->query("SELECT email FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "error: " . $e->getMessage();
}

function adjust_color_brightness($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + $percent));
    $g = max(0, min(255, $g + $percent));
    $b = max(0, min(255, $b + $percent));

    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

$hover_color = adjust_color_brightness($button_color, -20); 
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        button:hover {
            background-color: <?php echo adjust_color_brightness($button_color, -20); ?>;
}

           html, body {
        height: 100%;
        margin: 0;
        display: flex;
        flex-direction: column; 
        color: <?php echo $text_color; ?>; 
        <?php echo $text_bold; ?>
    }

        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        form, table {
            
            margin: auto;
            width: 50%;
        }
        input {
            padding: 8px;
            border-color: lightgray;
            border-radius: 5px;
            margin: 5px;
            width: 80%;
        }
        button {
            padding: 8px;
            background-color: lightpink;
            color: black;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        table {
            width: 50%;
            border-collapse: collapse;
        }
        th, td {
            
            padding: 10px;
            border: 1px solid black;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            width: 30%;
            margin-top: 6%;
        }
        .modal-content input {
            width: 80%;
        }
        .modal-content button {
            width: 48%;
            margin: 10px 1%;
        }
        
    .footer img.mainloginlogo {
        width: 13%;
        height: auto;
    }
    .footer {
        background-color: <?php echo isset($_SESSION['footer_color']) ? $_SESSION['footer_color'] : '#333'; ?>;
        color: <?php echo $text_color; ?>;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        font-size: 0.9rem;
        position: relative;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 10;
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
    }
    button {
        background-color: <?php echo $button_color; ?>;
        color: <?php echo $text_color; ?>;
        <?php echo $text_bold; ?>
        border-radius: 5px;
    }
    .closebutton {
        background-color: <?php echo $button_color; ?>;
        color: <?php echo $text_color; ?>;
        <?php echo $text_bold; ?>
        width: 10%;
        margin: 10px 1%;
        border-radius: 5px;
        margin-top: -50px;
    }

    .submit {
        background-color: <?php echo $button_color; ?>;
        color: <?php echo $text_color; ?>;
        <?php echo $text_bold; ?>
        width: 14%;
        margin: 10px 1%;
        border-radius: 5px;
        margin-left: 69%;
        margin-bottom: -20px;
    }
    </style>
</head>
<body>

    <h2>User Management</h2>
    <button onclick="goBackToSettings()" class="closebutton">Close</button>
</br>
    <form id="addForm">
        <input type="email" id="email" placeholder="Enter new users email" required>
        <input type="password" id="password" placeholder="Enter password" required>
        <input type="password" id="confirm_password" placeholder="Confirm password" required>
        <button type="submit" class="submit">Add User</button>
    </form>
   
    <p id="error_message" style="color: red;"></p>

    <table border="1" >
        <tr>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <tbody id="customerTable">
    <?php foreach ($users as $user) { ?>
    <tr>
        <td><?php echo $user['email']; ?></td>
        <td>
            <button onclick="openEditModal('<?php echo $user['email']; ?>')">Edit</button>
            <button onclick="deleteCustomer('<?php echo $user['email']; ?>')">Delete</button>
        </td>
    </tr>
    <?php } ?>
</tbody>

            
    </table>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit User</h3>
            <input type="hidden" id="old_email">
            <input type="email" id="new_email" placeholder="Enter new email" required>
            <input type="password" id="old_password" placeholder="Enter old password" required>
            <input type="password" id="new_password" placeholder="Enter new password">
            <input type="password" id="confirm_new_password" placeholder="Confirm new password">
            <button onclick="updateCustomer()">Update</button>
            <button onclick="closeModal()" style="">Cancel</button>
        </div>
    </div>
            </br>
    <footer class="footer">
        <div class="contact-info">
          <span>Email: contact@moviesite.com</span>
          <span>Phone: +44 123 456 789</span>
        </div>
        <img class="mainloginlogo" src="./poweredbyms.png" alt="MS_logo">
      </footer>
    <script>
        function goBackToSettings() {
            window.location.href = 'settings.php';
        }

        $("#addForm").submit(function(e) {
            e.preventDefault();

            let email = $("#email").val();
            let password = $("#password").val();
            let confirmPassword = $("#confirm_password").val();

            if (password !== confirmPassword) {
                $("#error_message").text("Passwords do not match!");
                return;
            }

            $.post("users.php", { action: "add", email: email, password: password }, function(response) {
                if (response.trim() === "success") location.reload();
                else alert("Error: " + response);
            });
        });

        function openEditModal(email) {
            $("#old_email").val(email);
            $("#new_email").val(email);
            $("#editModal").show();
        }

        function closeModal() {
            $("#editModal").hide();
        }

        function updateCustomer() {
            let oldEmail = $("#old_email").val();
            let newEmail = $("#new_email").val();
            let oldPassword = $("#old_password").val();
            let newPassword = $("#new_password").val();
            let confirmNewPassword = $("#confirm_new_password").val();

            if (!oldPassword) {
                alert("Please enter your old password!");
                return;
            }

            if (newPassword && newPassword !== confirmNewPassword) {
                alert("New passwords do not match!");
                return;
            }

            $.post("users.php", { 
                action: "update", 
                old_email: oldEmail, 
                new_email: newEmail, 
                old_password: oldPassword, 
                new_password: newPassword 
            }, function(response) {
                if (response.trim() === "success") {
                    location.reload();
                    closeModal();
                } else {
                    alert("Error: " + response);
                }
            });
        }

        function deleteCustomer(email) {
            if (confirm("Are you sure you want to delete " + email + "?")) {
                $.post("users.php", { action: "delete", email: email }, function(response) {
                    if (response.trim() === "success") location.reload();
                    else alert("Error: " + response);
                });
            }
        }
    </script>

</body>
</html>
