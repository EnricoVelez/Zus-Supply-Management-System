<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'supply chain employee') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
  echo json_encode(['success' => false, 'message' => 'Connection error']);
  exit;
}

$sku = trim($_POST['SKU']);
$name = trim($_POST['item_name']);
$warehouse = trim($_POST['warehouse']);
$id = $_POST['supply_ID'] ?? null;

// Check for duplicates
$checkSql = "SELECT supply_ID FROM supplies WHERE SKU = ? AND item_name = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ss", $sku, $name);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0 && (!$id || $checkResult->fetch_assoc()['supply_ID'] != $id)) {
  echo json_encode(['success' => false, 'message' => 'Duplicate SKU + Item Name combination']);
  exit;
}
$checkStmt->close();

// Insert or update
if ($id) {
  $stmt = $conn->prepare("UPDATE supplies SET SKU=?, item_name=?, warehouse=? WHERE supply_ID=?");
  $stmt->bind_param("sssi", $sku, $name, $warehouse, $id);
  $action = "updated";
} else {
  $stmt = $conn->prepare("INSERT INTO supplies (SKU, item_name, warehouse) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $sku, $name, $warehouse);
  $action = "added";
}

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => "Supply successfully $action."]);
} else {
  echo json_encode(['success' => false, 'message' => 'Database error']);
}
$stmt->close();
$conn->close();
