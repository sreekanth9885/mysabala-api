<?php
class CategoryModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(string $name, ?string $description = null): int
    {
        if ($this->exists($name)) {
            throw new Exception("Category already exists");
        }

        $stmt = $this->db->prepare("
            INSERT INTO categories (name, description, is_active)
            VALUES (?, ?, 1)
        ");

        $stmt->execute([$name, $description]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $name, ?string $description = null): bool
    {
        if ($this->exists($name, $id)) {
            throw new Exception("Duplicate category");
        }

        $stmt = $this->db->prepare("
            UPDATE categories 
            SET name = ?, description = ?
            WHERE id = ? AND is_active = 1
        ");

        $stmt->execute([$name, $description, $id]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();
            // SOFT DELETE CATEGORY
            $categoryStmt = $this->db->prepare("
                UPDATE categories
                SET is_active = 0
                WHERE id = ?
            ");

            $categoryStmt->execute([$id]);

            
            $this->db->commit();

            return $categoryStmt->rowCount() > 0;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function all(): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, description, is_active, created_at 
            FROM categories
            WHERE is_active = 1
            ORDER BY id DESC
        ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, description, is_active, created_at
            FROM categories
            WHERE id = ? AND is_active = 1
        ");

        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    private function exists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM categories WHERE name = ? AND is_active = 1";
        
        if ($excludeId) {
            $sql .= " AND id != ?";
        }

        $stmt = $this->db->prepare($sql);
        $params = [$name];
        
        if ($excludeId) {
            $params[] = $excludeId;
        }

        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }
}