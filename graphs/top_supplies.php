<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$sql = "
    SELECT s.SKU, s.item_name, SUM(oi.quantity) AS total_quantity
    FROM orders_items oi
    JOIN supplies s ON oi.supply_ID = s.supply_ID
    GROUP BY oi.supply_ID
    ORDER BY total_quantity DESC
    LIMIT 5
";

$result = $conn->query($sql);

$data = [
    "labels" => [],
    "quantities" => [],
    "itemNames" => []
];

while ($row = $result->fetch_assoc()) {
    $data['labels'][] = $row['SKU'];
    $data['quantities'][] = (int)$row['total_quantity'];
    $data['itemNames'][] = $row['item_name'];
}

$conn->close();

echo json_encode($data);
?>
