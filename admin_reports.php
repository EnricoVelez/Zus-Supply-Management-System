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

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$employee = $_GET['employee'] ?? '';
$branch = $_GET['branch'] ?? '';

$sql = "
  SELECT 
    o.orderID,
    o.orderDate,
    o. invoice,
    s.userFullName AS employeeName,
    s.branch,
    o.admin_acknowledged,
    o.admin_acknowledged_at,
    o.employee_acknowledged,
    o.employee_acknowledged_at,
    GROUP_CONCAT(CONCAT(su.item_name, ' (', oi.quantity, ')') SEPARATOR ', ') AS items_summary
  FROM orders o
  JOIN supply_mgmt s ON o.userID = s.userID
  JOIN orders_items oi ON o.orderID = oi.orderID
  JOIN supplies su ON oi.supply_ID = su.supply_ID
  WHERE 1
";


$params = [];
$types = '';

if (!empty($start) && !empty($end)) {
    $sql .= " AND o.orderDate BETWEEN ? AND ?";
    $types .= 'ss';
    $params[] = $start;
    $params[] = $end;
}

if (!empty($employee)) {
    $sql .= " AND s.userFullName LIKE ?";
    $types .= 's';
    $params[] = "%$employee%";
}

if (!empty($branch)) {
    $sql .= " AND s.branch = ?";
    $types .= 's';
    $params[] = $branch;
}

$sql .= " GROUP BY o.orderID ORDER BY o.orderDate DESC";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// Group employees by branch
$groupedEmployees = [];
$empQuery = $conn->query("SELECT userFullName, branch FROM supply_mgmt WHERE role = 'employee' ORDER BY branch, userFullName");
while ($row = $empQuery->fetch_assoc()) {
    $groupedEmployees[$row['branch']][] = $row['userFullName'];
}

// Get distinct branches
$branches = [];
$branchQuery = $conn->query("SELECT DISTINCT branch FROM supply_mgmt ORDER BY branch ASC");
while ($row = $branchQuery->fetch_assoc()) {
    $branches[] = $row['branch'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin - Reports</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css"/>
  <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"/>

  <style>
    .select2-container .select2-selection--single {
      height: 2.5rem !important;
      padding: 0.5rem;
      border-color: #d1d5db;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 1.75rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
      top: 8px;
    }
  </style>
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

<!-- Sidebar -->
<?php include 'admin_sidebar.php'; ?>



<!-- Main Content -->
<div class="flex-1 p-8">
  <h1 class="text-3xl font-bold text-gray-800 mb-6">Order Reports</h1>

  <!-- Filter Form -->
  <form method="GET" class="bg-white p-6 rounded-xl shadow mb-6 max-w-6xl">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Filter the reports</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm text-gray-600 mb-1">Start Date</label>
        <input type="date" name="start" value="<?= htmlspecialchars($start) ?>" class="border border-gray-300 p-2 w-full rounded" />
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">End Date</label>
        <input type="date" name="end" value="<?= htmlspecialchars($end) ?>" class="border border-gray-300 p-2 w-full rounded" />
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Employee Name</label>
        <select name="employee" class="select-employee border border-gray-300 p-2 w-full rounded">
          <option></option>
          <?php foreach ($groupedEmployees as $branchName => $names): ?>
            <optgroup label="<?= htmlspecialchars($branchName) ?>">
              <?php foreach ($names as $name): ?>
                <option value="<?= htmlspecialchars($name) ?>" <?= ($employee === $name) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($name) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Branch</label>
        <select name="branch" class="select-branch border border-gray-300 p-2 w-full rounded">
          <option></option>
          <?php foreach ($branches as $branchOption): ?>
            <option value="<?= htmlspecialchars($branchOption) ?>" <?= ($branch === $branchOption) ? 'selected' : '' ?>>
              <?= htmlspecialchars($branchOption) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mt-6 flex flex-wrap gap-4">
      <button type="submit" class="bg-[#1D4ED8] text-white px-6 py-2 rounded hover:bg-[#162035]">Filter</button>
      <a href="admin_reports.php" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Clear</a>
    </div>
  </form>

  <!-- Table -->
  <div class="bg-white p-4 rounded-xl shadow">
    <table id="reportTable" class="display w-full text-sm">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th><input type="checkbox" id="selectAll"></th>
          <th>Invoice No.</th>
          <th>Employee</th>
          <th>Order Date</th>
          <th>Items</th>
          <th>Admin Acknowledged</th>
          <th>Order Received</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td></td>
            <td><?= $order['invoice'] ?></td>
            <td><?= htmlspecialchars($order['employeeName']) ?></td>
            <td><?= date('Y-m-d H:i', strtotime($order['orderDate'])) ?></td>
            <td><?= htmlspecialchars($order['items_summary']) ?></td>
            <td><?= $order['admin_acknowledged'] ? '✅ ' . date('Y-m-d H:i', strtotime($order['admin_acknowledged_at'])) : '❌' ?></td>
            <td><?= $order['employee_acknowledged'] ? '✅ ' . date('Y-m-d H:i', strtotime($order['employee_acknowledged_at'])) : '❌' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function () {
    // Initialize Select2
    $('.select-employee').select2({
      placeholder: "Select an employee",
      allowClear: true,
      width: '100%'
    });

    $('.select-branch').select2({
      placeholder: "Select a branch",
      allowClear: true,
      width: '100%'
    });

    const start = "<?= $start ?>";
    const end = "<?= $end ?>";
    const employee = "<?= $employee ?>";
    const branch = "<?= $branch ?>";

    let exportTitle = 'Reports - ';
    if (start && end && employee) {
      exportTitle += employee.replace(/\s+/g, '_') + '_' + `${start}_to_${end}`;
    } else if (branch) {
      exportTitle += branch.replace(/\s+/g, '_');
    } else if (start && end) {
      exportTitle += `${start}_to_${end}`;
    } else if (employee) {
      exportTitle += employee.replace(/\s+/g, '_');
    } else {
      exportTitle += new Date().toISOString().split('T')[0];
    }

    const table = $('#reportTable').DataTable({
      dom: '<"flex justify-between items-center mb-4"Bf>rt<"mt-4"lip>',
      order: [[3, 'desc']],
      columnDefs: [
        {
          targets: 0,
          orderable: false,
          className: 'select-checkbox',
          defaultContent: '',
          checkboxes: { selectRow: true }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:first-child'
      },
      buttons: [
        {
          extend: 'excelHtml5',
          title: exportTitle,
          exportOptions: { search: 'applied' },
          className: 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded mr-2'
        },
        {
          extend: 'pdfHtml5',
          title: exportTitle,
          orientation: 'landscape',
          pageSize: 'A4',
          exportOptions: { search: 'applied' },
          className: 'bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded mr-2'
        },
        {
          extend: 'print',
          title: exportTitle,
          exportOptions: { search: 'applied' },
          className: 'bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mr-2'
        },
        {
          text: 'Select All',
          action: function () {
            table.rows({ search: 'applied' }).select();
          },
          className: 'bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded mr-2'
        },
        {
          text: 'Deselect All',
          action: function () {
            table.rows().deselect();
          },
          className: 'bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded'
        }
      ]
    });

    $('#selectAll').on('click', function () {
      if (this.checked) {
        table.rows({ search: 'applied' }).select();
      } else {
        table.rows().deselect();
      }
    });
  });
</script>
</body>
</html>
