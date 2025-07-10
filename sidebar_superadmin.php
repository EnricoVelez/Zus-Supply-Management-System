<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="w-64 bg-[#1D4ED8] text-white flex flex-col items-center p-6 shadow-lg min-h-screen">
    <img src="logo.png" alt="ZUS Coffee Logo" class="w-24 mb-6 rounded-full shadow-lg" />
    <h2 class="text-2xl font-bold mb-8 text-center">Supply Management</h2>
    
    <nav class="flex flex-col space-y-4 w-full">
        <a href="SuperAdminHomepage.php" 
           class="p-3 rounded transition hover:bg-primaryHover <?= $currentPage === 'SuperAdminHomepage.php' ? 'bg-primary font-semibold' : '' ?>">
           Dashboard
        </a>
        <a href="superadmin_reports.php" 
           class="p-3 rounded transition hover:bg-primaryHover <?= $currentPage === 'superadmin_reports.php' ? 'bg-primary font-semibold' : '' ?>">
           Reports
        </a>
        <a href="logout.php" 
           class="p-3 rounded transition hover:bg-primaryHover">
           Logout
        </a>
    </nav>
</div>
