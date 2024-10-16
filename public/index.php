<?php
require __DIR__ . '/../config/Database.php';
require __DIR__ . '/../app/Services/UserManager.php';

// Get the database connection
$db = new Database();
$pdo = $db->getConnection();

// Instantiate the user manager
$userManager = new UserManager($pdo);

print_r($userManager->getUserById(1))
?>