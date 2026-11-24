
<?php
session_start();

// Demo data until the MySQL database is available.
$reservations = [
    ['vehicle' => 'Toyota Corolla', 'location' => 'San Jose',
     'date' => '2026-10-15', 'amount' => 165, 'status' => 'Completed'],
    ['vehicle' => 'Honda CR-V', 'location' => 'San Francisco',
     'date' => '2026-10-22', 'amount' => 300, 'status' => 'Completed'],
    ['vehicle' => 'Toyota Corolla', 'location' => 'San Jose',
     'date' => '2026-11-03', 'amount' => 220, 'status' => 'Completed'],
    ['vehicle' => 'Tesla Model 3', 'location' => 'San Jose',
     'date' => '2026-11-10', 'amount' => 270, 'status' => 'Cancelled'],
    ['vehicle' => 'Honda CR-V', 'location' => 'San Francisco',
     'date' => '2026-11-18', 'amount' => 375, 'status' => 'Confirmed']
];

$popular = [];
$revenueByMonth = [];
$revenueByLocation = [];

foreach ($reservations as $reservation) {
    if ($reservation['status'] === 'Cancelled') {
        continue;
    }

    $vehicle = $reservation['vehicle'];
    $popular[$vehicle] = ($popular[$vehicle] ?? 0) + 1;

    // Demo assumption: count completed reservations as revenue.
    if ($reservation['status'] !== 'Completed') {
        continue;
    }

    $month = substr($reservation['date'], 0, 7);
    $location = $reservation['location'];

    $revenueByMonth[$month] =
        ($revenueByMonth[$month] ?? 0) + $reservation['amount'];

    $revenueByLocation[$location] =
        ($revenueByLocation[$location] ?? 0) + $reservation['amount'];
}

arsort($popular);
ksort($revenueByMonth);
arsort($revenueByLocation);

$maintenance = $_SESSION['demo_maintenance'] ?? [
    ['vehicle' => 'Toyota Corolla', 'service' => 'Oil Change',
     'date' => '2026-11-10', 'cost' => 80],
    ['vehicle' => 'Honda CR-V', 'service' => 'Tire Replacement',
     'date' => '2026-11-15', 'cost' => 320]
];

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoadMate | Analytics Reports</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<header class="header">
    <h1>RoadMate</h1>
    <span class="admin-label">Analytics Reports</span>
</header>

<main class="container">
    <a href="dashboard.php">← Back to Dashboard</a>
    <h2>Analytics Reports</h2>
    <p class="subtitle">Analyze rentals, revenue, and maintenance.</p>

    <h2>Most Popular Vehicles</h2>
    <div class="stat-card">
        <table width="100%" cellpadding="12">
            <tr><th>Vehicle</th><th>Reservations</th></tr>
            <?php foreach ($popular as $vehicle => $count): ?>
                <tr>
                    <td><?= e($vehicle) ?></td>
                    <td><?= $count ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <h2>Revenue by Month</h2>
    <div class="stat-card">
        <table width="100%" cellpadding="12">
            <tr><th>Month</th><th>Revenue</th></tr>
            <?php foreach ($revenueByMonth as $month => $amount): ?>
                <tr>
                    <td><?= e($month) ?></td>
                    <td>$<?= number_format($amount, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <h2>Revenue by Location</h2>
    <div class="stat-card">
        <table width="100%" cellpadding="12">
            <tr><th>Location</th><th>Revenue</th></tr>
            <?php foreach ($revenueByLocation as $location => $amount): ?>
                <tr>
                    <td><?= e($location) ?></td>
                    <td>$<?= number_format($amount, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <h2>Maintenance History</h2>
    <div class="stat-card">
        <table width="100%" cellpadding="12">
            <tr>
                <th>Vehicle</th><th>Service</th>
                <th>Date</th><th>Cost</th>
            </tr>
            <?php foreach ($maintenance as $record): ?>
                <tr>
                    <td><?= e($record['vehicle']) ?></td>
                    <td><?= e($record['service']) ?></td>
                    <td><?= e($record['date']) ?></td>
                    <td>$<?= number_format($record['cost'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
