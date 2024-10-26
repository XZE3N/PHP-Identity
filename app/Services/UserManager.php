<?php
require __DIR__ . '/../Helpers/Normalizer.php';

class UserAlreadyExistsException extends Exception {}

class UserManager {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getUserByUsername($username) {
        $normalized_username = Normalizer::normalize($username);

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE normalized_username = :normalized_username');
        $stmt->execute(['normalized_username' => $normalized_username]);
        return $stmt->fetch();
    }

    public function getUserByEmail($email) {
        $normalized_email = Normalizer::normalize($email);

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE normalized_email = :normalized_email');
        $stmt->execute(['normalized_email' => $normalized_email]);
        return $stmt->fetch();
    }

    public function createUser($username, $email, $password) {
        $normalized_username = Normalizer::normalize($username);
        $normalized_email = Normalizer::normalize($email);

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if($this->getUserByUsername($username)) {
            throw new UserAlreadyExistsException("A user with username '$username' already exists.");
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (
                username,
                normalized_username,
                email,
                normalized_email,
                email_confirmed,
                password_hash,
                lockout_end,
                lockout_enabled,
                access_failed_count,
                created_at,
                updated_at
            ) VALUES (
                :username,
                :normalized_username,
                :email,
                :normalized_email,
                :email_confirmed,
                :password_hash,
                :lockout_end,
                :lockout_enabled,
                :access_failed_count,
                :created_at,
                :updated_at
            )'
        );

        $stmt->execute([
            'username' => $username,
            'normalized_username' => $normalized_username,
            'email' => $email,
            'normalized_email' => $normalized_email,
            'email_confirmed' => 0,                // Default to 0 (Not confirmed)
            'password_hash' => $password_hash,
            'lockout_end' => null,                 // Default to NULL
            'lockout_enabled' => 1,                // Default to 1 (Enabled)
            'access_failed_count' => 0,            // Default to 0
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
?>
