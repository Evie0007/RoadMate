
<?php
session_start();

// Temporary demo data until MySQL is ready.
if (!isset($_SESSION['demo_discounts'])) {
    $_SESSION['demo_discounts'] = [
        1 => [
            'code' => 'SAVE10',
            'percentage' => 10,
            'expiration' => '2026-12-31'
        ],
        2 => [
            'code' => 'WINTER20',
            'percentage' => 20,
            'expiration' => '2027-01-31'
        ]
    ];
}

$discounts = &$_SESSION['demo_discounts'];
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
        if (isset($discounts[$id])) {
            unset($discounts[$id]);
        }
        header('Location: discounts.php');
        exit;
    }

    if ($action === 'add' || $action === 'edit') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $percentage = filter_var(
            $_POST['percentage'] ?? '',
            FILTER_VALIDATE_FLOAT
        );
        $expiration = $_POST['expiration'] ?? '';

        if (!preg_match('/^[A-Z0-9_-]{3,30}$/', $code)) {
            $error = 'Code must contain 3-30 letters, numbers, _ or -.';
        } elseif (
            $percentage === false ||
            $percentage <= 0 ||
            $percentage > 100
        ) {
            $error = 'Discount must be greater than 0 and at most 100%.';
        } elseif (!validDate($expiration)) {
            $error = 'Please enter a valid expiration date.';
        } elseif ($expiration < date('Y-m-d')) {
            $error = 'Cannot save an expired discount.';
        } elseif ($action === 'edit' && !isset($discounts[$id])) {
            $error = 'Discount not found.';
        } else {
            foreach ($discounts as $existingId => $discount) {
                if ($existingId !== $id && $discount['code'] === $code) {
                    $error = 'This discount code already exists.';
                    break;
                }
            }
        }

        if ($error === '') {
            if ($action === 'add') {
                $id = empty($discounts)
                    ? 1
                    : max(array_keys($discounts)) + 1;
            }

            $discounts[$id] = [
                'code' => $code,
                'percentage' => $percentage,
                'expiration' => $expiration
            ];

            header('Location: discounts.php');
            exit;
        }
    }
}

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editing = $discounts[$editId] ?? null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoadMate | Discount Management</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="header">
    <h1>RoadMate</h1>
    <span class="admin-label">Discount Management</span>
</header>

<main class="container">
    <a href="dashboard.php">← Back to Dashboard</a>

    <h2>Discount Management</h2>
    <p class="subtitle">Manage promotional discount codes.</p>

    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <div class="stat-card">
        <h3><?= $editing ? 'Edit Discount' : 'Add Discount' ?></h3>

        <form method="POST">
            <input type="hidden" name="action"
                   value="<?= $editing ? 'edit' : 'add' ?>">

            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= $editId ?>">
            <?php endif; ?>

            <p>
                <label for="code">Discount Code</label><br>
                <input id="code" type="text" name="code"
                       maxlength="30" required
                       value="<?= e($editing['code'] ?? '') ?>">
            </p>

            <p>
                <label for="percentage">Discount Percentage (%)</label><br>
                <input id="percentage" type="number" name="percentage"
                       min="0.01" max="100" step="0.01" required
                       value="<?= e($editing['percentage'] ?? '') ?>">
            </p>

            <p>
                <label for="expiration">Expiration Date</label><br>
                <input id="expiration" type="date" name="expiration"
                       required
                       value="<?= e($editing['expiration'] ?? '') ?>">
            </p>

            <button type="submit">
                <?= $editing ? 'Update Discount' : 'Add Discount' ?>
            </button>

            <?php if ($editing): ?>
                <a href="discounts.php">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <h2>Discount List</h2>

    <div class="stat-card">
        <div style="overflow-x: auto;">
            <table width="100%" cellpadding="12">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Expiration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($discounts as $id => $discount): ?>
                        <?php
                        $expired = $discount['expiration'] < date('Y-m-d');
                        ?>
                        <tr>
                            <td><?= e($discount['code']) ?></td>
                            <td><?= e($discount['percentage']) ?>%</td>
                            <td><?= e($discount['expiration']) ?></td>
                            <td><?= $expired ? 'Expired' : 'Active' ?></td>
                            <td>
                                <a href="discounts.php?edit=<?= $id ?>">Edit</a>

                                <form method="POST" style="display:inline"
                                      onsubmit="return confirm('Delete this discount?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($discounts)): ?>
                        <tr>
                            <td colspan="5">No discounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
