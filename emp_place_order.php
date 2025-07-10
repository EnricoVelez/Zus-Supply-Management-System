<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'employee') {
    header("Location: ZusSupplyManagement.html");
    exit();
}

$userID = $_SESSION['userID'];
$conn = new mysqli("localhost", "root", "", "supply_mgmt");
$supplies = [];

$supplyQuery = $conn->query("SELECT supply_ID, item_name, SKU FROM supplies ORDER BY item_name ASC");
while ($row = $supplyQuery->fetch_assoc()) {
  $supplies[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Place New Order</title>

  <script src="https://cdn.tailwindcss.com"></script>


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

  <!-- SweetAlert on Success -->
  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <script>
      Swal.fire({
        title: 'Success!',
        text: 'Order placed successfully.',
        icon: 'success',
        confirmButtonColor: '#1d4ed8'
      });
    </script>
  <?php endif; ?>

  <!-- Main Content -->
  <div class="flex-1 p-10">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Place a New Order</h1>

    <form action="submit_order.php" method="POST" id="orderForm" class="space-y-6">
      <div id="itemContainer" class="space-y-4">
        <div class="flex flex-wrap md:flex-nowrap space-y-2 md:space-y-0 md:space-x-4 items-end item-row" data-index="0" style="gap: 0.5rem;">
          <select name="items[0][supply_ID]" class="select-supply flex-1 px-4 py-2 border border-gray-300 rounded" required onchange="updateSKU(this)" style="min-width: 200px;">
            <option value="" disabled selected>Select Item</option>
            <?php foreach ($supplies as $supply): ?>
              <option value="<?= $supply['supply_ID'] ?>" data-sku="<?= htmlspecialchars($supply['SKU']) ?>">
                <?= htmlspecialchars($supply['item_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <input type="text" name="items[0][sku]" placeholder="SKU" readonly class="w-40 px-4 py-2 border border-gray-300 rounded bg-gray-100" style="flex-shrink: 0;" />

          <input type="number" min="1" name="items[0][quantity]" placeholder="Quantity" required class="w-28 px-4 py-2 border border-gray-300 rounded" style="flex-shrink: 0;" />

          <select name="items[0][uom]" class="w-28 px-4 py-2 border border-gray-300 rounded" required style="flex-shrink: 0;">
            <option value="" disabled selected>Select UOM</option>
            <option value="CTN">CTN</option>
            <option value="BOX">BOX</option>
            <option value="PKT">PKT</option>
            <option value="BTL">BTL</option>
            <option value="TUB">TUB</option>
            <option value="PC">PC</option>
            <option value="UNIT">UNIT</option>
            <option value="BDL">BDL</option>
          </select>

          <input type="number" min="1" name="items[0][req_qty]" placeholder="Req Qty" required class="w-28 px-4 py-2 border border-gray-300 rounded" style="flex-shrink: 0;" />

          <button type="button" onclick="removeRow(this)" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 flex-shrink-0">X</button>
        </div>
      </div>

      <div class="flex gap-4 mt-4">
        <button type="button" onclick="addRow()" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">
          + Add Item
        </button>
        <button id="submitOrderBtn" type="submit" class="bg-primary hover:bg-primaryHover text-white font-semibold py-2 px-6 rounded">
          Submit Order
        </button>
      </div>
    </form>
  </div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
let itemIndex = 1;

function addRow() {
  const container = document.getElementById('itemContainer');
  const firstRow = document.querySelector('.item-row');

  // Destroy select2 on firstRow before cloning
  $(firstRow).find('.select-supply').select2('destroy');

  // Clone the firstRow
  const newRow = firstRow.cloneNode(true);
  newRow.setAttribute('data-index', itemIndex);

  // Reset all inputs/selects and update their names
  newRow.querySelectorAll('select, input').forEach(el => {
    if (el.tagName === 'SELECT') {
      el.selectedIndex = 0;
    } else {
      el.value = '';
    }

    const name = el.getAttribute('name');
    if (name) {
      const newName = name.replace(/\[\d+\]/, `[${itemIndex}]`);
      el.setAttribute('name', newName);
    }
  });

  // Attach onchange handler for the new select
  newRow.querySelector('.select-supply').onchange = function () {
    updateSKU(this);
  };

  container.appendChild(newRow);

  // Reinitialize select2 on all select-supply elements (including original and new)
  $('.select-supply').select2({ width: 'resolve' });

  itemIndex++;
}

function updateSKU(selectElem) {
  const sku = selectElem.selectedOptions[0]?.getAttribute('data-sku') || '';
  const inputSKU = selectElem.parentElement.querySelector('input[name$="[sku]"]');
  if (inputSKU) inputSKU.value = sku;
}

function removeRow(button) {
  const allRows = document.querySelectorAll('.item-row');
  if (allRows.length > 1) {
    button.closest('.item-row').remove();
  }
}

$(document).ready(function () {
  // Initialize select2 on page load
  $('.select-supply').select2({ width: 'resolve' });

  $('#orderForm').on('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    Swal.fire({
      title: 'Placing Order...',
      text: 'Please wait while we process your order.',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    $.ajax({
      url: 'submit_order.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          Swal.fire({
            title: 'Order Placed!',
            text: response.message,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = 'emp_place_order.php';
          });
        } else {
          Swal.fire({
            title: 'Error!',
            text: response.message || 'Something went wrong.',
            icon: 'error'
          });
        }
      },
      error: function (xhr, status, error) {
        Swal.fire({
          title: 'Error!',
          text: 'An unexpected error occurred.',
          icon: 'error'
        });
        console.error('AJAX Error:', error);
      }
    });
  });
});
</script>
</body>
</html>
