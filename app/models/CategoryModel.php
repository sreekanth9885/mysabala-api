<?php

class CategoryModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Create Category
    public function create(
        string $name,
        string $description,
        int $isActive = 1
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO categories (name, description, is_active)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $description,
            $isActive
        ]);

        return (int)$this->db->lastInsertId();
    }

    // Get All Categories
    public function all(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                name,
                description,
                is_active,
                created_at
            FROM categories
            ORDER BY id DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get Single Category
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                name,
                description,
                is_active,
                created_at
            FROM categories
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        return $category ?: null;
    }

    // Update Category
    public function update(
        int $id,
        string $name,
        string $description,
        int $isActive
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE categories
            SET
                name = ?,
                description = ?,
                is_active = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $name,
            $description,
            $isActive,
            $id
        ]);

        return $stmt->rowCount() > 0;
    }

    // Delete Category
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM categories
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}