<?php
require __DIR__ . '/../config/Database.php';

// Create a new instance of the Database class to get the PDO connection
$db = new Database();
$pdo = $db->getConnection();
$migrationsStateFile = __DIR__ . '/../migrations/migration_state.json';
$logFile = __DIR__ . '/../migrations/migrations.log';  // Log file location

// Load the applied migrations from the state file
function loadMigrationState($file) {
    if (!file_exists($file)) {
        return ['migrations' => []]; // Return an empty migrations array if the file doesn't exist
    }
    return json_decode(file_get_contents($file), true);
}

// Save the applied migrations to the state file
function saveMigrationState($file, $migrations) {
    file_put_contents($file, json_encode(['migrations' => $migrations], JSON_PRETTY_PRINT));
}

// Function to log migration actions
function logMigrationAction($logFile, $action, $migrationName) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $action migration: $migrationName\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);  // Append log message to log file
}

// Get all migration files in the migrations directory
$migrationFiles = glob(__DIR__ . '/../migrations/*.php');
$migrationsState = loadMigrationState($migrationsStateFile);
$appliedMigrations = $migrationsState['migrations'];

function applyMigrations($pdo, $migrationFiles, $appliedMigrations, $migrationsStateFile, $logFile, $specificMigration = null) {
    foreach ($migrationFiles as $file) {
        // Extract the class name from the filename
        $className = basename($file, '.php'); // e.g., "20240927150812_CreateUsersTable"

        // Check if the class name corresponds to the file naming convention
        if (!preg_match('/^\d{14}_(.*)$/', $className, $matches)) {
            echo "Invalid migration file naming convention: $file\n";
            continue;
        }

        $simpleClassName = $matches[1]; // Get the actual class name without the timestamp

        // If specific migration is provided, only apply that one
        if ($specificMigration && $className !== $specificMigration) {
            continue;
        }

        if (!in_array($className, $appliedMigrations)) {
            include $file; // Include the migration file

            // Check if the class exists with the correct name
            if (class_exists($simpleClassName)) {
                $migration = new $simpleClassName(); // Instantiate the migration class
                echo "Applying migration: $className\n";
                $migration->Up($pdo); // Call the Up method

                // Record the migration
                $appliedMigrations[] = $className;
                saveMigrationState($migrationsStateFile, $appliedMigrations);
                
                // Log the migration action
                logMigrationAction($logFile, 'Applied', $className);
            } else {
                echo "Migration class does not exist: $simpleClassName\n";
            }
        } else {
            echo "Migration already applied: $className\n";
        }
    }
}

function rollbackMigrations($pdo, $migrationFiles, $appliedMigrations, $migrationsStateFile, $logFile, $specificMigration = null) {
    foreach (array_reverse($migrationFiles) as $file) {
        // Extract the class name from the filename
        $className = basename($file, '.php'); // e.g., "20240927150812_CreateUsersTable"

        if (!preg_match('/^\d{14}_(.*)$/', $className, $matches)) {
            echo "Invalid migration file naming convention: $file\n";
            continue;
        }

        $simpleClassName = $matches[1]; // Get the actual class name without the timestamp

        // If specific migration is provided, only roll back that one
        if ($specificMigration && $className !== $specificMigration) {
            continue;
        }

        if (in_array($className, $appliedMigrations)) {
            include $file; // Include the migration file

            // Check if the class exists with the correct name
            if (class_exists($simpleClassName)) {
                $migration = new $simpleClassName(); // Instantiate the migration class
                echo "Rolling back migration: $className\n";
                $migration->Down($pdo); // Call the Down method
                
                // Remove the migration from the state
                $appliedMigrations = array_diff($appliedMigrations, [$className]);
                saveMigrationState($migrationsStateFile, $appliedMigrations);

                // Log the rollback action
                logMigrationAction($logFile, 'Rolled back', $className);
            } else {
                echo "Migration class does not exist: $simpleClassName\n";
            }
        } else {
            echo "Migration not found for rollback: $className\n";
        }
    }
}

// Check for command line arguments to determine the action
if (isset($argv[1])) {
    $action = $argv[1];  // Action can be 'migrate' or 'rollback'
    $specificMigration = $argv[2] ?? null;  // Optional: Specific migration to run

    if ($action === 'migrate') {
        if ($specificMigration) {
            // Apply only the specific migration
            applyMigrations($pdo, $migrationFiles, $appliedMigrations, $migrationsStateFile, $logFile, $specificMigration);
        } else {
            // Apply all pending migrations
            applyMigrations($pdo, $migrationFiles, $appliedMigrations, $migrationsStateFile, $logFile);
        }
    } elseif ($action === 'rollback') {
        if ($specificMigration) {
            // Rollback only the specific migration
            rollbackMigrations($pdo, $migrationFiles, $appliedMigrations, $migrationsStateFile, $logFile, $specificMigration);
        } else {
            // Rollback all migrations
            rollbackMigrations($pdo, $migrationFiles, $appliedMigrations, $migrationsStateFile, $logFile);
        }
    } else {
        echo "Invalid command. Use 'migrate' or 'rollback'.\n";
    }
} else {
    echo "Please provide a command (migrate/rollback) and optionally a migration name.\n";
}
?>
