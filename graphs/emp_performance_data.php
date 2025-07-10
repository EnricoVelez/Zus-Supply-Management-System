<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'super admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$sql = "
    SELECT 
        sm.userFullName,
        COUNT(o.orderID) AS orders_placed,
        SUM(CASE WHEN o.employee_acknowledged = 1 THEN 1 ELSE 0 END) AS orders_acknowledged
    FROM supply_mgmt sm
    LEFT JOIN orders o ON sm.userID = o.userID
    WHERE sm.role = 'employee'
    GROUP BY sm.userFullName
    ORDER BY orders_placed DESC
    LIMIT 5
";

$result = $conn->query($sql);

$data = ['names' => [], 'placed' => [], 'acknowledged' => []];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data['names'][] = $row['userFullName'];
        $data['placed'][] = (int)$row['orders_placed'];
        $data['acknowledged'][] = (int)$row['orders_acknowledged'];
    }
}

$conn->close();

echo json_encode($data);
