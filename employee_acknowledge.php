<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'employee') {
    header("Location: ZusSupplyManagement.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderID'])) {
    $orderID = $_POST['orderID'];

    $conn = new mysqli("localhost", "root", "", "supply_mgmt");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE orders SET employee_acknowledged = 1, employee_acknowledged_at = NOW(), status = 'order received' WHERE orderID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: employee_acknowledgements.php?success=1");
    exit();

} else {
    echo "Invalid request.";
}
