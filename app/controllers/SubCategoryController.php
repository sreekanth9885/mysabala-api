<?php

require_once __DIR__ . '/../models/SubCategoryModel.php';
require_once __DIR__ . '/../Helpers/JwtHelper.php';
require_once __DIR__ . '/../Core/Response.php';

class SubCategoryController
{
    private SubCategoryModel $subCategoryModel;

    public function __construct(PDO $db)
    {
        $this->subCategoryModel = new SubCategoryModel($db);
    }

    // Create
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

        if (empty($data['category_id'])) {
            Response::json([
                "message" => "Category required"
            ], 422);
        }

        if (empty($data['name'])) {
            Response::json([
                "message" => "Sub category name required"
            ], 422);
        }

        $categoryExists = $this->subCategoryModel
            ->categoryExists((int)$data['category_id']);

        if (!$categoryExists) {
            Response::json([
                "message" => "Category not found"
            ], 404);
        }

        $subCategoryId = $this->subCategoryModel->create(
            (int)$data['category_id'],
            trim($data['name']),
            trim($data['description'] ?? ''),
            (int)($data['is_active'] ?? 1)
        );

        Response::json([
            "message" => "Sub category created successfully",
            "sub_category_id" => $subCategoryId
        ], 201);
    }

    // Get All
    public function index()
    {
        JwtHelper::getUserFromToken();

        $subCategories = $this->subCategoryModel->all();

        Response::json([
            "data" => $subCategories
        ]);
    }

    // Get Single
    public function show($id)
    {
        JwtHelper::getUserFromToken();

        $subCategory = $this->subCategoryModel->findById((int)$id);

        if (!$subCategory) {
            Response::json([
                "message" => "Sub category not found"
            ], 404);
        }

        Response::json([
            "data" => $subCategory
        ]);
    }

    // Get By Category
    public function byCategory($categoryId)
    {
        JwtHelper::getUserFromToken();

        $subCategories = $this->subCategoryModel->getByCategory(
            (int)$categoryId
        );

        Response::json([
            "data" => $subCategories
        ]);
    }

    // Update
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

        if (empty($data['category_id'])) {
            Response::json([
                "message" => "Category required"
            ], 422);
        }

        if (empty($data['name'])) {
            Response::json([
                "message" => "Sub category name required"
            ], 422);
        }

        $categoryExists = $this->subCategoryModel
            ->categoryExists((int)$data['category_id']);

        if (!$categoryExists) {
            Response::json([
                "message" => "Category not found"
            ], 404);
        }

        $updated = $this->subCategoryModel->update(
            (int)$id,
            (int)$data['category_id'],
            trim($data['name']),
            trim($data['description'] ?? ''),
            (int)($data['is_active'] ?? 1)
        );

        if (!$updated) {
            Response::json([
                "message" => "Sub category not found or no changes made"
            ], 404);
        }

        Response::json([
            "message" => "Sub category updated successfully"
        ]);
    }

    // Delete
    public function delete($id)
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

        $deleted = $this->subCategoryModel->delete((int)$id);

        if (!$deleted) {
            Response::json([
                "message" => "Sub category not found"
            ], 404);
        }

        Response::json([
            "message" => "Sub category deleted successfully"
        ]);
    }
}