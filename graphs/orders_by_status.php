<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$sql = "
    SELECT 
        status, 
        COUNT(*) as count 
    FROM orders 
    GROUP BY status
";

$result = $conn->query($sql);

$data = [
    "labels" => [],
    "counts" => []
];

while ($row = $result->fetch_assoc()) {
    $status = $row['status'];

    // If null or empty, label as "Waiting for Acknowledgment"
    if (is_null($status) || trim($status) === '') {
        $statusLabel = "Waiting for Acknowledgment";
    } else {
        $statusLabel = ucfirst($status);
    }

    $data['labels'][] = $statusLabel;
    $data['counts'][] = (int)$row['count'];
}

$conn->close();

echo json_encode($data);
?>
