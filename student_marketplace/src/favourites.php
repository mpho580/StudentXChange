<?php
function addFavorite($userId, $productId)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT IGNORE INTO favorites(user_id, product_id)
        VALUES (?, ?)
    ");

    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param("ii", $userId, $productId);
    $saved = $stmt->execute();
    $stmt->close();
    return $saved;
}

function removeFavorite($userId, $productId)
{
    global $conn;

    $stmt = $conn->prepare("
        DELETE FROM favorites
        WHERE user_id = ?
        AND product_id = ?
    ");

    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param("ii", $userId, $productId);
    $removed = $stmt->execute();
    $stmt->close();
    return $removed;
}

function getFavorites($userId)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT p.*
        FROM favorites f
        JOIN products p
        ON f.product_id = p.id
        WHERE f.user_id = ?
    ");

    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    return $stmt->get_result();
}
