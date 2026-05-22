<?php
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../helpers/JwtHelper.php';

class CategoryController
{
    private CategoryModel $model;

    public function __construct(PDO $db)
    {
        $this->model = new CategoryModel($db);
    }

    public function create()
    {
        $user = JwtHelper::getUserFromToken();

        // Change to lowercase to match your token
        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['name'])) {
            Response::json(["message" => "Category name required"], 422);
        }

        try {
            $description = $data['description'] ?? null;
            
            $id = $this->model->create(
                trim($data['name']),
                $description ? trim($description) : null
            );

            Response::json([
                "message" => "Category created",
                "id" => $id
            ], 201);

        } catch (Exception $e) {
            $status = $e->getMessage() === "Category already exists" ? 409 : 500;
            Response::json(["message" => $e->getMessage()], $status);
        }
    }

    public function index()
    {
        $user = JwtHelper::getUserFromToken();

        // Change to lowercase to match your token
        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }

        $data = $this->model->all();
        Response::json(["data" => $data]);
    }

    public function show($id)
    {
        $user = JwtHelper::getUserFromToken();

        // Change to lowercase to match your token
        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }
        
        $category = $this->model->getById((int)$id);
        
        if (!$category) {
            Response::json(["message" => "Category not found"], 404);
        }
        
        Response::json(["data" => $category]);
    }

    public function update($id)
    {
        $user = JwtHelper::getUserFromToken();

        // Change to lowercase to match your token
        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['name'])) {
            Response::json(["message" => "Name required"], 422);
        }

        try {
            $description = $data['description'] ?? null;
            
            $updated = $this->model->update(
                (int)$id,
                trim($data['name']),
                $description ? trim($description) : null
            );

            if (!$updated) {
                Response::json(["message" => "Category not found"], 404);
            }

            Response::json(["message" => "Category updated"]);

        } catch (Exception $e) {
            $status = $e->getMessage() === "Duplicate category" ? 409 : 500;
            Response::json(["message" => $e->getMessage()], $status);
        }
    }

    public function delete($id)
    {
        $user = JwtHelper::getUserFromToken();

        // Change to lowercase to match your token
        if (!in_array($user['role'], ['admin', 'super_admin', 'store_admin'])) {
            Response::json(["message" => "Forbidden"], 403);
        }

        try {
            $deleted = $this->model->delete((int)$id);

            if (!$deleted) {
                Response::json(["message" => "Category not found"], 404);
            }

            Response::json(["message" => "Category deleted"]);
            
        } catch (Exception $e) {
            $status = $e->getMessage() === "Cannot delete category. Products are linked." ? 409 : 500;
            Response::json(["message" => $e->getMessage()], $status);
        }
    }
}