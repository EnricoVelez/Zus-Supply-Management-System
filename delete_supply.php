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

$id = $_POST['id'] ?? null;
if (!$id) {
  echo json_encode(['success' => false, 'message' => 'Missing supply ID']);
  exit;
}

$stmt = $conn->prepare("DELETE FROM supplies WHERE supply_ID = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => 'Item deleted successfully.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Deletion failed.']);
}
$stmt->close();
$conn->close();
