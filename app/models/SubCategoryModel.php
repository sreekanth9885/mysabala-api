<?php
class SubCategoryModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(int $categoryId, string $name, ?string $description = null): int
    {
        if ($this->exists($categoryId, $name)) {
            throw new Exception("Sub-category already exists in this category");
        }

        $stmt = $this->db->prepare("
            INSERT INTO sub_categories (category_id, name, description, is_active)
            VALUES (?, ?, ?, 1)
        ");

        $stmt->execute([$categoryId, $name, $description]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $categoryId, string $name, ?string $description = null): bool
    {
        if ($this->exists($categoryId, $name, $id)) {
            throw new Exception("Duplicate sub-category");
        }

        $stmt = $this->db->prepare("
            UPDATE sub_categories 
            SET category_id = ?, name = ?, description = ?
            WHERE id = ? AND is_active = 1
        ");

        $stmt->execute([$categoryId, $name, $description, $id]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            // Check if sub-category has products (if products table exists)
            // Uncomment if you have products table
            /*
            $productStmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM products
                WHERE sub_category_id = ? AND is_active = 1
            ");
            $productStmt->execute([$id]);
            $productCount = $productStmt->fetch(PDO::FETCH_ASSOC);

            if ($productCount['total'] > 0) {
                throw new Exception("Cannot delete sub-category. Products are linked.");
            }
            */

            // Soft delete sub-category
            $stmt = $this->db->prepare("
                UPDATE sub_categories
                SET is_active = 0
                WHERE id = ?
            ");

            $stmt->execute([$id]);
            $this->db->commit();

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function all(int $categoryId = null): array
    {
        if ($categoryId) {
            $stmt = $this->db->prepare("
                SELECT sc.id, sc.name, sc.description, sc.category_id, sc.created_at,
                       c.name as category_name
                FROM sub_categories sc
                LEFT JOIN categories c ON sc.category_id = c.id
                WHERE sc.is_active = 1 AND sc.category_id = ?
                ORDER BY sc.id DESC
            ");
            $stmt->execute([$categoryId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT sc.id, sc.name, sc.description, sc.category_id, sc.created_at,
                       c.name as category_name
                FROM sub_categories sc
                LEFT JOIN categories c ON sc.category_id = c.id
                WHERE sc.is_active = 1
                ORDER BY sc.id DESC
            ");
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT sc.*, c.name as category_name
            FROM sub_categories sc
            LEFT JOIN categories c ON sc.category_id = c.id
            WHERE sc.id = ? AND sc.is_active = 1
        ");

        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    private function exists(int $categoryId, string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM sub_categories WHERE category_id = ? AND name = ? AND is_active = 1";
        
        if ($excludeId) {
            $sql .= " AND id != ?";
        }

        $stmt = $this->db->prepare($sql);
        $params = [$categoryId, $name];
        
        if ($excludeId) {
            $params[] = $excludeId;
        }

        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }
}