<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
  echo json_encode(['success' => false, 'message' => 'Database connection failed']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

$userID = $_SESSION['userID'] ?? null;
$employeeName = $_SESSION['name'] ?? 'Unknown';
if (!$userID) {
  echo json_encode(['success' => false, 'message' => 'User not authenticated']);
  exit;
}

$outlet = $_POST['outlet'] ?? '';
$date_requested = $_POST['date_requested'] ?? '';
$date_required = $_POST['date_required'] ?? '';
$items = $_POST['items'] ?? [];

if (empty($outlet) || empty($date_requested) || empty($date_required) || empty($items)) {
  echo json_encode(['success' => false, 'message' => 'Missing required fields']);
  exit;
}

// ✅ Generate invoice number (YYMMDD-###)
$formattedDate = date('ymd', strtotime($date_requested));
$check = $conn->prepare("SELECT COUNT(*) FROM orders WHERE orderDate = ?");
$check->bind_param("s", $date_requested);
$check->execute();
$check->bind_result($orderCount);
$check->fetch();
$check->close();

$orderNumber = str_pad($orderCount + 1, 3, '0', STR_PAD_LEFT);
$invoice = $formattedDate . '-' . $orderNumber;

// ✅ Insert into `orders` table
$insertOrder = $conn->prepare("INSERT INTO orders 
  (invoice, userID, outlet, orderDate, date_requested, date_required, admin_acknowledged, employee_acknowledged, status, date_prepared, date_delivered) 
  VALUES (?, ?, ?, ?, ?, ?, 1, 1, NULL, NULL, NULL)");
$insertOrder->bind_param("sissss", $invoice, $userID, $outlet, $date_requested, $date_requested, $date_required);
if (!$insertOrder->execute()) {
  echo json_encode(['success' => false, 'message' => 'Failed to insert order header']);
  exit;
}
$orderID = $insertOrder->insert_id;
$insertOrder->close();

// ✅ Insert items
$insertItem = $conn->prepare("INSERT INTO orders_items 
  (orderID, supply_ID, quantity, uom, req_qty) 
  VALUES (?, ?, ?, ?, ?)");

foreach ($items as $item) {
  $supplyID = (int)($item['supply_ID'] ?? 0);
  $qty = (int)($item['qty'] ?? 0);
  $uom = $item['uom'] ?? '';
  $reqQty = (int)($item['req_qty'] ?? 0);

  // ✅ Validate minimal required values
  if ($supplyID === 0 || $qty <= 0 || empty($uom)) continue;

  // ✅ Insert into orders_items table
  $insertItem->bind_param("iiisi", $orderID, $supplyID, $qty, $uom, $reqQty);
  $insertItem->execute();

  // ✅ Optional error logging for debugging
  if ($insertItem->error) {
    error_log("Insert into orders_items failed: " . $insertItem->error);
  }
}

$insertItem->close();



echo json_encode(['success' => true, 'message' => 'Order successfully submitted!']);
