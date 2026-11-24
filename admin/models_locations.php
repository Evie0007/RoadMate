
<?php
session_start();

// Demo only: replace session data with MySQL later.
if (!isset($_SESSION['demo_models'])) {
    $_SESSION['demo_models'] = [
        1 => ['brand' => 'Toyota', 'model' => 'Corolla', 'type' => 'Sedan'],
        2 => ['brand' => 'Honda', 'model' => 'CR-V', 'type' => 'SUV'],
        3 => ['brand' => 'Tesla', 'model' => 'Model 3', 'type' => 'Electric']
    ];
}
if (!isset($_SESSION['demo_locations'])) {
    $_SESSION['demo_locations'] = [
        1 => ['name' => 'SJC Airport', 'city' => 'San Jose'],
        2 => ['name' => 'SFO Airport', 'city' => 'San Francisco']
    ];
}

$models = &$_SESSION['demo_models'];
$locations = &$_SESSION['demo_locations'];
$error = '';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function nextId($items) {
    return empty($items) ? 1 : max(array_keys($items)) + 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $action = $_POST['action'] ?? '';
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if (!in_array($section, ['model', 'location'], true) ||
        !in_array($action, ['add', 'edit', 'delete'], true)) {
        $error = 'Invalid request.';
    } else {
        $items = &$models;
        if ($section === 'location') {
            $items = &$locations;
        }

        if ($action === 'delete') {
            if (!$id || !isset($items[$id])) {
                $error = 'Record not found.';
            } else {
                unset($items[$id]);
            }
        } else {
            $record = [];
            if ($section === 'model') {
                $record = [
                    'brand' => trim($_POST['brand'] ?? ''),
                    'model' => trim($_POST['model'] ?? ''),
                    'type' => trim($_POST['type'] ?? '')
                ];
            } else {
                $record = [
                    'name' => trim($_POST['name'] ?? ''),
                    'city' => trim($_POST['city'] ?? '')
                ];
            }

            foreach ($record as $value) {
                if ($value === '') {
                    $error = 'All fields are required.';
                    break;
                }
            }

            if ($action === 'edit' && (!$id || !isset($items[$id]))) {
                $error = 'Record not found.';
            }

            if ($error === '') {
                if ($action === 'add') {
                    $id = nextId($items);
                }
                $items[$id] = $record;
            }
        }

        unset($items);
    }

    if ($error === '') {
        header('Location: models_locations.php');
        exit;
    }
}

$editSection = $_GET['section'] ?? '';
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
$editing = null;

if ($editId && $editSection === 'model') {
    $editing = $models[$editId] ?? null;
} elseif ($editId && $editSection === 'location') {
    $editing = $locations[$editId] ?? null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoadMate | Models & Locations</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<header class="header">
    <h1>RoadMate</h1>
    <span class="admin-label">Models & Locations</span>
</header>

<main class="container">
    <a href="dashboard.php">← Back to Dashboard</a>
    <h2>Models & Locations</h2>
    <p class="subtitle">Manage vehicle models and rental locations.</p>

    <?php if ($error): ?>
        <p style="color:red;"><?= e($error) ?></p>
    <?php endif; ?>

    <div class="stat-card">
        <h3><?= $editSection === 'model' && $editing ? 'Edit Model' : 'Add Vehicle Model' ?></h3>
        <form method="POST">
            <input type="hidden" name="section" value="model">
            <input type="hidden" name="action"
                   value="<?= $editSection === 'model' && $editing ? 'edit' : 'add' ?>">
            <?php if ($editSection === 'model' && $editing): ?>
                <input type="hidden" name="id" value="<?= $editId ?>">
            <?php endif; ?>

            <p><label>Brand</label><br>
                <input name="brand" required value="<?= e($editSection === 'model' ? ($editing['brand'] ?? '') : '') ?>">
            </p>
            <p><label>Model</label><br>
                <input name="model" required value="<?= e($editSection === 'model' ? ($editing['model'] ?? '') : '') ?>">
            </p>
            <p><label>Vehicle Type</label><br>
                <select name="type" required>
                    <?php foreach (['Sedan','SUV','Electric','Truck'] as $type): ?>
                        <option value="<?= e($type) ?>"
                            <?= ($editSection === 'model' && ($editing['type'] ?? '') === $type) ? 'selected' : '' ?>>
                            <?= e($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <button type="submit"><?= $editSection === 'model' && $editing ? 'Update Model' : 'Add Model' ?></button>
            <?php if ($editSection === 'model' && $editing): ?>
                <a href="models_locations.php">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <h2>Vehicle Models</h2>
    <div class="stat-card" style="overflow-x:auto;">
        <table width="100%" cellpadding="12">
            <tr><th>Brand</th><th>Model</th><th>Type</th><th>Actions</th></tr>
            <?php foreach ($models as $id => $model): ?>
                <tr>
                    <td><?= e($model['brand']) ?></td>
                    <td><?= e($model['model']) ?></td>
                    <td><?= e($model['type']) ?></td>
                    <td>
                        <a href="?section=model&amp;edit=<?= $id ?>">Edit</a>
                        <form method="POST" style="display:inline"
                              onsubmit="return confirm('Delete this model?');">
                            <input type="hidden" name="section" value="model">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="stat-card">
        <h3><?= $editSection === 'location' && $editing ? 'Edit Location' : 'Add Rental Location' ?></h3>
        <form method="POST">
            <input type="hidden" name="section" value="location">
            <input type="hidden" name="action"
                   value="<?= $editSection === 'location' && $editing ? 'edit' : 'add' ?>">
            <?php if ($editSection === 'location' && $editing): ?>
                <input type="hidden" name="id" value="<?= $editId ?>">
            <?php endif; ?>

            <p><label>Location Name</label><br>
                <input name="name" required value="<?= e($editSection === 'location' ? ($editing['name'] ?? '') : '') ?>">
            </p>
            <p><label>City</label><br>
                <input name="city" required value="<?= e($editSection === 'location' ? ($editing['city'] ?? '') : '') ?>">
            </p>
            <button type="submit"><?= $editSection === 'location' && $editing ? 'Update Location' : 'Add Location' ?></button>
            <?php if ($editSection === 'location' && $editing): ?>
                <a href="models_locations.php">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <h2>Rental Locations</h2>
    <div class="stat-card" style="overflow-x:auto;">
        <table width="100%" cellpadding="12">
            <tr><th>Location</th><th>City</th><th>Actions</th></tr>
            <?php foreach ($locations as $id => $location): ?>
                <tr>
                    <td><?= e($location['name']) ?></td>
                    <td><?= e($location['city']) ?></td>
                    <td>
                        <a href="?section=location&amp;edit=<?= $id ?>">Edit</a>
                        <form method="POST" style="display:inline"
                              onsubmit="return confirm('Delete this location?');">
                            <input type="hidden" name="section" value="location">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
