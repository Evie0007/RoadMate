<?php
$vehicles = [
    1 => [
        'make' => 'Toyota',
        'model' => 'Corolla',
        'year' => 2023,
        'type' => 'Sedan',
        'fuel' => 'Gasoline',
        'seats' => 5,
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
        'price' => 90,
        'location' => 'San Jose'
    ]
];

$reservations = [
    [
        'vehicle_id' => 1,
        'pickup_date' => '2026-12-10',
        'return_date' => '2026-12-14',
        'status' => 'Confirmed'
    ]
];

$locations = ['San Jose', 'San Francisco'];
$types = ['Sedan', 'SUV', 'Electric'];

$pickupDate = $_GET['pickup_date'] ?? '';
$returnDate = $_GET['return_date'] ?? '';
$location = $_GET['location'] ?? '';
$type = $_GET['type'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

$searched = isset($_GET['search']);
$error = '';
$results = [];

if ($searched) {
    if ($pickupDate === '' || $returnDate === '') {
        $error = 'Please enter both pickup and return dates.';
    } elseif ($returnDate <= $pickupDate) {
        $error = 'Return date must be after pickup date.';
    } elseif ($maxPrice !== '' && $maxPrice < 0) {
        $error = 'Maximum price cannot be negative.';
    } else {
        foreach ($vehicles as $id => $vehicle) {
            if ($location !== '' && $vehicle['location'] !== $location) {
                continue;
            }

            if ($type !== '' && $vehicle['type'] !== $type) {
                continue;
            }

            if ($maxPrice !== '' && $vehicle['price'] > $maxPrice) {
                continue;
            }

            $isAvailable = true;

            foreach ($reservations as $reservation) {
                if ($reservation['vehicle_id'] != $id) {
                    continue;
                }

                if ($reservation['status'] === 'Cancelled') {
                    continue;
                }

                $hasOverlap =
                    $reservation['pickup_date'] < $returnDate &&
                    $reservation['return_date'] > $pickupDate;

                if ($hasOverlap) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $results[$id] = $vehicle;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>RoadMate | Vehicle Search</title>

    <link rel="stylesheet" href="catalog.css">
</head>
<body>

<header class="header">
    <h1>RoadMate</h1>
    <span class="page-label">Vehicle Catalog</span>
</header>

<main class="container">

    <h2>Find Your Rental Vehicle</h2>

    <p class="subtitle">
        Search available vehicles by location, dates, type, and price.
    </p>

    <section class="search-panel">

        <form method="GET" action="search.php">

            <div class="filter-grid">

                <div>
                    <label for="location">Pickup Location</label>

                    <select id="location" name="location">
                        <option value="">Any location</option>

                        <?php foreach ($locations as $item): ?>
                            <option value="<?= htmlspecialchars($item) ?>"
                                <?= $location === $item ? 'selected' : '' ?>>
                                <?= htmlspecialchars($item) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="pickup_date">Pickup Date</label>

                    <input
                        type="date"
                        id="pickup_date"
                        name="pickup_date"
                        value="<?= htmlspecialchars($pickupDate) ?>"
                        required
                    >
                </div>

                <div>
                    <label for="return_date">Return Date</label>

                    <input
                        type="date"
                        id="return_date"
                        name="return_date"
                        value="<?= htmlspecialchars($returnDate) ?>"
                        required
                    >
                </div>

                <div>
                    <label for="type">Vehicle Type</label>

                    <select id="type" name="type">
                        <option value="">Any type</option>

                        <?php foreach ($types as $item): ?>
                            <option value="<?= htmlspecialchars($item) ?>"
                                <?= $type === $item ? 'selected' : '' ?>>
                                <?= htmlspecialchars($item) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="max_price">Maximum Daily Price</label>

                    <input
                        type="number"
                        id="max_price"
                        name="max_price"
                        min="0"
                        step="0.01"
                        value="<?= htmlspecialchars($maxPrice) ?>"
                        placeholder="No limit"
                    >
                </div>

            </div>

            <div class="search-actions">
                <button type="submit" name="search" value="1">
                    Search Vehicles
                </button>

                <a class="button secondary" href="search.php">
                    Clear
                </a>
            </div>

        </form>

    </section>

    <?php if ($error !== ''): ?>

        <p class="message">
            <?= htmlspecialchars($error) ?>
        </p>

    <?php elseif ($searched): ?>

        <div class="results-heading">
            <h2>Available Vehicles</h2>

            <span class="muted">
                <?= count($results) ?> result(s)
            </span>
        </div>

        <?php if (empty($results)): ?>

            <div class="empty-state">
                No vehicles match your search criteria.
            </div>

        <?php else: ?>

            <section class="vehicle-grid">

                <?php foreach ($results as $id => $vehicle): ?>

                    <article class="vehicle-card">

                        <h3>
                            <?= htmlspecialchars(
                                $vehicle['make'] . ' ' . $vehicle['model']
                            ) ?>
                        </h3>

                        <p class="vehicle-meta">
                            Year: <?= htmlspecialchars($vehicle['year']) ?><br>
                            Type: <?= htmlspecialchars($vehicle['type']) ?><br>
                            Fuel: <?= htmlspecialchars($vehicle['fuel']) ?><br>
                            Seats: <?= htmlspecialchars($vehicle['seats']) ?><br>
                            Location: <?= htmlspecialchars($vehicle['location']) ?>
                        </p>

                        <p class="price">
                            $<?= number_format($vehicle['price'], 2) ?> / day
                        </p>

                        <a
                            class="button"
                            href="vehicle_details.php?id=<?= $id ?>"
                        >
                            View Details
                        </a>

                    </article>

                <?php endforeach; ?>

            </section>

        <?php endif; ?>

    <?php endif; ?>

</main>

</body>
</html>