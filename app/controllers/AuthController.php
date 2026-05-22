<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../Helpers/JwtHelper.php';
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../../config/database.php';

class AuthController
{
    public static function login()
    {
        try {
            // Log start of login attempt
            error_log("=== LOGIN ATTEMPT STARTED ===");

            global $pdo;

            // Check database connection
            if (!$pdo) {
                error_log("ERROR: Database connection not established");
                Response::json([
                    "message" => "Database connection error",
                    "debug" => "PDO connection failed"
                ], 500);
                return;
            }

            error_log("Database connection OK");

            // Get and log input
            $input = file_get_contents("php://input");
            error_log("Raw input received: " . $input);

            $data = json_decode($input, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error: " . json_last_error_msg());
                Response::json([
                    "message" => "Invalid JSON data",
                    "debug" => json_last_error_msg()
                ], 400);
                return;
            }

            $email = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');

            error_log("Email provided: " . $email);
            error_log("Password length: " . strlen($password));

            /*
            |--------------------------------------------------------------------------
            | Validations
            |--------------------------------------------------------------------------
            */

            if (!$email || !$password) {
                error_log("Validation failed: Missing email or password");
                Response::json([
                    "message" => "Email and password are required",
                    "debug" => "Email: " . ($email ? "provided" : "missing") . ", Password: " . ($password ? "provided" : "missing")
                ], 422);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error_log("Validation failed: Invalid email format - " . $email);
                Response::json([
                    "message" => "Invalid email format",
                    "debug" => "Email format check failed for: " . $email
                ], 422);
                return;
            }

            if (strlen($password) < 6) {
                error_log("Validation failed: Password too short - Length: " . strlen($password));
                Response::json([
                    "message" => "Password must be at least 6 characters",
                    "debug" => "Password length: " . strlen($password)
                ], 422);
                return;
            }

            error_log("Validations passed");

            /*
            |--------------------------------------------------------------------------
            | Find User
            |--------------------------------------------------------------------------
            */

            try {
                $userModel = new User($pdo);
                error_log("User model instantiated");

                $user = $userModel->findByEmail($email);
                error_log("findByEmail executed");
            } catch (Exception $e) {
                error_log("Error in findByEmail: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                Response::json([
                    "message" => "Database query error",
                    "debug" => $e->getMessage()
                ], 500);
                return;
            }

            if (!$user) {
                error_log("User not found: " . $email);
                Response::json([
                    "message" => "Invalid credentials",
                    "debug" => "No user found with email: " . $email
                ], 401);
                return;
            }

            error_log("User found - ID: " . $user['id'] . ", Role: " . $user['role'] . ", Status: " . $user['status']);

            /*
            |--------------------------------------------------------------------------
            | Account Status Validation
            |--------------------------------------------------------------------------
            */

            if ($user['status'] !== 'active') {
                $message = match ($user['status']) {
                    'inactive' => 'Account is inactive',
                    'blocked' => 'Account has been blocked',
                    default => 'Account access denied'
                };

                error_log("Account not active. Status: " . $user['status']);
                Response::json([
                    "message" => $message,
                    "debug" => "Account status: " . $user['status']
                ], 403);
                return;
            }

            error_log("Account status is active");

            /*
            |--------------------------------------------------------------------------
            | Verify Password
            |--------------------------------------------------------------------------
            */

            error_log("Verifying password...");
            $passwordValid = password_verify($password, $user['password']);
            error_log("Password verify result: " . ($passwordValid ? "true" : "false"));

            if (!$passwordValid) {
                error_log("Password verification failed for user: " . $email);
                Response::json([
                    "message" => "Invalid credentials",
                    "debug" => "Password verification failed"
                ], 401);
                return;
            }

            error_log("Password verified successfully");

            /*
            |--------------------------------------------------------------------------
            | Update Last Login
            |--------------------------------------------------------------------------
            */

            try {
                error_log("Updating last login for user ID: " . $user['id']);
                $userModel->updateLastLogin($user['id']);
                error_log("Last login updated successfully");
            } catch (Exception $e) {
                error_log("Failed to update last login: " . $e->getMessage());
                // Continue anyway - this shouldn't block login
            }

            // Refresh user data
            error_log("Refreshing user data");
            $user = $userModel->findByEmail($email);

            /*
            |--------------------------------------------------------------------------
            | Generate JWT
            |--------------------------------------------------------------------------
            */

            try {
                error_log("Generating JWT token...");
                $token = JwtHelper::generateAccessToken([
                    "id" => $user['id'],
                    "email" => $user['email'],
                    "role" => $user['role']
                ]);
                error_log("JWT generated successfully. Token length: " . strlen($token));
            } catch (Exception $e) {
                error_log("JWT generation failed: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                Response::json([
                    "message" => "Token generation failed",
                    "debug" => $e->getMessage()
                ], 500);
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Password
            |--------------------------------------------------------------------------
            */

            unset($user['password']);
            error_log("Password removed from response");

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            error_log("=== LOGIN SUCCESSFUL ===");
            Response::json([
                "message" => "Login successful",
                "token" => $token,
                "user" => $user
            ]);
        } catch (Throwable $e) {
            // Catch any unexpected errors
            error_log("!!! UNCAUGHT EXCEPTION IN LOGIN !!!");
            error_log("Error message: " . $e->getMessage());
            error_log("Error file: " . $e->getFile());
            error_log("Error line: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());

            Response::json([
                "message" => "Server error",
                "debug" => $e->getMessage(),
                "file" => basename($e->getFile()),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public static function me()
    {
        try {
            error_log("=== ME ENDPOINT CALLED ===");
            $payload = JwtHelper::getUserFromToken();
            error_log("User payload retrieved successfully");

            Response::json([
                "user" => $payload
            ]);
        } catch (Exception $e) {
            error_log("ME endpoint error: " . $e->getMessage());
            Response::json([
                "message" => "Authentication failed",
                "debug" => $e->getMessage()
            ], 401);
        }
    }

    public static function logout()
    {
        try {
            error_log("=== LOGOUT ENDPOINT CALLED ===");
            Response::json([
                "message" => "Logout successful"
            ]);
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            Response::json([
                "message" => "Logout failed",
                "debug" => $e->getMessage()
            ], 500);
        }
    }
}