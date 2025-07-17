<?php
// api/auth.php

// Start session to manage user login state
session_start();

// Include database configuration
require_once '../config/database.php';

// Set header to return JSON content
header('Content-Type: application/json');

// Get the posted data.
$data = json_decode(file_get_contents("php://input"));

// Check if data and action are set
if (!isset($data->action)) {
    echo json_encode(['success' => false, 'message' => 'No action specified.']);
    exit();
}

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Response array
$response = ['success' => false, 'message' => 'An error occurred.'];

switch ($data->action) {
    case 'register':
        // --- REGISTRATION LOGIC ---
        if (empty($data->name) || empty($data->email) || empty($data->password) || empty($data->confirmPassword)) {
            $response['message'] = 'Please fill in all fields.';
        } elseif (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email format.';
        } elseif (strlen($data->password) < 8) {
            $response['message'] = 'Password must be at least 8 characters long.';
        } elseif ($data->password !== $data->confirmPassword) {
            $response['message'] = 'Passwords do not match.';
        } else {
            // Check if email already exists
            $query = 'SELECT id FROM users WHERE email = :email';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $response['message'] = 'An account with this email already exists.';
            } else {
                // Hash the password
                $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);

                // Create user
                $query = 'INSERT INTO users (full_name, email, password_hash) VALUES (:name, :email, :password)';
                $stmt = $db->prepare($query);
                
                // Bind data
                $stmt->bindParam(':name', $data->name);
                $stmt->bindParam(':email', $data->email);
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Registration successful! You can now sign in.'];
                } else {
                    $response['message'] = 'Registration failed. Please try again.';
                }
            }
        }
        break;

    case 'login':
        // --- LOGIN LOGIC ---
        if (empty($data->email) || empty($data->password)) {
            $response['message'] = 'Please fill in all fields.';
        } else {
            // Find user by email
            $query = 'SELECT * FROM users WHERE email = :email';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Verify password
                if (password_verify($data->password, $user['password_hash'])) {
                    // Password is correct, set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    
                    $response = [
                        'success' => true, 
                        'message' => 'Login successful! Redirecting...',
                        'redirect' => 'dashboard.php' // URL to redirect to
                    ];
                } else {
                    $response['message'] = 'Invalid email or password.';
                }
            } else {
                $response['message'] = 'Invalid email or password.';
            }
        }
        break;
        
    default:
        $response['message'] = 'Invalid action.';
        break;
}

// Send the response
echo json_encode($response);
?>