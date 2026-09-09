<?php

require_once "../../src/auth.php";
require_once "../../src/listings.php";

requireLogin();

if (!isset($_GET['id'])) {
    header("Location: ../my_listings.php");
    exit;
}

$id = (int)$_GET['id'];

deleteListing($id, $_SESSION['user_id']);

header("Location: ../my_listings.php");
exit;
?>