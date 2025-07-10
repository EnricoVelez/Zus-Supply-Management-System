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

$employeeID = $_SESSION['userID'];
$userFullName = $_SESSION['userFullName'] ?? 'Unknown User';

$supplies = [];
$result = $conn->query("SELECT * FROM supplies ORDER BY item_name ASC");
while ($row = $result->fetch_assoc()) {
  $supplies[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Employee History</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">
     <?php include 'admin_sidebar.php'; ?>

    <div class="flex-1 p-10">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Order History</h1>
    <form id="historyForm">
      <div class="mb-4 grid grid-cols-2 gap-4">
        <div>
          <label>Requested By:</label>
          <input type="text" readonly class="w-full border p-2 rounded" value="<?= htmlspecialchars($userFullName) ?>" />
        </div>
        <div>
          <label>Outlet:</label>
          <select name="outlet" class="w-full border p-2 rounded" required>
            <option value="">Select Branch</option>
            <option value="Setia Point">Setia Point</option>
            <option value="The Curve">The Curve</option>
            <option value="Sunway Center">Sunway Center</option>
            <option value="Jerudong">Jerudong</option>
          </select>
        </div>
        <div>
          <label>Date Requested:</label>
          <input type="date" name="date_requested" class="w-full border p-2 rounded" required />
        </div>
        <div>
          <label>Date Required:</label>
          <input type="date" name="date_required" class="w-full border p-2 rounded" required />
        </div>
      </div>

      <h2 class="text-xl font-bold mb-2">Items</h2>
      <table class="w-full text-sm mb-4" id="itemTable">
        <thead>
          <tr class="bg-gray-200">
            <th>#</th>
            <th>Item Name</th>
            <th>SKU</th>
            <th>Qty</th>
            <th>UOM</th>
            <th>Remark</th>
            <th>Warehouse</th>
            <th>Req Qty</th>
            <th></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <button type="button" id="addRowBtn" class="bg-blue-500 text-white px-4 py-2 rounded">+ Add Item</button>
      <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded ml-2">Submit</button>
    </form>
</div>

<script>
let supplies = <?= json_encode($supplies) ?>;

function newRow(index) {
  const itemOptions = supplies.map(s =>
    `<option value="${s.supply_ID}" data-sku="${s.SKU}" data-warehouse="${s.warehouse}">${s.item_name}</option>`
  ).join('');

  const uomOptions = ['PC', 'CTN', 'PKT', 'BOX', 'BTL', 'TUB', 'UNIT', 'BDL'].map(u =>
    `<option value="${u}">${u}</option>`
  ).join('');

  return `<tr>
    <td>${index + 1}</td>
    <td>
      <select name="items[${index}][supply_ID]" class="itemDropdown w-full p-1 border rounded">
        <option value="" disabled selected>Select Item</option>
        ${itemOptions}
      </select>
      <input type="hidden" name="items[${index}][item_name]" class="itemName">
    </td>
    <td><input type="text" name="items[${index}][sku]" class="sku w-full border p-1 bg-gray-100" readonly></td>
    <td><input type="number" name="items[${index}][qty]" required class="w-full border p-1"></td>
    <td>
      <select name="items[${index}][uom]" required class="w-full border p-1 rounded">
        <option value="" disabled selected>Select UOM</option>
        ${uomOptions}
      </select>
    </td>
    <td><input type="text" name="items[${index}][remark]" class="w-full border p-1"></td>
    <td><input type="text" name="items[${index}][warehouse]" class="warehouse w-full border p-1 bg-gray-100" readonly></td>
    <td><input type="number" name="items[${index}][req_qty]" required class="w-full border p-1"></td>
    <td><button type="button" class="removeBtn text-red-600">✖</button></td>
  </tr>`;
}

function updateHandlers() {
  $('.itemDropdown').off().on('change', function () {
    const selected = $(this).find(':selected');
    const row = $(this).closest('tr');
    row.find('.sku').val(selected.data('sku') || '');
    row.find('.warehouse').val(selected.data('warehouse') || '');
    row.find('.itemName').val(selected.text()); // Set item_name value
  });


  $('.removeBtn').off().on('click', function () {
    $(this).closest('tr').remove();
  });
}

$('#addRowBtn').click(() => {
  const i = $('#itemTable tbody tr').length;
  $('#itemTable tbody').append(newRow(i));
  updateHandlers();

  // 🔁 Auto-trigger change for pre-selected item (fix for first row)
  const $newSelect = $('#itemTable tbody tr:last .itemDropdown');
  $newSelect.trigger('change');
});

$(document).ready(function () {
  $('#historyForm').submit(function (e) {
    e.preventDefault();
    console.log('Form submit triggered'); // ✅ Confirm this appears
    const form = $(this);
    const data = form.serialize();

    Swal.fire({
      title: 'Confirm Submission',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Submit'
    }).then(result => {
      if (result.isConfirmed) {
        $.post('submit_history.php', data, function (res) {
          if (res.success) {
            Swal.fire('Success', res.message, 'success').then(() => location.reload());
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
