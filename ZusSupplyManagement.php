<?php
session_start();

$host = "localhost";
$db = "supply_mgmt";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Form submitted
if (isset($_POST['username']) && isset($_POST['userpass'])) {
    $username = $_POST['username'];
    $password = $_POST['userpass'];

    $sql = "SELECT * FROM supply_mgmt WHERE username=? AND userpass=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['userID'] = $row['userID'];
        $_SESSION['userFullName'] = $row['userFullName'];
        $_SESSION['role'] = $row['role'];

        if ($row['role'] === 'supply chain employee') {
            header("Location: ZusSupplyHomepage.php");
            exit();
        } elseif ($row['role'] === 'employee') {
            header("Location: EmployeeHomepage.php");
            exit();
        } elseif ($row['role'] === 'super admin') {
            header("Location: SuperAdminHomepage.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Unknown role. Please contact the administrator.";
            header("Location: ZusLogin.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: ZusLogin.php");
        exit();
    }
} else {
    $_SESSION['login_error'] = "Please fill in the form.";
    header("Location: ZusLogin.php");
    exit();
}

$conn->close();
?>
