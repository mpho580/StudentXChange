# StudentXChange
StudentXChange

**StudentXChange** is a campus-focused, peer-to-peer marketplace project. It gives students one place to list items they no longer need and discover affordable items offered by other students, with the intention of arranging a convenient on-campus hand-off.

This README can be used as both a project overview and a guide for a presentation or live demonstration.

## The problem

Students often need affordable textbooks, electronics, furniture, stationery, and other campus essentials. At the same time, useful items go unused when students move on or no longer need them. StudentXChange connects student buyers and sellers in one searchable marketplace and supports direct communication about a listing.

## Project goals

- Make it straightforward to post and discover second-hand items.
- Organize listings by category, price, and item condition.
- Give interested buyers a way to contact sellers through the site.
- Encourage reuse and affordable exchanges within the campus community.

## Main features

- **Student accounts:** Registration collects a student number and institutional email. The current registration flow accepts `@my.richfield.ac.za` addresses and checks that the student number appears in the email address. Sign-in accepts an email address or student number.
- **Marketplace listings:** Signed-in users can publish an item with a title, description, price in South African rand, category, condition, and optional image. Sellers can manage their own listings and mark items as sold.
- **Browse and search:** Visitors can view recent listings, search by text, and filter by category, price range, and condition.
- **Listing details:** Each listing displays the item information, price, availability, and seller name.
- **Buyer–seller contact:** Signed-in buyers can express interest and exchange messages with the seller. An inbox groups conversations by listing and shows unread message counts.
- **Favorites and profiles:** Signed-in users can save listings and view or manage profile information.
- **Administration:** Admin users have a dashboard for reviewing user and product records and managing accounts and listings.

## Typical user journey

1. A student registers with their student details and institutional email, then signs in.
2. A seller creates a listing and adds its description, price, category, condition, and (optionally) a photo.
3. Another student searches or browses listings, opens an item, and saves it or contacts the seller.
4. Buyer and seller discuss the item and arrange their exchange directly. The seller can mark the listing as sold.

## Technology

- PHP
- MySQL or MariaDB
- HTML, CSS, and JavaScript
- Bootstrap 5 and Bootstrap Icons (loaded from a CDN)
- XAMPP is the intended local development environment

The PHP pages are grouped into `public/` (main site pages), `listing/` (listing workflows), and `message/` (inbox and conversations). Shared authentication, database, and marketplace functions are in `src/`. Styles and browser-side behavior are in `assets/`. The database setup is in `sql/student_marketplace.sql`.

## Run locally with XAMPP

1. Place the project folder in XAMPP's `htdocs` directory. The current pages use `/student_marketplace` as the base URL, so the folder should be named `student_marketplace` unless those paths are updated.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin, create/import the database by importing `sql/student_marketplace.sql`. The SQL dump creates the `student_marketplace` database and its tables.
4. Check the database settings in `src/db.php`. The defaults are host `localhost`, user `root`, a blank password, and database `student_marketplace`; update these if your local MySQL setup differs.
5. Open [http://localhost/student_marketplace/public/](http://localhost/student_marketplace/public/) in a browser.

Bootstrap styling and icons are loaded from public CDNs, so the browser needs internet access for those assets to appear. For a clean presentation demo, register a fresh account and create sample listings rather than relying on the example records in the SQL dump.
