
<?php
session_start();

// Temporary demo data until MySQL is ready.
if (!isset($_SESSION['demo_vehicles'])) {
    $_SESSION['demo_vehicles'] = [
        1 => [
            'model' => 'Toyota Corolla',
            'type' => 'Sedan',
            'location' => 'San Jose',
            'price' => 55
        ],
        2 => [
            'model' => 'Honda CR-V',
            'type' => 'SUV',
            'location' => 'San Francisco',
            'price' => 75
        ],
        3 => [
            'model' => 'Tesla Model 3',
            'type' => 'Electric',
            'location' => 'San Jose',
            'price' => 90
        ]
    ];
}

$vehicles = &$_SESSION['demo_vehicles'];
$error = '';
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        if (isset($vehicles[$id])) {
            unset($vehicles[$id]);
        }
        header('Location: vehicles.php');
        exit;
    }

    if ($action === 'add' || $action === 'edit') {
        $model = trim($_POST['model'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $price = filter_var(
            $_POST['price'] ?? '',
            FILTER_VALIDATE_FLOAT
        );

        if (
            $model === '' ||
            $type === '' ||
            $location === '' ||
            $price === false ||
            $price < 0
        ) {
            $error = 'Please enter valid vehicle information.';
        } elseif ($action === 'edit' && !isset($vehicles[$id])) {
            $error = 'Vehicle not found.';
        } else {
            if ($action === 'add') {
                $id = empty($vehicles)
                    ? 1
                    : max(array_keys($vehicles)) + 1;
            }

            $vehicles[$id] = [
                'model' => $model,
                'type' => $type,
                'location' => $location,
                'price' => $price
            ];

            header('Location: vehicles.php');
            exit;
        }
    }
}

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editing = $vehicles[$editId] ?? null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoadMate | Vehicle Management</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="header">
    <h1>RoadMate</h1>
    <span class="admin-label">Vehicle Management</span>
</header>

<main class="container">
    <a href="dashboard.php">← Back to Dashboard</a>

    <h2>Vehicle Management</h2>
    <p class="subtitle">Add, edit, and remove rental vehicles.</p>

    <?php if ($error): ?>
        <p style="color: red;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <div class="stat-card">
        <h3>
            <?= $editing ? 'Edit Vehicle' : 'Add Vehicle' ?>
        </h3>

        <form method="POST">
            <input type="hidden" name="action"
                value="<?= $editing ? 'edit' : 'add' ?>">

            <?php if ($editing): ?>
                <input type="hidden" name="id"
                    value="<?= $editId ?>">
            <?php endif; ?>

            <p>
                <label>Vehicle Model</label><br>
                <input type="text" name="model" required
                    value="<?= htmlspecialchars($editing['model'] ?? '') ?>">
            </p>

            <p>
                <label>Vehicle Type</label><br>
                <select name="type" required>
                    <?php foreach (['Sedan', 'SUV', 'Electric', 'Truck'] as $type): ?>
                        <option value="<?= $type ?>"
                            <?= ($editing['type'] ?? '') === $type ? 'selected' : '' ?>>
                            <?= $type ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label>Rental Location</label><br>
                <input type="text" name="location" required
                    value="<?= htmlspecialchars($editing['location'] ?? '') ?>">
            </p>

            <p>
                <label>Price Per Day ($)</label><br>
                <input type="number" name="price" min="0" step="0.01"
                    required value="<?= htmlspecialchars((string)($editing['price'] ?? '')) ?>">
            </p>

            <button type="submit">
                <?= $editing ? 'Update Vehicle' : 'Add Vehicle' ?>
            </button>

            <?php if ($editing): ?>
                <a href="vehicles.php">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <h2>Vehicle List</h2>

    <div class="stat-card">
        <table width="100%" cellpadding="12">
            <thead>
                <tr>
                    <th>Model</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Price/Day</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $id => $vehicle): ?>
                    <tr>
                        <td><?= htmlspecialchars($vehicle['model']) ?></td>
                        <td><?= htmlspecialchars($vehicle['type']) ?></td>
                        <td><?= htmlspecialchars($vehicle['location']) ?></td>
                        <td>$<?= number_format($vehicle['price'], 2) ?></td>
                        <td>
                            <a href="vehicles.php?edit=<?= $id ?>">Edit</a>

                            <form method="POST" style="display:inline"
                                  onsubmit="return confirm('Delete this vehicle?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($vehicles)): ?>
                    <tr>
                        <td colspan="5">No vehicles found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
