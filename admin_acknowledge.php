<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'supply chain employee') {
    header("Location: ZusSupplyManagement.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderID'])) {
    $orderID = $_POST['orderID'];

    $conn = new mysqli("localhost", "root", "", "supply_mgmt");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE orders SET admin_acknowledged = 1, admin_acknowledged_at = NOW(), status = 'pending' WHERE orderID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: admin_review_orders.php?success=1");
    exit();

} else {
    echo "Invalid request.";
}
