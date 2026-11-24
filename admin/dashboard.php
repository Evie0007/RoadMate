
<?php
// Temporary sample data.
// Replace with MySQL queries after the database is ready.

$totalVehicles = 24;
$totalReservations = 18;
$totalRevenue = 6250;
$totalMaintenance = 3;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoadMate | Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="header">
    <h1>RoadMate</h1>
    <span class="admin-label">Admin Dashboard</span>
</header>

<main class="container">
    <h2>Dashboard Overview</h2>
    <p class="subtitle">Manage your car rental system.</p>

    <section class="stats">
        <div class="stat-card">
            <h3>Total Vehicles</h3>
            <p><?= $totalVehicles ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Reservations</h3>
            <p><?= $totalReservations ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Revenue</h3>
            <p>$<?= number_format($totalRevenue, 2) ?></p>
        </div>

        <div class="stat-card">
            <h3>Maintenance Records</h3>
            <p><?= $totalMaintenance ?></p>
        </div>
    </section>

    <h2>Management</h2>

    <section class="management">
        <a href="vehicles.php" class="management-card">
            <h3>Vehicle Management</h3>
            <p>Add, edit, and remove vehicles.</p>
        </a>

        <a href="reports.php" class="management-card">
            <h3>Reports</h3>
            <p>View revenue and rental analytics.</p>
        </a>
        <a href="discounts.php" class="management-card">
            <h3>Discount Management</h3>
            <p>Add, edit, and remove discount codes.</p>
        </a>

        <a href="maintenance.php" class="management-card">
            <h3>Maintenance Management</h3>
            <p>Add, edit, and view maintenance records.</p>
        </a>

        <a href="models_locations.php" class="management-card">
            <h3>Models & Locations</h3>
            <p>Manage vehicle models and rental locations.</p>
        </a>

        <a href="reservations_payments.php" class="management-card">
            <h3>Reservations & Payments</h3>
            <p>View reservations, payments, and update statuses.</p>
        </a>



    </section>
</main>

</body>
</html>
