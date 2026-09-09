<?php function addFavorite($userId, $productId)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT IGNORE INTO favorites(user_id, product_id)
        VALUES (?, ?)
    ");

    $stmt->bind_param("ii", $userId, $productId);

    return $stmt->execute();
}

function removeFavorite($userId, $productId)
{
    global $conn;

    $stmt = $conn->prepare("
        DELETE FROM favorites
        WHERE user_id = ?
        AND product_id = ?
    ");

    $stmt->bind_param("ii", $userId, $productId);

    return $stmt->execute();
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

    $stmt->bind_param("i", $userId);

    $stmt->execute();

    return $stmt->get_result();
}
?>