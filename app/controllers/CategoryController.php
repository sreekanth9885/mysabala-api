<?php

require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../Helpers/JwtHelper.php';
require_once __DIR__ . '/../Core/Response.php';

class CategoryController
{
    private CategoryModel $categoryModel;

    public function __construct(PDO $db)
    {
        $this->categoryModel = new CategoryModel($db);
    }

    // Create Category
    public function create()
    {
        $user = JwtHelper::getUserFromToken();

        if (
            $user['role'] !== 'ADMIN' &&
            $user['role'] !== 'SUPER_ADMIN'
        ) {
            Response::json([
                "message" => "Forbidden - Admin access required"
            ], 403);
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['name'])) {
            Response::json([
                "message" => "Category name required"
            ], 422);
        }

        $categoryId = $this->categoryModel->create(
            trim($data['name']),
            trim($data['description'] ?? ''),
            (int)($data['is_active'] ?? 1)
        );

        Response::json([
            "message" => "Category created successfully",
            "category_id" => $categoryId
        ], 201);
    }

    // Get All Categories
    public function index()
    {
        JwtHelper::getUserFromToken();

        $categories = $this->categoryModel->all();

        Response::json([
            "data" => $categories
        ]);
    }

    // Get Single Category
    public function show($id)
    {
        JwtHelper::getUserFromToken();

        $category = $this->categoryModel->findById((int)$id);

        if (!$category) {
            Response::json([
                "message" => "Category not found"
            ], 404);
        }

        Response::json([
            "data" => $category
        ]);
    }

    // Update Category
    public function update($id)
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

        if (empty($data['name'])) {
            Response::json([
                "message" => "Category name required"
            ], 422);
        }

        $updated = $this->categoryModel->update(
            (int)$id,
            trim($data['name']),
            trim($data['description'] ?? ''),
            (int)($data['is_active'] ?? 1)
        );

        if (!$updated) {
            Response::json([
                "message" => "Category not found or no changes made"
            ], 404);
        }

        Response::json([
            "message" => "Category updated successfully"
        ]);
    }

    // Delete Category
    public function delete($id)
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

        $deleted = $this->categoryModel->delete((int)$id);

        if (!$deleted) {
            Response::json([
                "message" => "Category not found"
            ], 404);
        }

        Response::json([
            "message" => "Category deleted successfully"
        ]);
    }
}