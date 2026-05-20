<?php

class User
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM users 
            WHERE email = ?
            AND deleted_at IS NULL
            LIMIT 1
        ");

        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLastLogin(int $userId)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET last_login = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$userId]);
    }
}