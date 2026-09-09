<?php
require_once __DIR__ . '/db.php';

/**
 * Send an Internal Message from Buyer to Seller
 */
function sendMessage($senderId, $receiverId, $productId, $messageText) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, product_id, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $senderId, $receiverId, $productId, $messageText);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/**
 * Fetch the Conversation List (Inbox) for the Logged-In Student
 */
function getInbox($userId) {
    global $conn;
    $sql = "SELECT DISTINCT
                CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as partner_id,
                u.name as partner_name, u.profile_picture as partner_picture,
                p.id as product_id, p.title as product_title, p.price as product_price,
                (SELECT message FROM messages WHERE 
                    ((sender_id = m.sender_id AND receiver_id = m.receiver_id) OR (sender_id = m.receiver_id AND receiver_id = m.sender_id))
                    AND product_id = m.product_id
                    ORDER BY sent_at DESC LIMIT 1) as last_message,
                (SELECT sent_at FROM messages WHERE 
                    ((sender_id = m.sender_id AND receiver_id = m.receiver_id) OR (sender_id = m.receiver_id AND receiver_id = m.sender_id))
                    AND product_id = m.product_id
                    ORDER BY sent_at DESC LIMIT 1) as last_message_time
            FROM messages m
            JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u.id
            JOIN products p ON m.product_id = p.id
            WHERE m.sender_id = ? OR m.receiver_id = ?
            ORDER BY last_message_time DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $userId, $userId, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        $conversations[] = $row;
    }
    $stmt->close();
    return $conversations;
}

/**
 * Fetch the Full Conversation Thread for a Specific Product Listing
 */
function getConversation($userId, $partnerId, $productId) {
    global $conn;
    $stmt = $conn->prepare("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)) AND m.product_id = ? ORDER BY m.created_at ASC");
    $stmt->bind_param("iiiii", $userId, $partnerId, $partnerId, $userId, $productId);
    $stmt->execute();
    
    // Auto-Mark incoming messages as read
    $updateStmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND product_id = ?");
    $updateStmt->bind_param("iii", $userId, $partnerId, $productId);
    $updateStmt->execute();
    $updateStmt->close();
    
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
    return $messages;
}


?>