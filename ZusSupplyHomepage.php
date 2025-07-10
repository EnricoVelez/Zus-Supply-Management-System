<!-- ADMIN -->
<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'supply chain employee') {
    header("Location: ZusSupplyManagement.html");
    exit();
}
$userFullName = $_SESSION['userFullName'];

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pendingCount = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE admin_acknowledged = 0");
if ($result && $row = $result->fetch_assoc()) {
    $pendingCount = $row['count'];
}

$recent = [];
$sql = "
    SELECT 
        o.orderID,
        o.orderDate,
        s.userFullName,
        o. invoice,
        o.admin_acknowledged,
        o.employee_acknowledged
    FROM orders o
    JOIN supply_mgmt s ON o.userID = s.userID
    ORDER BY o.orderDate DESC
    LIMIT 5
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent[] = $row;
    }
}

// Get number of distinct warehouses
$sqlWarehouses = "
    SELECT COUNT(DISTINCT sup.warehouse) as warehouse_count
    FROM supplies sup
";
$resultWarehouses = $conn->query($sqlWarehouses);
$warehouseCount = 0;
if ($resultWarehouses && $row = $resultWarehouses->fetch_assoc()) {
    $warehouseCount = $row['warehouse_count'];
}

// Count total distinct inventory items by item_name
$sqlInventoryCount = "SELECT COUNT(DISTINCT item_name) as total_items FROM supplies";
$resultInventory = $conn->query($sqlInventoryCount);
$totalInventoryItems = 0;
if ($resultInventory && $row = $resultInventory->fetch_assoc()) {
    $totalInventoryItems = $row['total_items'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Coffee Shop Supply Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1d4ed8',
            primaryHover: '#1e40af',
          }
        }
      }
    }
  </script>
</head>
<body class="bg-gray-100 font-sans">

  <div class="flex min-h-screen">

    <!-- Sidebar -->
    <?php include 'admin_sidebar.php'; ?>

    <!-- Main Dashboard Content -->
    <div class="flex-1 p-10">
      <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-lg text-gray-600 mt-2">
          Welcome back, <span class="font-semibold"><?= htmlspecialchars($userFullName); ?></span>
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Inventory Items -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
          <h2 class="text-xl font-semibold text-gray-700 mb-2">Total Inventory Items</h2>
          <p class="text-3xl font-bold text-primary"><?= $totalInventoryItems ?></p>
        </div>

        <!-- Pending Acknowledgments -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
          <h2 class="text-xl font-semibold text-gray-700 mb-2">Pending Acknowledgments</h2>
          <p class="text-3xl font-bold text-primary"><?= $pendingCount ?></p>
        </div>

        <!-- Suppliers -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
          <h2 class="text-xl font-semibold text-gray-700 mb-2">Warehouses</h2>
          <p class="text-3xl font-bold text-primary"><?= $warehouseCount ?></p>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Activity</h2>
        <div class="bg-white rounded-xl shadow-md p-6">
          <ul class="space-y-3 text-gray-600 text-sm leading-relaxed">
            <?php if (count($recent) > 0): ?>
              <?php foreach ($recent as $r): ?>
                <li>
                  <span class="font-semibold"><?= htmlspecialchars($r['userFullName']) ?></span>
                  <?= $r['admin_acknowledged'] ? 'acknowledged' : 'placed' ?> 
                  an order (Invoice #<?= $r['invoice'] ?>) on 
                  <?= date("M d, Y H:i", strtotime($r['orderDate'])) ?>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li>No recent activity found.</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
