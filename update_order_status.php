<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'supply chain employee') {
  header("Location: ZusSupplyManagement.html");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderID'], $_POST['status'])) {
  $orderID = $_POST['orderID'];
  $status = $_POST['status'];

  $conn = new mysqli("localhost", "root", "", "supply_mgmt");
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  if ($status === 'prepared') {
    $sql = "UPDATE orders SET status = ?, date_prepared = NOW() WHERE orderID = ?";
  } elseif ($status === 'delivered') {
    $sql = "UPDATE orders SET status = ?, date_delivered = NOW() WHERE orderID = ?";
  } else {
    $sql = "UPDATE orders SET status = ? WHERE orderID = ?";
  }

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("si", $status, $orderID);
  $stmt->execute();
  $stmt->close();
  $conn->close();

  header("Location: admin_review_orders.php");
  exit();
} else {
  echo "Invalid request.";
}
