<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'supply chain employee') {
  header("Location: ZusSupplyManagement.html");
  exit();
}

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "
  SELECT 
    o.orderID,
    o.orderDate,
    o.invoice,
    o.admin_acknowledged,
    o.employee_acknowledged,
    o.status,
    o.date_prepared,
    o.date_delivered,
    o.userID,
    s.userFullName AS employeeName,
    su. item_name,
    GROUP_CONCAT(CONCAT(su. item_name, ' (', oi.quantity, ')') SEPARATOR ', ') AS items_summary
  FROM orders o
  JOIN supply_mgmt s ON o.userID = s.userID
  JOIN orders_items oi ON o.orderID = oi.orderID
  JOIN supplies su ON oi.supply_ID = su.supply_ID
  GROUP BY o.orderID
  ORDER BY o.orderDate DESC
";


$result = $conn->query($sql);
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
  <title>Admin - Review Orders</title>

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
            primaryHover: '#1e40af',
          }
        }
      }
    }
  </script>
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

  <!-- Sidebar -->
  <?php include 'admin_sidebar.php'; ?>

  <!-- Main Content -->
  <div class="flex-1 p-10">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Orders Needing Acknowledgment</h1>

    <div class="bg-white rounded-xl shadow-md p-6">
      <table id="ordersTable" class="display w-full text-sm">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="px-4 py-2">Invoice No.</th>
            <th class="px-4 py-2">Employee</th>
            <th class="px-4 py-2">Order Date</th>
            <th class="px-4 py-2">Items</th>
            <th class="px-4 py-2">Delivery Status</th>
            <th class="px-4 py-2">Acknowledge</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody class="text-gray-600">
          <?php foreach ($orders as $order): ?>
          <tr class="border-t border-gray-200 hover:bg-gray-50">
            <td class="px-4 py-2"><?= $order['invoice'] ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($order['employeeName']) ?></td>
            <td class="px-4 py-2"><?= date('Y-m-d H:i', strtotime($order['orderDate'])) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($order['items_summary']) ?></td>
            <td class="px-4 py-2">
              <?php
                if ($order['status'] === 'prepared') {
                  echo '<span class="text-yellow-600 font-medium">Prepared</span>';
                } elseif ($order['status'] === 'delivered') {
                  echo '<span class="text-green-700 font-medium">Delivered</span>';
                } elseif (!$order['admin_acknowledged'] && !$order['employee_acknowledged']) {
                  echo '<span class="text-blue-600 font-medium">Order Placed</span>';
                } elseif ($order['admin_acknowledged'] && !$order['employee_acknowledged']) {
                  echo '<span class="text-yellow-600 font-medium">Pending</span>';
                } elseif ($order['admin_acknowledged'] && $order['employee_acknowledged']) {
                  echo '<span class="text-green-600 font-medium">Order Received</span>';
                } else {
                  echo '<span class="text-gray-500 font-medium">—</span>';
                }
              ?>
            </td>
            <td class="px-4 py-2">
              <?php if (!$order['admin_acknowledged']): ?>
              <form action="admin_acknowledge.php" method="POST">
                <input type="hidden" name="orderID" value="<?= $order['orderID'] ?>">
                <button type="submit" class="bg-primary text-white px-3 py-1 rounded hover:bg-primaryHover text-sm">
                  Acknowledge
                </button>
              </form>
              <?php else: ?>
              <span class="text-green-600 font-semibold text-sm">Acknowledged</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-2">
              <?php if ($order['status'] === 'pending'): ?>
                <form action="update_order_status.php" method="POST" class="inline">
                  <input type="hidden" name="orderID" value="<?= $order['orderID'] ?>">
                  <input type="hidden" name="status" value="prepared">
                  <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                    Mark Prepared
                  </button>
                </form>
              <?php elseif ($order['status'] === 'prepared'): ?>
                <form action="update_order_status.php" method="POST" class="inline deliver-form">
                  <input type="hidden" name="orderID" value="<?= $order['orderID'] ?>">
                  <input type="hidden" name="status" value="delivered">
                  <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs confirm-deliver">
                    Mark Delivered
                  </button>
                </form>
              <?php elseif ($order['status'] === 'delivered'): ?>
                <span class="text-green-700 font-semibold text-sm">Delivered</span>
              <?php else: ?>
                <span class="text-gray-400 text-sm">—</span>
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
      order: [[2, 'desc']]
    });

    // SweetAlert confirmation for "Mark Delivered"
    $('.confirm-deliver').on('click', function () {
      const form = $(this).closest('form');

      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the order as delivered.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1d4ed8',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, mark as delivered',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
</script>


  <!-- <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
  <script>
    Swal.fire({
      title: 'Acknowledged!',
      text: 'You have successfully acknowledged the order.',
      icon: 'success',
      confirmButtonColor: '#1d4ed8'
    });
  </script>
  <?php endif; ?> -->

</body>
</html>
