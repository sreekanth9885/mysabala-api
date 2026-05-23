<?php

require_once __DIR__ . '/../Core/Response.php';

class UploadController
{
    public function uploadFoodImage()
    {
        if (!isset($_FILES['image'])) {
            Response::json([
                "message" => "Image required"
            ], 422);
        }

        $file = $_FILES['image'];

        // Validate Image
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($file['type'], $allowedTypes)) {
            Response::json([
                "message" => "Invalid image type"
            ], 422);
        }

        // Create Upload Directory
        $uploadDir = __DIR__ . '/../../uploads/food-items/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate Unique File Name
        $extension = pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        );

        $fileName = uniqid() . '.' . $extension;

        $destination = $uploadDir . $fileName;

        // Move File
        if (!move_uploaded_file(
            $file['tmp_name'],
            $destination
        )) {
            Response::json([
                "message" => "Upload failed"
            ], 500);
        }

        // Public Path
        $imageUrl = 'uploads/food-items/' . $fileName;

        Response::json([
            "message" => "Image uploaded successfully",
            "image_url" => $imageUrl
        ]);
    }
}