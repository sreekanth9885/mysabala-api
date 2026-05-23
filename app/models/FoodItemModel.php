<?php

class FoodItemModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Create Food Item
    public function create(
        int $categoryId,
        ?int $subCategoryId,
        string $name,
        string $description,
        float $price,
        ?string $image,
        int $isAvailable = 1,
        int $isActive = 1
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO food_items (
                category_id,
                sub_category_id,
                name,
                description,
                price,
                image,
                is_available,
                is_active
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $categoryId,
            $subCategoryId,
            $name,
            $description,
            $price,
            $image,
            $isAvailable,
            $isActive
        ]);

        return (int)$this->db->lastInsertId();
    }

    // Get All Food Items
    public function all(): array
    {
        $stmt = $this->db->prepare("
            SELECT
                fi.id,
                fi.name,
                fi.description,
                fi.price,
                fi.image,
                fi.is_available,
                fi.created_at,

                c.id as category_id,
                c.name as category_name,

                sc.id as sub_category_id,
                sc.name as sub_category_name

            FROM food_items fi

            INNER JOIN categories c
                ON fi.category_id = c.id

            LEFT JOIN sub_categories sc
                ON fi.sub_category_id = sc.id

            WHERE fi.is_active = 1

            ORDER BY fi.id DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get Single Food Item
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                *
            FROM food_items
            WHERE id = ?
            AND is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $foodItem = $stmt->fetch(PDO::FETCH_ASSOC);

        return $foodItem ?: null;
    }

    // Update Food Item
    public function update(
        int $id,
        int $categoryId,
        ?int $subCategoryId,
        string $name,
        string $description,
        float $price,
        ?string $image,
        int $isAvailable,
        int $isActive
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE food_items
            SET
                category_id = ?,
                sub_category_id = ?,
                name = ?,
                description = ?,
                price = ?,
                image = ?,
                is_available = ?,
                is_active = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $categoryId,
            $subCategoryId,
            $name,
            $description,
            $price,
            $image,
            $isAvailable,
            $isActive,
            $id
        ]);

        return $stmt->rowCount() > 0;
    }

    // Delete Food Item
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE food_items
            SET is_active = 0
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
    public function getByCategory(int $categoryId): array
{
    $stmt = $this->db->prepare("
        SELECT
            fi.id,
            fi.name,
            fi.description,
            fi.price,
            fi.image,
            fi.is_available,
            fi.created_at,

            c.name as category_name,
            sc.name as sub_category_name

        FROM food_items fi

        INNER JOIN categories c
            ON fi.category_id = c.id

        LEFT JOIN sub_categories sc
            ON fi.sub_category_id = sc.id

        WHERE fi.category_id = ?
        AND fi.is_active = 1

        ORDER BY fi.id DESC
    ");

    $stmt->execute([$categoryId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getBySubCategory(int $subCategoryId): array
{
    $stmt = $this->db->prepare("
        SELECT
            fi.id,
            fi.name,
            fi.description,
            fi.price,
            fi.image,
            fi.is_available,
            fi.created_at,

            c.name as category_name,
            sc.name as sub_category_name

        FROM food_items fi

        INNER JOIN categories c
            ON fi.category_id = c.id

        LEFT JOIN sub_categories sc
            ON fi.sub_category_id = sc.id

        WHERE fi.sub_category_id = ?
        AND fi.is_active = 1

        ORDER BY fi.id DESC
    ");

    $stmt->execute([$subCategoryId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}