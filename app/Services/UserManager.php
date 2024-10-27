<?php
require __DIR__ . '/../Helpers/Normalizer.php';

class UserAlreadyExistsException extends Exception {}
class UserNotFoundException extends Exception {}
class FailedSignInException extends Exception {}

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

    public function userSignIn($username, $password) {
        $user = $this->getUserByUsername($username);

        if(!$user) {
            throw new FailedSignInException("These credentials do not match our records.");
            return;
        }

        if(!password_verify($password, $user['password_hash'])) {
            throw new FailedSignInException("These credentials do not match our records.");
            return;
        }

        // Check if the hash needs to be updated (e.g., if the algorithm or cost has changed)
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = :newHash WHERE username = :username");
            $stmt->execute(['newHash' => $newHash, 'username' => $username]);
        }

        // TODO: Set session variable
        return TRUE;
    }
}
?>
