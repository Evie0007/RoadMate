
<?php
session_start();

// Temporary sample data until MySQL is ready.
if (!isset($_SESSION['demo_maintenance'])) {
    $_SESSION['demo_maintenance'] = [
        1 => [
            'vehicle' => 'Toyota Corolla',
            'service' => 'Oil Change',
            'date' => '2026-11-10',
            'cost' => 80
        ],
        2 => [
            'vehicle' => 'Honda CR-V',
            'service' => 'Tire Replacement',
            'date' => '2026-11-15',
            'cost' => 320
        ]
    ];
}

$records = &$_SESSION['demo_maintenance'];
$error = '';
$editing = null;

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function validDate($date) {
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        if (isset($records[$id])) {
            unset($records[$id]);
        }
        header('Location: maintenance.php');
        exit;
    }

    if ($action === 'add' || $action === 'edit') {
        $vehicle = trim($_POST['vehicle'] ?? '');
        $service = trim($_POST['service'] ?? '');
        $date = $_POST['date'] ?? '';
        $cost = filter_var(
            $_POST['cost'] ?? '',
            FILTER_VALIDATE_FLOAT
        );

        if ($vehicle === '' || $service === '') {
            $error = 'Vehicle and service type are required.';
        } elseif (!validDate($date)) {
            $error = 'Please enter a valid service date.';
        } elseif ($date > date('Y-m-d')) {
            $error = 'Service date cannot be in the future.';
        } elseif ($cost === false || $cost < 0) {
            $error = 'Cost must be zero or greater.';
        } elseif ($action === 'edit' && !isset($records[$id])) {
            $error = 'Maintenance record not found.';
        }

        if ($error === '') {
            if ($action === 'add') {
                $id = empty($records)
                    ? 1
                    : max(array_keys($records)) + 1;
            }

            $records[$id] = [
                'vehicle' => $vehicle,
                'service' => $service,
                'date' => $date,
                'cost' => $cost
            ];

            header('Location: maintenance.php');
            exit;
        }
    }
}

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editing = $records[$editId] ?? null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoadMate | Maintenance Management</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="header">
    <h1>RoadMate</h1>
    <span class="admin-label">Maintenance Management</span>
</header>

<main class="container">
    <a href="dashboard.php">← Back to Dashboard</a>

    <h2>Maintenance Management</h2>
    <p class="subtitle">Manage vehicle maintenance history.</p>

    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <div class="stat-card">
        <h3>
            <?= $editing ? 'Edit Maintenance Record' : 'Add Maintenance Record' ?>
        </h3>

        <form method="POST">
            <input type="hidden" name="action"
                   value="<?= $editing ? 'edit' : 'add' ?>">

            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= $editId ?>">
            <?php endif; ?>

            <p>
                <label for="vehicle">Vehicle</label><br>
                <select id="vehicle" name="vehicle" required>
                    <?php foreach (['Toyota Corolla', 'Honda CR-V', 'Tesla Model 3'] as $vehicle): ?>
                        <option value="<?= e($vehicle) ?>"
                            <?= ($editing['vehicle'] ?? '') === $vehicle ? 'selected' : '' ?>>
                            <?= e($vehicle) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="service">Service Type</label><br>
                <input id="service" type="text" name="service" required
                       value="<?= e($editing['service'] ?? '') ?>">
            </p>

            <p>
                <label for="date">Service Date</label><br>
                <input id="date" type="date" name="date" required
                       value="<?= e($editing['date'] ?? '') ?>">
            </p>

            <p>
                <label for="cost">Cost ($)</label><br>
                <input id="cost" type="number" name="cost"
                       min="0" step="0.01" required
                       value="<?= e($editing['cost'] ?? '') ?>">
            </p>

            <button type="submit">
                <?= $editing ? 'Update Record' : 'Add Record' ?>
            </button>

            <?php if ($editing): ?>
                <a href="maintenance.php">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <h2>Maintenance History</h2>

    <div class="stat-card">
        <div style="overflow-x: auto;">
            <table width="100%" cellpadding="12">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $id => $record): ?>
                        <tr>
                            <td><?= e($record['vehicle']) ?></td>
                            <td><?= e($record['service']) ?></td>
                            <td><?= e($record['date']) ?></td>
                            <td>$<?= number_format($record['cost'], 2) ?></td>
                            <td>
                                <a href="maintenance.php?edit=<?= $id ?>">Edit</a>

                                <form method="POST" style="display:inline"
                                      onsubmit="return confirm('Delete this record?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="5">No maintenance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
