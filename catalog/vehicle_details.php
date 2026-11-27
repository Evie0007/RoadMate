<?php
$vehicles = [
    1 => [
        'make' => 'Toyota',
        'model' => 'Corolla',
        'year' => 2023,
        'type' => 'Sedan',
        'fuel' => 'Gasoline',
        'seats' => 5,
        'color' => 'White',
        'mileage' => 12000,
        'price' => 55,
        'location' => 'San Jose'
    ],
    2 => [
        'make' => 'Honda',
        'model' => 'CR-V',
        'year' => 2022,
        'type' => 'SUV',
        'fuel' => 'Gasoline',
        'seats' => 5,
        'color' => 'Blue',
        'mileage' => 18000,
        'price' => 75,
        'location' => 'San Francisco'
    ],
    3 => [
        'make' => 'Tesla',
        'model' => 'Model 3',
        'year' => 2024,
        'type' => 'Electric',
        'fuel' => 'Electric',
        'seats' => 5,
        'color' => 'Black',
        'mileage' => 7000,
        'price' => 90,
        'location' => 'San Jose'
    ]
];

$vehicleId = $_GET['id'] ?? 0;
$vehicle = $vehicles[$vehicleId] ?? null;
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