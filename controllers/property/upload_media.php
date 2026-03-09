<?php

function uploadMedia($property_id, $db)
{
    $uploadDir = __DIR__ . '/../../uploads/properties/' . $property_id . '/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {

            if ($_FILES['images']['error'][$key] === 0) {

                $fileName = time() . '_' . basename($_FILES['images']['name'][$key]);
                $targetFile = $uploadDir . $fileName;

                move_uploaded_file($tmp_name, $targetFile);

                $stmt = $db->prepare("
                    INSERT INTO property_media
                    (property_id, file_path, media_type, is_primary)
                    VALUES (?, ?, 'image', 0)
                ");

                $stmt->execute([
                    $property_id,
                    'uploads/properties/' . $property_id . '/' . $fileName
                ]);
            }
        }
    }

    
    if (!empty($_FILES['videos']['name'][0])) {
        foreach ($_FILES['videos']['tmp_name'] as $key => $tmp_name) {

            if ($_FILES['videos']['error'][$key] === 0) {

                $fileName = time() . '_' . basename($_FILES['videos']['name'][$key]);
                $targetFile = $uploadDir . $fileName;

                move_uploaded_file($tmp_name, $targetFile);

                $stmt = $db->prepare("
                    INSERT INTO property_media
                    (property_id, file_path, media_type, is_primary)
                    VALUES (?, ?, 'video', 0)
                ");

                $stmt->execute([
                    $property_id,
                    'uploads/properties/' . $property_id . '/' . $fileName
                ]);
            }
        }
    }
}