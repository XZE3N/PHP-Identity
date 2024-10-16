<?php
class CreateSchema
{
    public function up($pdo) {
        // SQL statement to create the users table
        $stmt = "CREATE TABLE users (
                    id VARCHAR(255) NOT NULL PRIMARY KEY,  -- Unique identifier (string)
                    username VARCHAR(255) NOT NULL UNIQUE,  -- Username (unique)
                    normalized_username VARCHAR(255) NOT NULL,  -- Normalized username
                    email VARCHAR(255) NOT NULL UNIQUE,  -- Email (unique)
                    normalized_email VARCHAR(255) NOT NULL,  -- Normalized email
                    email_confirmed TINYINT(1) NOT NULL DEFAULT 0,  -- Email confirmation flag
                    password_hash VARCHAR(255) NOT NULL,  -- Hashed password
                    lockout_end DATETIME DEFAULT NULL,  -- Lockout end time
                    lockout_enabled TINYINT(1) NOT NULL DEFAULT 0,  -- Lockout enabled flag
                    access_failed_count INT NOT NULL DEFAULT 0,  -- Failed login attempts
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Creation timestamp
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  -- Update timestamp
                )
        ";
        $pdo->exec($stmt);
    }

    public function down($pdo) {
        $stmt = "DROP TABLE IF EXISTS users";
        $pdo->exec($stmt);
    }
}
?>