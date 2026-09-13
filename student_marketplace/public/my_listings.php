<?php

	require_once "../src/auth.php";
	require_once "../src/listings.php";

	requireLogin();

	$listings = getUserListings($_SESSION['user_id']);

	include "header.php";
?>

	<div class="d-flex justify-content-between align-items-center mb-4">
		<div><h1 class="h2 mb-1">My Listings</h1><p class="text-secondary mb-0">Items you have published for sale.</p></div>
		<a class="btn btn-primary" href="/student_marketplace/listing/create.php">Sell an item</a>
	</div>

	<?php if ($listings === false || $listings->num_rows === 0): ?>
		<div class="card border-0 shadow-sm text-center p-5">
			<i class="bi bi-box-seam fs-1 text-primary mb-3"></i>
			<h2 class="h4">No listings yet</h2>
			<p class="text-secondary">Listings appear here when they are published from your account.</p>
			<div><a class="btn btn-primary" href="/student_marketplace/listing/create.php">Create your first listing</a></div>
		</div>
	<?php else: ?>

	<div class="row">

		<?php while($listing = $listings->fetch_assoc()): ?>

		<div class="col-md-4">

			<div class="card shadow mb-4">

			<img src="<?= htmlspecialchars($listing['image_path']) ?>"
			class="card-img-top"
			style="height:220px;object-fit:cover;">

				<div class="card-body">

					<h5><?= htmlspecialchars($listing['title']) ?></h5>

					<p class="text-success fw-bold">
						R<?= number_format($listing['price'],2) ?>
					</p>

					<p><?= htmlspecialchars($listing['category_name']) ?></p>

					<span class="badge bg-success">
						<?= htmlspecialchars($listing['status']) ?>
					</span>

					<hr>

					<a
						class="btn btn-primary btn-sm"
						href="/student_marketplace/listing/view.php?id=<?= (int) $listing['id'] ?>">
						View
					</a>

					<a
						class="btn btn-warning btn-sm"
						href="/student_marketplace/listing/edit.php?id=<?= (int) $listing['id'] ?>">
						Edit
					</a>

					<a
						class="btn btn-danger btn-sm"
						href="/student_marketplace/listing/delete.php?id=<?= (int) $listing['id'] ?>">
						Delete
					</a>

					<a
						class="btn btn-success btn-sm"
						href="/student_marketplace/listing/sold.php?id=<?= (int) $listing['id'] ?>">
						Sold
					</a>

				</div>

			</div>

		</div>

	<?php endwhile; ?>

	</div>
	<?php endif; ?>

<?php include "footer.php"; ?>
