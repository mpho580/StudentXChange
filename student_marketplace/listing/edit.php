<?php

require_once "../../src/auth.php";
require_once "../../src/listings.php";

requireLogin();

$id = (int)$_GET['id'];

$item = getListing($id);

if (!$item || $item['user_id'] != $_SESSION['user_id']) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    updateListing(
        $id,
        $_SESSION['user_id'],
        $_POST['title'],
        $_POST['description'],
        $_POST['price'],
        $_POST['category_id'],
        $_POST['item_condition']
    );

    header("Location: ../my_listings.php");
    exit;
}
?>

<!DOCTYPE html>

<html>

<head>

<title>Edit Listing</title>

</head>

<body>

<h2>Edit Listing</h2>

<form method="post">

Title

<input
type="text"
name="title"
value="<?= htmlspecialchars($item['title']) ?>">

<br><br>

Description

<textarea
name="description"><?= htmlspecialchars($item['description']) ?></textarea>

<br><br>

Price

<input
type="number"
step="0.01"
name="price"
value="<?= $item['price'] ?>">

<br><br>

Category ID

<input
type="number"
name="category_id"
value="<?= $item['category_id'] ?>">

<br><br>

Condition

<select name="item_condition">

<option <?= $item['item_condition']=="New"?"selected":"" ?>>New</option>

<option <?= $item['item_condition']=="Like New"?"selected":"" ?>>Like New</option>

<option <?= $item['item_condition']=="Good"?"selected":"" ?>>Good</option>

<option <?= $item['item_condition']=="Fair"?"selected":"" ?>>Fair</option>

<option <?= $item['item_condition']=="Poor"?"selected":"" ?>>Poor</option>

</select>

<br><br>

<button type="submit">

Save Changes

</button>

</form>

</body>

</html>