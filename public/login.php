<?php
require __DIR__ . '/../config/Database.php';
require __DIR__ . '/../app/Services/UserManager.php';
require __DIR__ . '/../app/Helpers/Normalizer.php';

// Get the database connection
$db = new Database();
$pdo = $db->getConnection();

// Instantiate the user manager
$userManager = new UserManager($pdo);

$username = NULL;
$password = NULL;

if(isset($_POST['username'])) {
    $username = $_POST['username'];
}
if(isset($_POST['password'])) {
    $password = $_POST['password'];
}

// Format input data
$username = Normalizer::trim($username);
$password = Normalizer::trim($password);


if(isset($_POST)) {
    // Display the Sign In Page
    include '../app/Pages/login.html';
}
?>
