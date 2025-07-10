<?php
session_start();
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json'); // JSON response for AJAX

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'employee') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$userID = $_SESSION['userID'];
$orderDate = date('Y-m-d H:i:s');

// Properly parse the items array from POST
if (isset($_POST['items'])) {
    $items = $_POST['items'];
    // If your frontend sends JSON string instead, decode here:
    // $items = is_string($_POST['items']) ? json_decode($_POST['items'], true) : $_POST['items'];
} else {
    echo json_encode(['success' => false, 'message' => 'No order items found']);
    exit;
}

// Get employee name and branch
$nameStmt = $conn->prepare("SELECT userFullName, branch FROM supply_mgmt WHERE userID = ?");
$nameStmt->bind_param("i", $userID);
$nameStmt->execute();
$nameResult = $nameStmt->get_result()->fetch_assoc();
if (!$nameResult) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}
$employeeName = $nameResult['userFullName'];
$employeeBranch = $nameResult['branch'];
$nameStmt->close();

// Insert into orders table
$stmt = $conn->prepare("INSERT INTO orders (userID, orderDate, admin_acknowledged, employee_acknowledged) VALUES (?, ?, 0, 0)");
$stmt->bind_param("is", $userID, $orderDate);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to create order']);
    exit;
}
$orderID = $stmt->insert_id;
$stmt->close();

// Generate invoice number
$todayCode = date('ymd');
$todayStart = date('Y-m-d 00:00:00');
$todayEnd = date('Y-m-d 23:59:59');

$countQuery = $conn->prepare("SELECT COUNT(*) AS count FROM orders WHERE orderDate BETWEEN ? AND ?");
$countQuery->bind_param("ss", $todayStart, $todayEnd);
$countQuery->execute();
$countResult = $countQuery->get_result()->fetch_assoc();
$todayOrderCount = $countResult['count'] ?? 0;
$countQuery->close();

$invoiceSuffix = str_pad($todayOrderCount, 3, '0', STR_PAD_LEFT);
$invoice = $todayCode . '-' . $invoiceSuffix;

$updateStmt = $conn->prepare("UPDATE orders SET invoice = ? WHERE orderID = ?");
$updateStmt->bind_param("si", $invoice, $orderID);
$updateStmt->execute();
$updateStmt->close();

// Prepare statement to insert order items
$itemStmt = $conn->prepare("INSERT INTO orders_items (orderID, supply_ID, quantity, uom, req_qty) VALUES (?, ?, ?, ?, ?)");

foreach ($items as $item) {
    // Validate required keys and sanitize
    if (
        empty($item['supply_ID']) ||
        empty($item['quantity']) ||
        empty($item['uom']) ||
        empty($item['req_qty'])
    ) {
        continue; // skip invalid entries
    }

    $supply_ID_val = (int)$item['supply_ID'];
    $quantity_val = (int)$item['quantity'];
    $uom_val = $conn->real_escape_string($item['uom']);
    $req_qty_val = (int)$item['req_qty'];

    // Insert order item
    $itemStmt->bind_param("iiiss", $orderID, $supply_ID_val, $quantity_val, $uom_val, $req_qty_val);
    $itemStmt->execute();
}
$itemStmt->close();

// Send emails to supply chain employees
$emailQuery = $conn->query("SELECT userFullName, useremail FROM supply_mgmt WHERE role = 'supply chain employee'");
if (!$emailQuery) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch email recipients']);
    exit;
}

// Build item list HTML for email
$itemListHTML = "";
foreach ($items as $item) {
    if (empty($item['supply_ID'])) continue;

    $supply_ID_val = (int)$item['supply_ID'];

    $supplyQuery = $conn->prepare("SELECT item_name, SKU FROM supplies WHERE supply_ID = ?");
    $supplyQuery->bind_param("i", $supply_ID_val);
    $supplyQuery->execute();
    $supplyResult = $supplyQuery->get_result()->fetch_assoc();
    $supplyQuery->close();

    if ($supplyResult) {
        $itemName = htmlspecialchars($supplyResult['item_name']);
        $sku = htmlspecialchars($supplyResult['SKU']);
        $quantity_val = (int)$item['quantity'];
        $uom_val = htmlspecialchars($item['uom']);
        $req_qty_val = (int)$item['req_qty'];

        $itemListHTML .= "<tr>
            <td style='padding:8px;border:1px solid #ccc;'>$itemName</td>
            <td style='padding:8px;border:1px solid #ccc;'>$sku</td>
            <td style='padding:8px;border:1px solid #ccc;'>$quantity_val</td>
            <td style='padding:8px;border:1px solid #ccc;'>$uom_val</td>
            <td style='padding:8px;border:1px solid #ccc;'>$req_qty_val</td>
        </tr>";
    }
}

while ($row = $emailQuery->fetch_assoc()) {
    $recipientName = $row['userFullName'];
    $recipientEmail = $row['useremail'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'zuscoffeenotification@gmail.com';
        $mail->Password   = 'kmcr dfjx jmrj lwph'; // Consider environment vars or config file for security
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom('zuscoffeenotification@gmail.com', 'Zus Supply System');
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->isHTML(true);
        $mail->Subject = "New Order Placed - Invoice $invoice";

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <img src='cid:logo' alt='ZUS Coffee' style='width: 120px;'/>
            </div>
            <h2 style='color: #1d4ed8;'>Hello, $recipientName</h2>
            <p>A new order has been placed by <strong>$employeeName</strong> from the <strong>$employeeBranch</strong> branch.</p>
            <p><strong>Order Date:</strong> $orderDate<br>
               <strong>Invoice No.:</strong> $invoice</p>

            <table style='border-collapse: collapse; width: 100%; margin-top: 10px;'>
              <thead>
                <tr style='background-color: #f0f0f0;'>
                  <th style='padding: 8px; border: 1px solid #ccc;'>Item Name</th>
                  <th style='padding: 8px; border: 1px solid #ccc;'>SKU</th>
                  <th style='padding: 8px; border: 1px solid #ccc;'>Qty</th>
                  <th style='padding: 8px; border: 1px solid #ccc;'>UOM</th>
                  <th style='padding: 8px; border: 1px solid #ccc;'>Req Qty</th>
                </tr>
              </thead>
              <tbody>
                $itemListHTML
              </tbody>
            </table>

            <p style='margin-top: 20px;'>
              <a href='http://localhost/zus_system/admin_review_orders.php'
                 style='background-color: #1d4ed8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                 Review Now
              </a>
            </p>

            <p style='margin-top: 30px;'>Regards,<br><strong>Zus Supply Chain Management System</strong></p>
        </div>";

        $mail->AltBody = "Hello $recipientName,\n\n"
            . "$employeeName from $employeeBranch placed an order.\n"
            . "Invoice: $invoice\nDate: $orderDate";

        $mail->AddEmbeddedImage('logo.png', 'logo', 'logo.png');
        $mail->send();
    } catch (Exception $e) {
        error_log("Email failed to $recipientEmail: " . $mail->ErrorInfo);
    }
}

echo json_encode(['success' => true, 'message' => 'Your order has been placed successfully.']);
$conn->close();
exit;