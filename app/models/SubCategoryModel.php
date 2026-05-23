<?php

class SubCategoryModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Create Sub Category
    public function create(
        int $categoryId,
        string $name,
        ?string $description,
        int $isActive = 1
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO sub_categories
            (
                category_id,
                name,
                description,
                is_active
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $categoryId,
            $name,
            $description,
            $isActive
        ]);

        return (int)$this->db->lastInsertId();
    }

    // Get All Sub Categories
    public function all(): array
    {
        $stmt = $this->db->prepare("
            SELECT
                sc.id,
                sc.category_id,
                c.name AS category_name,
                sc.name,
                sc.description,
                sc.is_active,
                sc.created_at
            FROM sub_categories sc
            INNER JOIN categories c
                ON c.id = sc.category_id
            WHERE sc.is_active = 1
            ORDER BY sc.id DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get Single Sub Category
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                sc.id,
                sc.category_id,
                c.name AS category_name,
                sc.name,
                sc.description,
                sc.is_active,
                sc.created_at
            FROM sub_categories sc
            INNER JOIN categories c
                ON c.id = sc.category_id
            WHERE sc.id = ?
            AND sc.is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $subCategory = $stmt->fetch(PDO::FETCH_ASSOC);

        return $subCategory ?: null;
    }

    // Get By Category
    public function getByCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                category_id,
                name,
                description,
                is_active,
                created_at
            FROM sub_categories
            WHERE category_id = ?
            AND is_active = 1
            ORDER BY name ASC
        ");

        $stmt->execute([$categoryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update Sub Category
    public function update(
        int $id,
        int $categoryId,
        string $name,
        ?string $description,
        int $isActive
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE sub_categories
            SET
                category_id = ?,
                name = ?,
                description = ?,
                is_active = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $categoryId,
            $name,
            $description,
            $isActive,
            $id
        ]);

        return $stmt->rowCount() > 0;
    }

    // Delete Sub Category
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE sub_categories
            SET is_active = 0
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    // Check Category Exists
    public function categoryExists(int $categoryId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM categories
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$categoryId]);

        return (bool)$stmt->fetch();
    }
}