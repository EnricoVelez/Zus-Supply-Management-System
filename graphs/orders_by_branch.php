<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$sql = "
    SELECT sm.branch, COUNT(o.orderID) as total_orders
    FROM orders o
    JOIN supply_mgmt sm ON o.userID = sm.userID
    GROUP BY sm.branch
    ORDER BY total_orders DESC
";

$result = $conn->query($sql);

$data = [
    'branches' => [],
    'totals' => []
];

while ($row = $result->fetch_assoc()) {
    $data['branches'][] = $row['branch'];
    $data['totals'][] = (int)$row['total_orders'];
}

$conn->close();

echo json_encode($data);
?>
