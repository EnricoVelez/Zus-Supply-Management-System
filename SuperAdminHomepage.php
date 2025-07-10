<!-- ADMIN -->
<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'super admin') {
    header("Location: ZusSupplyManagement.php");
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
        o. invoice,
        s.userFullName,
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
  <title>Zus Supply Management</title>
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

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">

<div class="flex min-h-screen bg-gray-100">

  <!-- Sidebar -->
   <?php include 'sidebar_superadmin.php'; ?>

  <!-- Main Dashboard Content -->
  <main class="flex-1 p-10 overflow-y-auto">
    <header class="mb-10">
      <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
      <p class="text-lg text-gray-600 mt-2">
        Welcome back, <span class="font-semibold"><?= htmlspecialchars($userFullName); ?></span>
      </p>
    </header>

    <!-- Stats cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Total Inventory Items</h2>
        <p class="text-3xl font-bold text-primary"><?= $totalInventoryItems ?></p>
    </div>


      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Pending Acknowledgments</h2>
        <p class="text-3xl font-bold text-primary"><?= $pendingCount ?></p>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Warehouses</h2>
        <p class="text-3xl font-bold text-primary"><?= $warehouseCount ?></p>
      </div>
    </section>

    <hr class="my-10 border-gray-300" />

    <!-- Recent Activity -->
    <section>
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Activity</h2>
      <div class="bg-white rounded-xl shadow-md p-6 max-w-4xl">
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
    </section>

    <hr class="my-10 border-gray-300" />

    <!-- Charts Grid -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-7xl mx-auto px-4">

      <!-- Orders by Status -->
      <div class="flex flex-col">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Orders by Status</h2>
        <div class="bg-white rounded-lg shadow p-6 flex-grow flex items-center justify-center">
          <canvas id="ordersByStatusChart" class="w-full h-64 md:h-72"></canvas>
        </div>
      </div>

      <!-- Orders by Branch -->
      <div class="flex flex-col">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Orders by Branch</h2>
        <div class="bg-white rounded-lg shadow p-6 flex-grow flex items-center justify-center">
          <canvas id="ordersByBranchChart" class="w-full h-64 md:h-72"></canvas>
        </div>
      </div>

      <!-- Top Ordered Supplies -->
      <div class="flex flex-col">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Top Ordered Supplies</h2>
        <div class="bg-white rounded-lg shadow p-6 flex-grow flex items-center justify-center">
          <canvas id="topSuppliesChart" class="w-full h-64 md:h-72"></canvas>
        </div>
      </div>

      <!-- Orders Over Time -->
      <div class="flex flex-col">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Orders Over Time (Last 30 Days)</h2>
        <div class="bg-white rounded-lg shadow p-6 flex-grow flex items-center justify-center">
          <canvas id="ordersOverTimeChart" class="w-full h-64 md:h-72"></canvas>
        </div>
      </div>

      <!-- Top Employees Performance (full width) -->
      <div class="md:col-span-2 flex flex-col">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Top Employees Performance</h2>
        <div class="bg-white rounded-lg shadow p-6 flex-grow flex items-center justify-center">
          <canvas id="employeePerformanceChart" class="w-full h-96 md:h-[400px]"></canvas>
        </div>
      </div>

    </section>


  </main>
</div>


<script>
  // Employee Performance
  fetch('graphs/emp_performance_data.php')
    .then(response => response.json())
    .then(data => {
      const ctx = document.getElementById('employeePerformanceChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.names,
          datasets: [
            {
              label: 'Orders Placed',
              data: data.placed,
              backgroundColor: '#3b82f6'
            },
            {
              label: 'Orders Received',
              data: data.acknowledged,
              backgroundColor: '#34d399'
            }
          ]
        },
        options: {
          responsive: true,
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
    })
    .catch(err => console.error('Error loading employee performance data:', err));

  // Orders Over Time
  fetch('graphs/get_orders_over_time.php')
    .then(response => response.json())
    .then(data => {
      const ordersOverTimeCtx = document.getElementById('ordersOverTimeChart').getContext('2d');
      new Chart(ordersOverTimeCtx, {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Orders per Day',
            data: data.data,
            fill: false,
            borderColor: '#1d4ed8',
            tension: 0.3,
            pointBackgroundColor: '#1d4ed8',
            pointRadius: 4
          }]
        },
        options: {
          responsive: true,
          scales: {
            x: {
              ticks: {
                maxRotation: 90,
                minRotation: 45
              }
            },
            y: {
              beginAtZero: true,
              precision: 0
            }
          },
          plugins: {
            legend: { display: true },
            tooltip: { mode: 'index', intersect: false }
          }
        }
      });
    })
    .catch(err => console.error('Error loading orders over time:', err));

    // Orders by Status
    fetch('graphs/orders_by_status.php')
    .then(response => response.json())
    .then(data => {
      const ctx = document.getElementById('ordersByStatusChart').getContext('2d');

      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: data.labels,
          datasets: [{
            data: data.counts,
            backgroundColor: ['#facc15', '#3b82f6', '#10b981', '#f87171'], // Yellow, Blue, Green, Red
            hoverOffset: 10
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            title: {
              display: true,
              text: 'Orders by Status'
            }
          }
        }
      });
    })
    .catch(err => {
      console.error('Error loading order status data:', err);
    });
  
  // Top ordered supplies
  fetch('graphs/top_supplies.php')
  .then(response => response.json())
  .then(data => {
    const ctx = document.getElementById('topSuppliesChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.labels, // SKU for X-axis
        datasets: [{
          label: 'Total Quantity Ordered',
          data: data.quantities,
          backgroundColor: '#facc15' // yellow-400
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              title: function(tooltipItems) {
                // Show the full item name in tooltip title
                const index = tooltipItems[0].dataIndex;
                return data.itemNames[index];
              },
              label: function(tooltipItem) {
                return `Quantity: ${tooltipItem.raw}`;
              }
            }
          }
        }
      }
    });
  })
  .catch(err => console.error('Error loading top supplies data:', err));

  // Orders by branch
  fetch('graphs/orders_by_branch.php')
  .then(response => response.json())
  .then(data => {
    const ctx = document.getElementById('ordersByBranchChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.branches,
        datasets: [{
          label: 'Orders per Branch',
          data: data.totals,
          backgroundColor: '#60a5fa' // blue-400
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (tooltipItem) => `Total Orders: ${tooltipItem.raw}`
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });
  })
  .catch(err => console.error('Error loading orders by branch:', err));
</script>
</body>
</html>
