<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'employee') {
    header("Location: ZusSupplyManagement.html");
    exit();
}

$userID = $_SESSION['userID'];

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT 
        o.orderID,
        o.invoice,
        o.orderDate,
        o.admin_acknowledged,
        o.employee_acknowledged,
        o. status,
        s. item_name,
        GROUP_CONCAT(CONCAT(s. item_name, ' (', oi.quantity, ')') SEPARATOR ', ') AS items_summary
    FROM orders o
    JOIN orders_items oi ON o.orderID = oi.orderID
    JOIN supplies s ON oi.supply_ID = s.supply_ID
    WHERE o.userID = ?
    GROUP BY o.orderID
    ORDER BY o.orderDate DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Status</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1d4ed8',
            primaryHover: '#1e40af'
          }
        }
      }
    }
  </script>
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

  <!-- Sidebar -->
  <?php include 'employee_sidebar.php'; ?>

  <!-- Main Content -->
  <div class="flex-1 p-10">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Order Status</h1>

    <div class="bg-white rounded-xl shadow-md p-6">
      <table id="ordersTable" class="display w-full text-sm">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="px-4 py-2">Invoice No.</th>
            <th class="px-4 py-2">Order Date</th>
            <th class="px-4 py-2">Items</th>
            <th class="px-4 py-2">Admin Status</th>
            <!-- <th class="px-4 py-2">Employee Status</th> -->
            <th class="px-4 py-2">Order Status</th>
            <th class="px-4 py-2">Acknowledge</th>
          </tr>
        </thead>
        <tbody class="text-gray-600">
          <?php foreach ($orders as $order): ?>
          <tr class="border-t border-gray-200 hover:bg-gray-50">
            <td class="px-4 py-2"><?= $order['invoice'] ?></td>
            <td class="px-4 py-2"><?= date('Y-m-d H:i', strtotime($order['orderDate'])) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($order['items_summary']) ?></td>
            <td class="px-4 py-2">
              <?= $order['admin_acknowledged']
                  ? '<span class="text-green-600 font-semibold">Acknowledged</span>'
                  : '<span class="text-red-600 font-medium">Pending</span>' ?>
            </td>
            <!-- <td class="px-4 py-2">
              <?= $order['employee_acknowledged']
                  ? '<span class="text-green-600 font-semibold">Order Received</span>'
                  : '<span class="text-yellow-600 font-medium">Pending</span>' ?>
            </td> -->
            <td class="px-4 py-2">
              <?php
                if ($order['status'] === 'prepared') {
                  echo '<span class="text-blue-600 font-medium">Prepared</span>';
                } elseif ($order['status'] === 'delivered') {
                  echo '<span class="text-green-700 font-medium">Delivered</span>';
                } elseif (!$order['admin_acknowledged'] && !$order['employee_acknowledged']) {
                  echo '<span class="text-yellow-600 font-medium">Pending</span>';
                } elseif ($order['admin_acknowledged'] && $order['employee_acknowledged']) {
                  echo '<span class="text-indigo-600 font-medium">Order Received</span>';
                } else {
                  echo '<span class="text-gray-500 font-medium">—</span>';
                }
              ?>
            </td>

            <td class="px-4 py-2">
              <?php if ($order['status'] === 'delivered' && !$order['employee_acknowledged']): ?>
                <form action="employee_acknowledge.php" method="POST">
                  <input type="hidden" name="orderID" value="<?= $order['orderID'] ?>">
                  <button type="submit" class="bg-primary text-white px-3 py-1 rounded hover:bg-primaryHover text-sm">
                    Order Received
                  </button>
                </form>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>

          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $('#ordersTable').DataTable({
        order: [[1, 'desc']] 
      });
    });
  </script>

  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <script>
      Swal.fire({
        title: 'Order Received!',
        icon: 'success',
        confirmButtonColor: '#1d4ed8'
      });
    </script>
  <?php endif; ?>
</body>
</html>
