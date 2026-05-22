<?php
require_once __DIR__ . '/../models/SubCategoryModel.php';
require_once __DIR__ . '/../helpers/JwtHelper.php';

class SubCategoryController
{
    private SubCategoryModel $model;

    public function __construct(PDO $db)
    {
        $this->model = new SubCategoryModel($db);
    }

    public function create()
    {
        $user = JwtHelper::getUserFromToken();

        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['name']) || empty($data['category_id'])) {
            Response::json(["message" => "Sub-category name and category ID required"], 422);
        }

        try {
            $id = $this->model->create(
                (int)$data['category_id'],
                trim($data['name']),
                $data['description'] ?? null
            );

            Response::json([
                "message" => "Sub-category created",
                "id" => $id
            ], 201);

        } catch (Exception $e) {
            $status = $e->getMessage() === "Sub-category already exists in this category" ? 409 : 500;
            Response::json(["message" => $e->getMessage()], $status);
        }
    }

    public function index()
    {
        $user = JwtHelper::getUserFromToken();

        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }

        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $data = $this->model->all($categoryId);
        
        Response::json(["data" => $data]);
    }

    public function show($id)
    {
        $user = JwtHelper::getUserFromToken();

        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }
        
        $subCategory = $this->model->getById((int)$id);
        
        if (!$subCategory) {
            Response::json(["message" => "Sub-category not found"], 404);
        }
        
        Response::json(["data" => $subCategory]);
    }

    public function update($id)
    {
        $user = JwtHelper::getUserFromToken();

        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['name']) || empty($data['category_id'])) {
            Response::json(["message" => "Name and category ID required"], 422);
        }

        try {
            $updated = $this->model->update(
                (int)$id,
                (int)$data['category_id'],
                trim($data['name']),
                $data['description'] ?? null
            );

            if (!$updated) {
                Response::json(["message" => "Sub-category not found"], 404);
            }

            Response::json(["message" => "Sub-category updated"]);

        } catch (Exception $e) {
            $status = $e->getMessage() === "Duplicate sub-category" ? 409 : 500;
            Response::json(["message" => $e->getMessage()], $status);
        }
    }

    public function delete($id)
    {
        $user = JwtHelper::getUserFromToken();

        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }

        try {
            $deleted = $this->model->delete((int)$id);

            if (!$deleted) {
                Response::json(["message" => "Sub-category not found"], 404);
            }

            Response::json(["message" => "Sub-category deleted"]);
            
        } catch (Exception $e) {
            $status = $e->getMessage() === "Cannot delete sub-category. Products are linked." ? 409 : 500;
            Response::json(["message" => $e->getMessage()], $status);
        }
    }
}