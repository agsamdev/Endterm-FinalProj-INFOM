<?php
session_start();
require 'db.php';

if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Auth Check - Ensures only logged-in users can access
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Data Fetching for User-level view
$recent_sales = $conn->query("
    SELECT s.total_amount, s.sale_date, c.first_name, c.last_name, p.product_name 
    FROM sales s 
    JOIN customers c ON s.customer_id = c.customer_id 
    JOIN products p ON s.product_id = p.product_id 
    ORDER BY s.sale_id DESC LIMIT 8
");

$low_stock = $conn->query("SELECT product_name, stock_quantity FROM products WHERE stock_quantity < 5 LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalesApp | User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
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
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-3">Operations</p>
                <a href="user_dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-indigo-600 text-white shadow-indigo-100 shadow-md transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="text-sm font-bold">Dashboard</span>
                </a>
                <a href="customers.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition-all">
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
            </nav>
        </div>

        <div class="mt-auto p-6 border-t border-slate-50">
            <a href="logout.php" class="flex items-center justify-center space-x-2 px-4 py-4 rounded-2xl bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest hover:bg-rose-100 transition-all">
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-10 min-h-screen">
        <header class="flex justify-between items-center mb-12">
            <div>
                <p class="text-indigo-600 font-bold text-xs uppercase tracking-widest mb-1">Staff Portal</p>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Active Terminal</h1>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-xs font-bold text-slate-900"><?= h($_SESSION['username'] ?? 'User') ?></p>
                    <p class="text-[10px] text-indigo-500 font-black uppercase">Standard Access</p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center border-2 border-white shadow-sm font-black text-indigo-600">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div class="lg:col-span-8 bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-slate-800 uppercase text-xs tracking-widest">Recent Activity</h3>
                    <a href="sales.php" class="text-[10px] font-black text-indigo-600 uppercase tracking-tighter hover:underline">View Journal</a>
                </div>
                <table class="w-full">
                    <tbody class="divide-y divide-slate-50">
                        <?php while($s = $recent_sales->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-6">
                                <span class="text-sm font-bold text-slate-900"><?= h($s['first_name'].' '.$s['last_name']) ?></span>
                                <p class="text-[10px] text-slate-400 font-bold uppercase mt-0.5"><?= date('M d, H:i', strtotime($s['sale_date'])) ?></p>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-[10px] font-black text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg uppercase tracking-tight"><?= h($s['product_name']) ?></span>
                            </td>
                            <td class="px-8 py-6 text-right font-black text-slate-900">$<?= number_format($s['total_amount'], 2) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="lg:col-span-4 space-y-8">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <h3 class="font-black text-slate-800 uppercase text-xs tracking-widest mb-6">Inventory Alerts</h3>
                    <div class="space-y-4">
                        <?php while($item = $low_stock->fetch_assoc()): ?>
                        <div class="flex justify-between items-center p-4 bg-amber-50 rounded-2xl border border-amber-100">
                            <span class="text-xs font-bold text-amber-900"><?= h($item['product_name']) ?></span>
                            <span class="text-[10px] font-black text-amber-600"><?= $item['stock_quantity'] ?> left</span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-indigo-100 shadow-2xl">
                    <p class="text-indigo-400 text-[10px] font-black uppercase tracking-[0.2em] mb-6">Terminal Task</p>
                    <a href="sales.php" class="block w-full py-5 bg-indigo-600 text-white text-center text-xs font-black rounded-2xl uppercase tracking-widest hover:bg-indigo-700 hover:scale-[1.03] transition-all shadow-lg shadow-indigo-900/20">
                        Process New Sale
                    </a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>