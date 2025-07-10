<?php
$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$sql = "
    SELECT DATE(orderDate) as order_day, COUNT(*) as order_count
    FROM orders
    WHERE orderDate >= CURDATE() - INTERVAL 30 DAY
    GROUP BY order_day
    ORDER BY order_day ASC
";

$labels = [];
$data = [];

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['order_day'];
    $data[] = $row['order_count'];
}

echo json_encode(['labels' => $labels, 'data' => $data]);
?>
