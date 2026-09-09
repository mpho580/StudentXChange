<?php
	function createTransaction($productId, $buyerId, $sellerId, $amount)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO transactions
        (product_id, buyer_id, seller_id, amount)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iiid",
        $productId,
        $buyerId,
        $sellerId,
        $amount
    );

    return $stmt->execute();
}

function getPurchases($userId)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT *
        FROM transactions
        WHERE buyer_id = ?
    ");

    $stmt->bind_param("i", $userId);

    $stmt->execute();

    return $stmt->get_result();
}

function getSales($sellerId)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT *
        FROM transactions
        WHERE seller_id = ?
    ");

    $stmt->bind_param("i", $sellerId);

    $stmt->execute();

    return $stmt->get_result();
}
?>