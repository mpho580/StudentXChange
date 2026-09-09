<?php

	require_once "../src/auth.php";
	require_once "../src/listings.php";

	requireLogin();

	$listings = getUserListings($_SESSION['user_id']);

	include "header.php";
?>

	<h2 class="mb-4">My Listings</h2>

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
						href="listing/view.php?id=<?= $listing['id'] ?>">
						View
					</a>

					<a
						class="btn btn-warning btn-sm"
						href="listing/edit.php?id=<?= $listing['id'] ?>">
						Edit
					</a>

					<a
						class="btn btn-danger btn-sm"
						href="listing/delete.php?id=<?= $listing['id'] ?>">
						Delete
					</a>

					<a
						class="btn btn-success btn-sm"
						href="listing/sold.php?id=<?= $listing['id'] ?>">
						Sold
					</a>

				</div>

			</div>

		</div>

	<?php endwhile; ?>

	</div>

<?php include "footer.php"; ?>