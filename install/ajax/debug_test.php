<?php
/**
 * Debug test file - DELETE AFTER TESTING
 */

// Show all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP Debug Test</h1>";
echo "<pre>";

// PHP Info
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP SAPI: " . PHP_SAPI . "\n\n";

// Check MySQLi
echo "MySQLi Extension: " . (extension_loaded('mysqli') ? 'LOADED' : 'NOT LOADED') . "\n";
echo "MySQLi Class Exists: " . (class_exists('mysqli') ? 'YES' : 'NO') . "\n\n";

// Session test
session_start();
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n\n";

// Test MySQL connection with default values
echo "Testing MySQL Connection...\n";
echo "Host: localhost\n";
echo "User: root\n";
echo "Pass: (empty)\n\n";

try {
    $mysqli = @new mysqli('localhost', 'root', '', '', 3306);
    
    if ($mysqli->connect_error) {
        echo "Connection FAILED!\n";
        echo "Error Code: " . $mysqli->connect_errno . "\n";
        echo "Error Message: " . $mysqli->connect_error . "\n";
    } else {
        echo "Connection SUCCESSFUL!\n";
        echo "Server Version: " . $mysqli->server_info . "\n";
        
        // Show databases
        $result = $mysqli->query("SHOW DATABASES");
        if ($result) {
            echo "\nDatabases found:\n";
            while ($row = $result->fetch_array()) {
                echo "  - " . $row[0] . "\n";
            }
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}

echo "</pre>";

// Check file permissions
echo "<h2>File Permissions</h2>";
echo "<pre>";
echo "Current file: " . __FILE__ . "\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Writable: " . (is_writable(__DIR__) ? 'YES' : 'NO') . "\n";
echo "</pre>";
?>