<?php

require_once "../../src/auth.php";
require_once "../../src/listings.php";

requireLogin();

$id = (int)$_GET['id'];

markListingSold($id, $_SESSION['user_id']);

header("Location: ../my_listings.php");
exit;
?>