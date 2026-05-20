<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../Helpers/JwtHelper.php';
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../../config/database.php';

class AuthController
{
    public static function login()
    {
        global $pdo;

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | Validations
        |--------------------------------------------------------------------------
        */

        if (!$email || !$password) {
            Response::json([
                "message" => "Email and password are required"
            ], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json([
                "message" => "Invalid email format"
            ], 422);
        }

        if (strlen($password) < 6) {
            Response::json([
                "message" => "Password must be at least 6 characters"
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $userModel = new User($pdo);

        $user = $userModel->findByEmail($email);

        if (!$user) {
            Response::json([
                "message" => "Invalid credentials"
            ], 401);
        }

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

            Response::json([
                "message" => $message
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        */

        if (!password_verify(
            $password,
            $user['password']
        )) {
            Response::json([
                "message" => "Invalid credentials"
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Last Login
        |--------------------------------------------------------------------------
        */

        $userModel->updateLastLogin($user['id']);

        /*
        |--------------------------------------------------------------------------
        | Generate JWT
        |--------------------------------------------------------------------------
        */

        $token = JwtHelper::generateAccessToken([
            "id" => $user['id'],
            "email" => $user['email'],
            "role" => $user['role']
        ]);

        /*
        |--------------------------------------------------------------------------
        | Remove Password
        |--------------------------------------------------------------------------
        */

        unset($user['password']);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        Response::json([
            "message" => "Login successful",
            "token" => $token,
            "user" => $user
        ]);
    }

    public static function me()
    {
        $payload = JwtHelper::getUserFromToken();

        Response::json([
            "user" => $payload
        ]);
    }

    public static function logout()
    {
        Response::json([
            "message" => "Logout successful"
        ]);
    }
}