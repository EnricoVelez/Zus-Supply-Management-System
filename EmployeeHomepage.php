<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'employee') {
    header("Location: ZusSupplyManagement.html");
    exit();
}

$userID = $_SESSION['userID'];
$userFullName = $_SESSION['userFullName'];

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$branch = "";
$branchQuery = $conn->prepare("SELECT branch FROM supply_mgmt WHERE userID = ?");
$branchQuery->bind_param("i", $userID);
$branchQuery->execute();
$branchQuery->bind_result($branch);
$branchQuery->fetch();
$branchQuery->close();

$totalOrders = 0;
$conn->query("SET SESSION sql_mode = ''");
$orderQuery = $conn->prepare("SELECT COUNT(*) FROM orders WHERE userID = ?");
$orderQuery->bind_param("i", $userID);
$orderQuery->execute();
$orderQuery->bind_result($totalOrders);
$orderQuery->fetch();
$orderQuery->close();

$pendingAck = 0;
$ackQuery = $conn->prepare("SELECT COUNT(*) FROM orders WHERE userID = ? AND admin_acknowledged = 1 AND employee_acknowledged = 0");
$ackQuery->bind_param("i", $userID);
$ackQuery->execute();
$ackQuery->bind_result($pendingAck);
$ackQuery->fetch();
$ackQuery->close();

$recentOrders = [];
$recentQuery = $conn->prepare("
    SELECT s.item_name, oi.quantity 
    FROM orders o
    JOIN orders_items oi ON o.orderID = oi.orderID
    JOIN supplies s ON oi.supply_ID = s.supply_ID
    WHERE o.userID = ?
    ORDER BY o.orderDate DESC
    LIMIT 3
");
$recentQuery->bind_param("i", $userID);
$recentQuery->execute();
$recentResult = $recentQuery->get_result();
while ($row = $recentResult->fetch_assoc()) {
    $recentOrders[] = $row;
}
$recentQuery->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Employee Dashboard - Coffee Shop Supply</title>
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
<body class="bg-gray-100 font-sans flex min-h-screen">

  <!-- Sidebar -->
  <?php include 'employee_sidebar.php'; ?>

  <!-- Main Dashboard Content -->
  <div class="flex-1 p-10">
      <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-lg text-gray-600 mt-2">
          Welcome back, <span class="font-semibold"><?= htmlspecialchars($userFullName); ?></span>
        </p>
      </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Orders Placed -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
        <h2 class="text-xl font-semibold mb-2 text-gray-700">Orders Placed</h2>
        <p class="text-3xl font-bold text-primary"><?= $totalOrders ?></p>
      </div>

      <!-- Pending Acknowledgments -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
        <h2 class="text-xl font-semibold mb-2 text-gray-700">Pending Acknowledgments</h2>
        <p class="text-3xl font-bold text-primary"><?= $pendingAck ?></p>
      </div>

      <!-- Branch Info -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
        <h2 class="text-xl font-semibold mb-2 text-gray-700">Branch</h2>
        <p class="text-3xl font-bold text-primary"><?= htmlspecialchars($branch) ?></p>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="mt-10">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Orders</h2>
      <div class="bg-white rounded-xl shadow-md p-6">
        <ul class="space-y-2 text-gray-600">
          <?php if (!empty($recentOrders)): ?>
            <?php foreach ($recentOrders as $item): ?>
              <li><?= htmlspecialchars($item['item_name']) ?> - <?= htmlspecialchars($item['quantity']) ?></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>No recent orders found.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    

  </div>
</body>
</html>
