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

        $data = json_decode(file_get_contents("php://input"), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            Response::json(["message" => "Email and password required"], 422);
        }

        $userModel = new User($pdo);
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Response::json(["message" => "Invalid credentials"], 401);
        }

        if ($user['status'] !== 'active') {
            Response::json(["message" => "Account is not active"], 403);
        }

        $userModel->updateLastLogin($user['id']);
        $user = $userModel->findByEmail($email);

        $token = JwtHelper::generateAccessToken([
            "id" => $user['id'],
            "email" => $user['email'],
            "role" => $user['role']
        ]);

        unset($user['password']);

        Response::json([
            "message" => "Login successful",
            "token" => $token,
            "user" => $user
        ]);
    }

    public static function me()
    {
        $payload = JwtHelper::getUserFromToken();
        Response::json(["user" => $payload]);
    }

    public static function logout()
    {
        Response::json(["message" => "Logout successful"]);
    }
    public static function register()
    {
        global $pdo;

        $data = json_decode(file_get_contents("php://input"), true);

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (!$name || !$email || !$password) {
            Response::json([
                "message" => "Name, email and password required"
            ], 422);
        }

        $userModel = new User($pdo);

        // CHECK EXISTING USER
        $existingUser = $userModel->findByEmail($email);

        if ($existingUser) {
            Response::json([
                "message" => "Email already exists"
            ], 409);
        }

        // HASH PASSWORD
        $hashedPassword = password_hash(
            $password,
            PASSWORD_BCRYPT
        );

        // CREATE USER
        $userId = $userModel->create(
            $name,
            $email,
            $hashedPassword
        );

        $user = $userModel->findByEmail($email);

        $token = JwtHelper::generateAccessToken([
            "id" => $user['id'],
            "email" => $user['email'],
            "role" => $user['role']
        ]);

        unset($user['password']);

        Response::json([
            "message" => "Registration successful",
            "token" => $token,
            "user" => $user
        ], 201);
    }
}