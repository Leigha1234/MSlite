<?php
session_start();
require_once 'db_connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['navbar_color'], $_SESSION['footer_color'], $_SESSION['button_color'], 
          $_SESSION['navbar_text_color'], $_SESSION['footer_text_color'], 
          $_SESSION['button_text_color'], $_SESSION['text_color'], $_SESSION['text_bold'])) {
    
    $user_id = (int) $_SESSION['user_id'];
    
    $sql = "SELECT navbar_color, footer_color, button_color, navbar_text_color, 
                   footer_text_color, button_text_color, text_color, text_bold 
            FROM settings 
            WHERE user_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['navbar_color'] = $row['navbar_color'];
        $_SESSION['footer_color'] = $row['footer_color'];
        $_SESSION['button_color'] = $row['button_color'];
        $_SESSION['navbar_text_color'] = $row['navbar_text_color'];
        $_SESSION['footer_text_color'] = $row['footer_text_color']; 
        $_SESSION['button_text_color'] = $row['button_text_color']; 
        $_SESSION['text_color'] = $row['text_color']; 
        $_SESSION['text_bold'] = $row['text_bold']; 
    } else {
        $_SESSION['navbar_color'] = '#007BFF'; 
        $_SESSION['footer_color'] = '#333'; 
        $_SESSION['button_color'] = '#007BFF'; 
        $_SESSION['navbar_text_color'] = '#fff'; 
        $_SESSION['footer_text_color'] = '#fff'; 
        $_SESSION['button_text_color'] = '#fff';
        $_SESSION['text_color'] = '#000'; 
        $_SESSION['text_bold'] = false; 
    }
}

$navbar_color = $_SESSION['navbar_color'];
$footer_color = $_SESSION['footer_color'];
$button_color = $_SESSION['button_color'];
$navbar_text_color = $_SESSION['navbar_text_color'];
$footer_text_color = $_SESSION['footer_text_color'];
$button_text_color = $_SESSION['button_text_color'];
$text_color = $_SESSION['text_color'];
$text_bold = $_SESSION['text_bold'] ? 'font-weight: bold;' : ''; 
?>