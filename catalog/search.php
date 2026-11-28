<?php
require_once __DIR__ . '/../includes/db.php';

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$locations = [];
$locationResult = $conn->query(
    "SELECT Name
     FROM RentalLocation
     ORDER BY Name"
);

while ($row = $locationResult->fetch_assoc()) {
    $locations[] = $row['Name'];
}

$types = [];
$typeResult = $conn->query(
    "SELECT TypeName
     FROM VehicleType
     ORDER BY TypeName"
);

while ($row = $typeResult->fetch_assoc()) {
    $types[] = $row['TypeName'];
}

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
    } elseif (
        $maxPrice !== '' &&
        (!is_numeric($maxPrice) || (float)$maxPrice < 0)
    ) {
        $error = 'Maximum price must be a non-negative number.';
    } else {
        $conditions = [
            "v.Status = 'Available'"
        ];

        $parameters = [];

        if ($location !== '') {
            $conditions[] = "rl.Name = ?";
            $parameters[] = $location;
        }

        if ($type !== '') {
            $conditions[] = "vt.TypeName = ?";
            $parameters[] = $type;
        }

        if ($maxPrice !== '') {
            $conditions[] = "v.DailyRate <= ?";
            $parameters[] = (float)$maxPrice;
        }

        $conditions[] = "
            NOT EXISTS (
                SELECT 1
                FROM Reservation r
                WHERE r.VehicleID = v.VehicleID
                  AND r.Status NOT IN ('Cancelled', 'Completed')
                  AND r.PickupDate < ?
                  AND r.ReturnDate > ?
            )
        ";

        $parameters[] = $returnDate . ' 00:00:00';
        $parameters[] = $pickupDate . ' 00:00:00';

        $sql = "
            SELECT
                v.VehicleID,
                v.DailyRate,
                vm.Make,
                vm.ModelName,
                vm.Year,
                vm.FuelType,
                vm.Seats,
                vt.TypeName,
                rl.Name AS LocationName
            FROM Vehicle v
            JOIN VehicleModel vm
                ON v.ModelID = vm.ModelID
            JOIN VehicleType vt
                ON vm.TypeID = vt.TypeID
            JOIN RentalLocation rl
                ON v.LocationID = rl.LocationID
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY v.DailyRate ASC,
                     vm.Make ASC,
                     vm.ModelName ASC
        ";

        $vehicleResult = $conn->execute_query($sql, $parameters);

        while ($row = $vehicleResult->fetch_assoc()) {
            $vehicleId = (int)$row['VehicleID'];

            $results[$vehicleId] = [
                'make' => $row['Make'],
                'model' => $row['ModelName'],
                'year' => $row['Year'],
                'type' => $row['TypeName'],
                'fuel' => $row['FuelType'],
                'seats' => $row['Seats'],
                'price' => $row['DailyRate'],
                'location' => $row['LocationName']
            ];
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
                            <option
                                value="<?= h($item) ?>"
                                <?= $location === $item ? 'selected' : '' ?>
                            >
                                <?= h($item) ?>
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
                        value="<?= h($pickupDate) ?>"
                        required
                    >
                </div>

                <div>
                    <label for="return_date">Return Date</label>

                    <input
                        type="date"
                        id="return_date"
                        name="return_date"
                        value="<?= h($returnDate) ?>"
                        required
                    >
                </div>

                <div>
                    <label for="type">Vehicle Type</label>

                    <select id="type" name="type">
                        <option value="">Any type</option>

                        <?php foreach ($types as $item): ?>
                            <option
                                value="<?= h($item) ?>"
                                <?= $type === $item ? 'selected' : '' ?>
                            >
                                <?= h($item) ?>
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
                        value="<?= h($maxPrice) ?>"
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
            <?= h($error) ?>
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
                            <?= h($vehicle['make'] . ' ' . $vehicle['model']) ?>
                        </h3>

                        <p class="vehicle-meta">
                            Year: <?= h($vehicle['year']) ?><br>
                            Type: <?= h($vehicle['type']) ?><br>
                            Fuel: <?= h($vehicle['fuel']) ?><br>
                            Seats: <?= h($vehicle['seats']) ?><br>
                            Location: <?= h($vehicle['location']) ?>
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