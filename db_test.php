<?php
// Connection test page. If this shows the table counts, your setup works.
require_once __DIR__ . '/includes/db.php';

$tables = ['UserAccount', 'Customer', 'Admin', 'Vehicle', 'RentalLocation', 'Reservation', 'Payment'];
$counts = [];
foreach ($tables as $t) {
    $counts[$t] = $conn->query("SELECT COUNT(*) FROM `$t`")->fetch_row()[0];
}

// Example of a prepared statement (always use these when a value comes from the user).
$type = 'SUV';
$stmt = $conn->prepare(
    'SELECT v.VehicleID, m.Make, m.ModelName, v.DailyRate, l.Name AS Location
       FROM Vehicle v
       JOIN VehicleModel m  ON m.ModelID = v.ModelID
       JOIN VehicleType t   ON t.TypeID = m.TypeID
       JOIN RentalLocation l ON l.LocationID = v.LocationID
      WHERE t.TypeName = ? AND v.Status = ?
      ORDER BY v.DailyRate'
);
$status = 'Available';
$stmt->bind_param('ss', $type, $status);
$stmt->execute();
$suvs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RoadMate: connection test</title>
<style>
  body { font-family: system-ui, sans-serif; max-width: 720px; margin: 40px auto; padding: 0 16px; color: #111; }
  .ok { background: #e7f6ec; color: #135c2b; padding: 12px 16px; border-radius: 6px; font-weight: 600; }
  table { border-collapse: collapse; width: 100%; margin: 12px 0 28px; }
  th, td { text-align: left; padding: 6px 10px; border-bottom: 1px solid #ddd; }
  td.num { text-align: right; }
</style>
</head>
<body>
  <h1>RoadMate</h1>
  <p class="ok">Connected to the <?= htmlspecialchars($config['database']) ?> database.</p>

  <h2>Rows per table</h2>
  <table>
    <tr><th>Table</th><th>Rows</th></tr>
    <?php foreach ($counts as $t => $n): ?>
      <tr><td><?= htmlspecialchars($t) ?></td><td class="num"><?= (int) $n ?></td></tr>
    <?php endforeach; ?>
  </table>

  <h2>Available SUVs (prepared statement example)</h2>
  <table>
    <tr><th>ID</th><th>Vehicle</th><th>Location</th><th>Daily rate</th></tr>
    <?php foreach ($suvs as $v): ?>
      <tr>
        <td><?= (int) $v['VehicleID'] ?></td>
        <td><?= htmlspecialchars($v['Make'] . ' ' . $v['ModelName']) ?></td>
        <td><?= htmlspecialchars($v['Location']) ?></td>
        <td class="num">$<?= number_format((float) $v['DailyRate'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
