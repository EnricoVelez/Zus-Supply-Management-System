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

$supplies = [];
$result = $conn->query("SELECT supply_ID, SKU, item_name, warehouse FROM supplies ORDER BY item_name ASC");
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $supplies[] = $row;
  }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin - Inventory</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

  <?php include 'admin_sidebar.php'; ?>

  <div class="flex-1 p-10">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Inventory Supplies</h1>
      <div class="space-x-2">
        <button id="addItemBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-primaryHover">+ Add New Item</button>
        <button class="bg-gray-600 text-white px-4 py-2 rounded cursor-not-allowed opacity-60" disabled>📁 Import Data</button>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
      <table id="inventoryTable" class="display w-full text-sm">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th>SKU</th>
            <th>Item Name</th>
            <th>Warehouse</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($supplies as $supply): ?>
          <tr>
            <td><?= htmlspecialchars($supply['SKU']) ?></td>
            <td><?= htmlspecialchars($supply['item_name']) ?></td>
            <td><?= htmlspecialchars($supply['warehouse']) ?></td>
            <td>
              <button
                class="editBtn bg-yellow-500 text-white px-3 py-1 rounded text-sm mr-2"
                data-id="<?= $supply['supply_ID'] ?>"
                data-sku="<?= htmlspecialchars($supply['SKU']) ?>"
                data-name="<?= htmlspecialchars($supply['item_name']) ?>"
                data-warehouse="<?= htmlspecialchars($supply['warehouse']) ?>">
                ✏️ Edit
              </button>
              <button
                class="deleteBtn bg-red-500 text-white px-3 py-1 rounded text-sm"
                data-id="<?= $supply['supply_ID'] ?>">
                🗑️ Delete
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <div id="supplyModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-xl font-bold mb-4" id="modalTitle">Add Supply Item</h2>
      <form id="supplyForm">
        <input type="hidden" name="supply_ID" id="supply_ID" />

        <label class="block mb-2 text-sm">SKU</label>
        <input type="text" name="SKU" id="sku" class="w-full mb-4 px-3 py-2 border rounded" required />

        <label class="block mb-2 text-sm">Item Name</label>
        <input type="text" name="item_name" id="item_name" class="w-full mb-4 px-3 py-2 border rounded" required />

        <label class="block mb-2 text-sm">Warehouse</label>
        <input type="text" name="warehouse" id="warehouse" class="w-full mb-4 px-3 py-2 border rounded" required />

        <div class="flex justify-end gap-2">
          <button type="button" id="cancelBtn" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
        </div>
      </form>
    </div>
  </div>

<script>
$(document).ready(function () {
  $('#inventoryTable').DataTable({
    scrollY: '400px',
    scrollCollapse: true,
    paging: false,
    order: [[1, 'asc']]
  });

  const modal = $('#supplyModal');
  const form = $('#supplyForm');

  $('#addItemBtn').click(() => {
    $('#modalTitle').text('Add Supply Item');
    form[0].reset();
    $('#supply_ID').val('');
    modal.fadeIn();
  });

  $('#cancelBtn').click(() => modal.fadeOut());

  $('.editBtn').click(function () {
    $('#modalTitle').text('Edit Supply Item');
    $('#supply_ID').val($(this).data('id'));
    $('#sku').val($(this).data('sku'));
    $('#item_name').val($(this).data('name'));
    $('#warehouse').val($(this).data('warehouse'));
    modal.fadeIn();
  });

  form.on('submit', function (e) {
    e.preventDefault();
    const formData = form.serialize();

    Swal.fire({
      title: 'Are you sure?',
      text: 'Do you want to save these changes?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, save it!'
    }).then(result => {
      if (result.isConfirmed) {
        $.post('manage_supply.php', formData, function (res) {
          if (res.success) {
            Swal.fire('Success', res.message, 'success').then(() => location.reload());
          } else {
            Swal.fire('Error', res.message, 'error');
          }
        }, 'json');
      }
    });
  });

  $('.deleteBtn').click(function () {
    const supplyID = $(this).data('id');
    Swal.fire({
      title: 'Delete Item?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      confirmButtonColor: '#dc2626'
    }).then(result => {
      if (result.isConfirmed) {
        $.post('delete_supply.php', { id: supplyID }, function (res) {
          if (res.success) {
            Swal.fire('Deleted!', res.message, 'success').then(() => location.reload());
          } else {
            Swal.fire('Error', res.message, 'error');
          }
        }, 'json');
      }
    });
  });
});
</script>

</body>
</html>
