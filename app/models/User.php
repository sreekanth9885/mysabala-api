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
            SELECT * FROM users WHERE email = ?
        ");

        $stmt->execute([$email]);

        return $stmt->fetch();
    }
}