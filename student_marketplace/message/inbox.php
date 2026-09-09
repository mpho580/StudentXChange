<?php

require_once "../src/auth.php";
require_once "../src/messages.php";

requireLogin();

include "../public/header.php";

$messages = getInbox($_SESSION['user_id']);
?>

<h2 class="mb-4">
    <i class="bi bi-chat-dots"></i>
    Inbox
</h2>

<?php if (empty($messages)): ?>

<div class="alert alert-info">
    You have no conversations yet.
</div>

<?php else: ?>

<?php while($chat = $messages->fetch_assoc()): ?>

<div class="card shadow-sm mb-3">

    <div class="card-body">

        <div class="d-flex justify-content-between">

            <div>

                <h5 class="mb-1">

                    <?= htmlspecialchars($chat['partner_name']) ?>

                </h5>

                <small class="text-muted">

                    Product:
                    <?= htmlspecialchars($chat['title']) ?>

                </small>

            </div>

            <div class="text-end">

                <small class="text-muted">

                    <?= date(
                        "d M Y H:i",
                        strtotime($chat['last_message'])
                    ) ?>

                </small>

                <br>

                <a
                class="btn btn-primary btn-sm mt-2"
                href="thread.php?partner_id=<?= $chat['partner_id'] ?>&product_id=<?= $chat['product_id'] ?>">

                    Open Chat

                </a>

            </div>

        </div>

    </div>

</div>

<?php endwhile; ?>

<?php endif; ?>

<?php include "../public/footer.php"; ?>