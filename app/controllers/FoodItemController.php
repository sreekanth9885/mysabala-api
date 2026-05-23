<?php

require_once __DIR__ . '/../models/FoodItemModel.php';
require_once __DIR__ . '/../Helpers/JwtHelper.php';
require_once __DIR__ . '/../Core/Response.php';

class FoodItemController
{
    private FoodItemModel $foodItemModel;

    public function __construct(PDO $db)
    {
        $this->foodItemModel = new FoodItemModel($db);
    }

    // Create Food Item
    public function create()
    {
        $user = JwtHelper::getUserFromToken();

        if (
            $user['role'] !== 'ADMIN' &&
            $user['role'] !== 'super_admin'
        ) {
            Response::json([
                "message" => "Forbidden - Admin access required"
            ], 403);
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['name']) ||
            empty($data['category_id']) ||
            empty($data['price'])
        ) {
            Response::json([
                "message" => "Name, category and price required"
            ], 422);
        }

        $foodItemId = $this->foodItemModel->create(
            (int)$data['category_id'],
            isset($data['sub_category_id'])
                ? (int)$data['sub_category_id']
                : null,
            trim($data['name']),
            trim($data['description'] ?? ''),
            (float)$data['price'],
            $data['image'] ?? null,
            (int)($data['is_available'] ?? 1),
            (int)($data['is_active'] ?? 1)
        );

        Response::json([
            "message" => "Food item created successfully",
            "food_item_id" => $foodItemId
        ], 201);
    }

    // Get All Food Items
    public function index()
    {
        // JwtHelper::getUserFromToken();

        $foodItems = $this->foodItemModel->all();

        Response::json([
            "data" => $foodItems
        ]);
    }

    // Get Single Food Item
    public function show($id)
    {
        // JwtHelper::getUserFromToken();

        $foodItem = $this->foodItemModel->findById((int)$id);

        if (!$foodItem) {
            Response::json([
                "message" => "Food item not found"
            ], 404);
        }

        Response::json([
            "data" => $foodItem
        ]);
    }

    // Update Food Item
    public function update($id)
    {
        $user = JwtHelper::getUserFromToken();

        if (
            $user['role'] !== 'ADMIN' &&
            $user['role'] !== 'super_admin'
        ) {
            Response::json([
                "message" => "Forbidden"
            ], 403);
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $updated = $this->foodItemModel->update(
            (int)$id,
            (int)$data['category_id'],
            isset($data['sub_category_id'])
                ? (int)$data['sub_category_id']
                : null,
            trim($data['name']),
            trim($data['description'] ?? ''),
            (float)$data['price'],
            $data['image'] ?? null,
            (int)($data['is_available'] ?? 1),
            (int)($data['is_active'] ?? 1)
        );

        if (!$updated) {
            Response::json([
                "message" => "Food item not found or no changes made"
            ], 404);
        }

        Response::json([
            "message" => "Food item updated successfully"
        ]);
    }

    // Delete Food Item
    public function delete($id)
    {
        $user = JwtHelper::getUserFromToken();

        if (
            $user['role'] !== 'ADMIN' &&
            $user['role'] !== 'super_admin'
        ) {
            Response::json([
                "message" => "Forbidden"
            ], 403);
        }

        $deleted = $this->foodItemModel->delete((int)$id);

        if (!$deleted) {
            Response::json([
                "message" => "Food item not found"
            ], 404);
        }

        Response::json([
            "message" => "Food item deleted successfully"
        ]);
    }
    public function byCategory($id)
{
    JwtHelper::getUserFromToken();

    $foodItems = $this->foodItemModel->getByCategory((int)$id);

    Response::json([
        "data" => $foodItems
    ]);
}
public function bySubCategory($id)
{
    JwtHelper::getUserFromToken();

    $foodItems = $this->foodItemModel->getBySubCategory((int)$id);

    Response::json([
        "data" => $foodItems
    ]);
}
}