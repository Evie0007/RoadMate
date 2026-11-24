
<?php
session_start();

// Temporary sample data until MySQL is ready.
if (!isset($_SESSION['demo_reservations'])) {
    $_SESSION['demo_reservations'] = [
        101 => [
            'customer' => 'Alice Johnson',
            'vehicle' => 'Toyota Corolla',
            'pickup' => '2026-12-01',
            'return' => '2026-12-04',
            'status' => 'Pending'
        ],
        102 => [
            'customer' => 'Bob Smith',
            'vehicle' => 'Honda CR-V',
            'pickup' => '2026-12-05',
            'return' => '2026-12-09',
            'status' => 'Confirmed'
        ]
    ];
}

if (!isset($_SESSION['demo_payments'])) {
    $_SESSION['demo_payments'] = [
        201 => [
            'reservation_id' => 101,
            'amount' => 165,
            'method' => 'Cash',
            'status' => 'Pending'
        ],
        202 => [
            'reservation_id' => 102,
            'amount' => 300,
            'method' => 'Credit Card (Simulated)',
            'status' => 'Paid'
        ]
    ];
}

$reservations = &$_SESSION['demo_reservations'];
$payments = &$_SESSION['demo_payments'];

$reservationStatuses = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];
$paymentStatuses = ['Pending', 'Paid', 'Refunded'];
$error = '';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $newStatus = $_POST['status'] ?? '';

    if ($section === 'reservation') {
        if (!$id || !isset($reservations[$id])) {
            $error = 'Reservation not found.';
        } elseif (!in_array($newStatus, $reservationStatuses, true)) {
            $error = 'Invalid reservation status.';
        } else {
            $current = $reservations[$id]['status'];

            // Example policy: a completed reservation cannot be reopened.
            if ($current === 'Completed' && $newStatus !== 'Completed') {
                $error = 'Completed reservations cannot be reopened.';
            } elseif ($current === 'Cancelled' && $newStatus !== 'Cancelled') {
                $error = 'Cancelled reservations cannot be reopened.';
            } else {
                $reservations[$id]['status'] = $newStatus;
            }
        }
    } elseif ($section === 'payment') {
        if (!$id || !isset($payments[$id])) {
            $error = 'Payment not found.';
        } elseif (!in_array($newStatus, $paymentStatuses, true)) {
            $error = 'Invalid payment status.';
        } else {
            $current = $payments[$id]['status'];

            if ($current === 'Refunded' && $newStatus !== 'Refunded') {
                $error = 'Refunded payments cannot be changed.';
            } elseif ($newStatus === 'Refunded' && $current !== 'Paid') {
                $error = 'Only paid payments can be refunded.';
            } else {
                $payments[$id]['status'] = $newStatus;
            }
        }
    } else {
        $error = 'Invalid request.';
    }

    if ($error === '') {
        header('Location: reservations_payments.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoadMate | Reservations & Payments</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<header class="header">
    <h1>RoadMate</h1>
    <span class="admin-label">Reservations & Payments</span>
</header>

<main class="container">
    <a href="dashboard.php">← Back to Dashboard</a>
    <h2>Reservations & Payments</h2>
    <p class="subtitle">Manage customer reservations and payment records.</p>

    <?php if ($error): ?>
        <p style="color:red;"><?= e($error) ?></p>
    <?php endif; ?>

    <h2>Reservations</h2>
    <div class="stat-card" style="overflow-x:auto;">
        <table width="100%" cellpadding="12">
            <tr>
                <th>ID</th><th>Customer</th><th>Vehicle</th>
                <th>Pickup</th><th>Return</th><th>Status</th><th>Action</th>
            </tr>
            <?php foreach ($reservations as $id => $reservation): ?>
                <tr>
                    <td>#<?= $id ?></td>
                    <td><?= e($reservation['customer']) ?></td>
                    <td><?= e($reservation['vehicle']) ?></td>
                    <td><?= e($reservation['pickup']) ?></td>
                    <td><?= e($reservation['return']) ?></td>
                    <td><?= e($reservation['status']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="section" value="reservation">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <select name="status">
                                <?php foreach ($reservationStatuses as $status): ?>
                                    <option value="<?= e($status) ?>"
                                        <?= $reservation['status'] === $status ? 'selected' : '' ?>>
                                        <?= e($status) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <h2>Payments</h2>
    <div class="stat-card" style="overflow-x:auto;">
        <table width="100%" cellpadding="12">
            <tr>
                <th>ID</th><th>Reservation</th><th>Amount</th>
                <th>Method</th><th>Status</th><th>Action</th>
            </tr>
            <?php foreach ($payments as $id => $payment): ?>
                <tr>
                    <td>#<?= $id ?></td>
                    <td>#<?= e($payment['reservation_id']) ?></td>
                    <td>$<?= number_format($payment['amount'], 2) ?></td>
                    <td><?= e($payment['method']) ?></td>
                    <td><?= e($payment['status']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="section" value="payment">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <select name="status">
                                <?php foreach ($paymentStatuses as $status): ?>
                                    <option value="<?= e($status) ?>"
                                        <?= $payment['status'] === $status ? 'selected' : '' ?>>
                                        <?= e($status) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
