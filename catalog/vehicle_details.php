<?php
require_once __DIR__ . '/../includes/db.php';

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$vehicleId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$vehicle = null;

if ($vehicleId !== false && $vehicleId !== null && $vehicleId > 0) {
    $sql = "
        SELECT
            v.VehicleID,
            v.Color AS color,
            v.Mileage AS mileage,
            v.DailyRate AS price,
            vm.Make AS make,
            vm.ModelName AS model,
            vm.Year AS year,
            vm.FuelType AS fuel,
            vm.Seats AS seats,
            vt.TypeName AS type,
            rl.Name AS location
        FROM Vehicle v
        JOIN VehicleModel vm
            ON v.ModelID = vm.ModelID
        JOIN VehicleType vt
            ON vm.TypeID = vt.TypeID
        JOIN RentalLocation rl
            ON v.LocationID = rl.LocationID
        WHERE v.VehicleID = ?
    ";

    $result = $conn->execute_query($sql, [$vehicleId]);

    $vehicle = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>RoadMate | Vehicle Details</title>

    <link rel="stylesheet" href="catalog.css">
</head>
<body>

<header class="header">
    <h1>RoadMate</h1>
    <span class="page-label">Vehicle Details</span>
</header>

<main class="container">

    <p>
        <a href="search.php">← Back to Vehicle Search</a>
    </p>

    <?php if ($vehicle === null): ?>

        <section class="empty-state">
            <h2>Vehicle Not Found</h2>
            <p class="muted">
                The requested vehicle does not exist.
            </p>
        </section>

    <?php else: ?>

        <section class="details-card">

            <h2>
                <?= htmlspecialchars(
                    $vehicle['make'] . ' ' . $vehicle['model']
                ) ?>
            </h2>

            <p class="subtitle">
                Available for rental at
                <?= htmlspecialchars($vehicle['location']) ?>
            </p>

            <div class="details-grid">

                <div>
                    <strong>Vehicle Type</strong>
                    <?= htmlspecialchars($vehicle['type']) ?>
                </div>

                <div>
                    <strong>Year</strong>
                    <?= htmlspecialchars($vehicle['year']) ?>
                </div>

                <div>
                    <strong>Fuel Type</strong>
                    <?= htmlspecialchars($vehicle['fuel']) ?>
                </div>

                <div>
                    <strong>Seats</strong>
                    <?= htmlspecialchars($vehicle['seats']) ?>
                </div>

                <div>
                    <strong>Color</strong>
                    <?= htmlspecialchars($vehicle['color']) ?>
                </div>

                <div>
                    <strong>Mileage</strong>
                    <?= number_format($vehicle['mileage']) ?> miles
                </div>

                <div>
                    <strong>Pickup Location</strong>
                    <?= htmlspecialchars($vehicle['location']) ?>
                </div>

                <div>
                    <strong>Daily Price</strong>
                    $<?= number_format($vehicle['price'], 2) ?>
                </div>

            </div>

            <a class="button" href="../reservation/create_reservation.php?vehicle_id=<?= $vehicleId ?>">
                Reserve Now
            </a>

        </section>

    <?php endif; ?>

</main>

</body>
</html>