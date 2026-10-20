<?php
require_once __DIR__ . '/db.php';

/**
 * Add a New Product Listing to the Database
 */
function createListing($userId, $title, $description, $price, $categoryId, $condition, $imagePath) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO products (user_id, title, description, price, category_id, item_condition, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssiss", $userId, $title, $description, $price, $categoryId, $condition, $imagePath);
    if ($stmt->execute()) {
        $listingId = $stmt->insert_id;
        $stmt->close();
        return $listingId;
    }
    $stmt->close();
    return false;
}

/**
 * Search and Filter Listings Dynamically with Prepared Statements
 */
function getListings($search = '', $categoryId = null, $minPrice = null, $maxPrice = null, $condition = null) {
    global $conn;
$sql = "SELECT
            p.*,
            c.name AS category_name,
            u.name AS seller_name
        FROM products p
        JOIN categories c
            ON p.category_id = c.id
        JOIN users u
            ON p.user_id = u.id
        WHERE 1=1";    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
        $searchParam = "%" . $search . "%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }
    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
        $types .= "i";
    }
    if ($minPrice !== null && $minPrice !== '') {
        $sql .= " AND p.price >= ?";
        $params[] = $minPrice;
        $types .= "d";
    }
    if ($maxPrice !== null && $maxPrice !== '') {
        $sql .= " AND p.price <= ?";
        $params[] = $maxPrice;
        $types .= "d";
    }
    if (!empty($condition)) {
        $sql .= " AND p.item_condition = ?";
        $params[] = $condition;
        $types .= "s";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $listings = [];
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
    $stmt->close();
    return $listings;
}

/**
 * Fetch a Single Item Listing with Seller Contact Details
 */
function getListing($listingId) {
    global $conn;
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, u.name as seller_name, u.email as seller_email, u.phone as seller_phone, u.profile_picture as seller_picture FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.user_id = u.id WHERE p.id = ?");
    $stmt->bind_param("i", $listingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $listing = $result->fetch_assoc();
    $stmt->close();
    return $listing;
}

function getUserListings($userId)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c
            ON p.category_id = c.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result();
}

function deleteListing($listingId, $userId)
{
    global $conn;

    $stmt = $conn->prepare("
        DELETE FROM products
        WHERE id = ?
        AND user_id = ?
    ");

    $stmt->bind_param("ii", $listingId, $userId);

    return $stmt->execute();
}

function markListingSold($listingId, $userId)
{
    global $conn;

    $stmt = $conn->prepare("
        UPDATE products
        SET status='Sold'
        WHERE id=?
        AND user_id=?
    ");

    $stmt->bind_param("ii", $listingId, $userId);

    return $stmt->execute();
}

function updateListing(
    $id,
    $userId,
    $title,
    $description,
    $price,
    $categoryId,
    $condition
)
{
    global $conn;

    $stmt = $conn->prepare("
        UPDATE products
        SET
            title=?,
            description=?,
            price=?,
            category_id=?,
            item_condition=?
        WHERE
            id=?
        AND
            user_id=?
    ");

    $stmt->bind_param(
        "ssdisii",
        $title,
        $description,
        $price,
        $categoryId,
        $condition,
        $id,
        $userId
    );

    return $stmt->execute();
}

?>