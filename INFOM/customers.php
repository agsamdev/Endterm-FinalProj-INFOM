<?php
session_start();
require 'db.php';

if (!function_exists('h')) {
    function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


$message = "";
$status = "";

if (isset($_POST['add_customer'])) {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $phone      = $_POST['phone'];
    $created_at = $_POST['created_at'];

    $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, phone, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $created_at);

    if ($stmt->execute()) {
        $message = "Customer successfully enrolled.";
        $status = "success";
    } else {
        $message = "Database error: " . $conn->error;
        $status = "error";
    }
    $stmt->close();
}

$customers = $conn->query("SELECT * FROM customers ORDER BY customer_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalesApp | Customers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-[#F8FAFC] text-slate-900 flex">

    <aside class="w-64 h-screen bg-white border-r border-slate-200 fixed left-0 top-0 flex flex-col z-50">
        <div class="p-8">
            <div class="flex items-center space-x-3 mb-10">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-100">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <span class="text-sm font-black tracking-widest text-slate-800 uppercase">Sales<span class="text-indigo-600">App</span></span>
            </div>
            <nav class="space-y-1">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-3">Main Menu</p>
                <a href="admin.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="text-sm font-bold">Dashboard</span>
                </a>
                <a href="customers.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-indigo-600 text-white shadow-indigo-100 shadow-md transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="text-sm font-bold">Customers</span>
                </a>
                <a href="products.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m-8-4V7m8 4v10M4 7l8 4"></path></svg>
                    <span class="text-sm font-bold">Products</span>
                </a>
                <a href="sales.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="text-sm font-bold">Sales</span>
                </a>
               


    <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
         <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest pt-6 mb-4 ml-3">Administration</p>
       <a href="users.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-9-3.375M15 6.75a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-sm font-bold">Users</span>
                </a>
                <a href="logs.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-bold">System Logs</span>
                </a>
    <?php endif; ?>
                

            </nav>
        </div>
        <div class="mt-auto p-6">
            <a href="logout.php" class="flex items-center justify-center space-x-2 px-4 py-4 rounded-2xl bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest hover:bg-rose-100 transition-all"><span>Sign Out</span></a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-10 min-h-screen">
        <header class="mb-12">
            <p class="text-indigo-600 font-bold text-xs uppercase tracking-widest mb-1">CRM Management</p>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Client Directory</h1>
        </header>

        <?php if ($message): ?>
            <div class="mb-8 p-5 rounded-2xl border text-[10px] font-black uppercase tracking-widest <?= $status === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100' ?>">
                <?= h($message) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div class="lg:col-span-4">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <h3 class="text-xs font-black text-slate-800 mb-6 uppercase tracking-widest text-center">Enroll Customer</h3>
                    <form method="POST" class="space-y-4">
                        <input type="text" name="first_name" placeholder="First Name" required class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm">
                        <input type="text" name="last_name" placeholder="Last Name" required class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm">
                        <input type="email" name="email" placeholder="Email Address" required class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm">
                        <input type="text" name="phone" placeholder="Contact Phone" class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm">
                        <input type="date" name="created_at" value="<?= date('Y-m-d') ?>" class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm">
                        <button type="submit" name="add_customer" class="bg-slate-900 w-full py-5 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-indigo-100">
                            Save Client Profile
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-8 bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Records</h3>
                    <span class="text-[10px] text-slate-500 font-bold uppercase"><?= $customers->num_rows ?> Total</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-[10px] text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5">Full Identity</th>
                                <th class="px-8 py-5">Communication</th>
                                <th class="px-8 py-5 text-right">Registration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php while ($row = $customers->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="text-sm font-black text-slate-900"><?= h($row['first_name'] . ' ' . $row['last_name']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase mt-1">ID: #<?= h($row['customer_id']) ?></div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-xs font-bold text-slate-600"><?= h($row['email']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-1 tracking-tighter"><?= h($row['phone'] ?: 'No Phone Data') ?></div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <span class="text-[10px] font-black bg-slate-100 text-slate-500 px-3 py-1.5 rounded-lg border border-slate-200 uppercase">
                                        <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>