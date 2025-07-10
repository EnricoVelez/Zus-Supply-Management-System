<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'employee') {
    header("Location: ZusSupplyManagement.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "supply_mgmt");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userID = $_SESSION['userID'];
$successMsg = "";
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['userFullName'];
    $branch = $_POST['branch'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // If password is not empty, hash it; else keep the current password
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE supply_mgmt SET userFullName = ?, branch = ?, username = ?, password = ? WHERE userID = ?");
        $updateStmt->bind_param("ssssi", $fullName, $branch, $username, $hashedPassword, $userID);
    } else {
        $updateStmt = $conn->prepare("UPDATE supply_mgmt SET userFullName = ?, branch = ?, username = ? WHERE userID = ?");
        $updateStmt->bind_param("sssi", $fullName, $branch, $username, $userID);
    }

    if ($updateStmt->execute()) {
        $successMsg = "Profile updated successfully.";
        $_SESSION['userFullName'] = $fullName; // Update session
    } else {
        $errorMsg = "Failed to update profile.";
    }

    $updateStmt->close();
}

// Fetch employee info
$stmt = $conn->prepare("SELECT userFullName, branch, username FROM supply_mgmt WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Employee Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1d4ed8',
            primaryHover: '#1e40af',
          }
        }
      }
    }
  </script>
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

  <!-- Sidebar -->
  <?php include 'employee_sidebar.php'; ?>

  <!-- Main Content -->
  <div class="flex-1 p-10">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Profile</h1>

    <?php if (!empty($successMsg)): ?>
      <div class="bg-green-100 text-green-800 p-4 rounded mb-4"><?= $successMsg ?></div>
    <?php elseif (!empty($errorMsg)): ?>
      <div class="bg-red-100 text-red-800 p-4 rounded mb-4"><?= $errorMsg ?></div>
    <?php endif; ?>

    <form method="POST" id="profileForm" class="bg-white p-6 rounded-xl shadow-md space-y-6 max-w-xl">
      <div>
        <label class="block text-gray-700 font-semibold mb-1">Full Name</label>
        <input type="text" name="userFullName" value="<?= htmlspecialchars($employee['userFullName']) ?>" required class="w-full px-4 py-2 border border-gray-300 rounded" />
      </div>

      <div>
        <label class="block text-gray-700 font-semibold mb-1">Branch</label>
        <select name="branch" required class="w-full px-4 py-2 border border-gray-300 rounded">
          <?php
            $branches = ["Setia Point", "The Curve", "Jerudong", "Sunway Center"];
            foreach ($branches as $branch):
              $selected = ($employee['branch'] === $branch) ? 'selected' : '';
              echo "<option value=\"$branch\" $selected>$branch</option>";
            endforeach;
          ?>
        </select>
      </div>

      <div>
        <label class="block text-gray-700 font-semibold mb-1">Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($employee['username']) ?>" required class="w-full px-4 py-2 border border-gray-300 rounded" />
      </div>

      <div>
        <label class="block text-gray-700 font-semibold mb-1">Password</label>
        <input type="password" name="password" placeholder="Leave blank to keep current password" class="w-full px-4 py-2 border border-gray-300 rounded" />
      </div>

      <div>
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-primaryHover">Update Profile</button>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Stop the default submit

    Swal.fire({
      title: 'Are you sure?',
      text: "Do you want to save the changes to your profile?",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#1d4ed8',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, save changes'
    }).then((result) => {
      if (result.isConfirmed) {
        this.submit(); // Submit form after confirmation
      }
    });
  });
</script>

</body>
</html>
