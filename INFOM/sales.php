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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)$_POST['customer_id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $sale_date = $_POST['sale_date'];

    if ($customer_id > 0 && $product_id > 0 && $quantity > 0) {
        $stmt = $conn->prepare("SELECT price, stock_quantity FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($product && $product['stock_quantity'] >= $quantity) {
            $total_amount = (float)$product['price'] * $quantity;
            $conn->begin_transaction();
            try {
                $stmt1 = $conn->prepare("INSERT INTO sales (customer_id, product_id, quantity, sale_date, total_amount) VALUES (?, ?, ?, ?, ?)");
                $stmt1->bind_param("iiisd", $customer_id, $product_id, $quantity, $sale_date, $total_amount);
                $stmt1->execute();
                
                $stmt2 = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                $stmt2->bind_param("ii", $quantity, $product_id);
                $stmt2->execute();
                
                $conn->commit();
                $message = "Transaction finalized.";
                $status = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Operation failed: Transaction rolled back.";
                $status = "error";
            }
        } else {
            $message = "Insufficient inventory depth.";
            $status = "error";
        }
    }
}

$customers = $conn->query("SELECT customer_id, first_name, last_name FROM customers ORDER BY first_name ASC");
$products = $conn->query("SELECT product_id, product_name, price, stock_quantity FROM products ORDER BY product_name ASC");
$sales_query = $conn->query("SELECT s.*, c.first_name, c.last_name, p.product_name FROM sales s JOIN customers c ON s.customer_id = c.customer_id JOIN products p ON s.product_id = p.product_id ORDER BY s.sale_id DESC");

$total_revenue = 0;
$sales_data = [];
while ($row = $sales_query->fetch_assoc()) {
    $sales_data[] = $row;
    $total_revenue += $row['total_amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalesApp | Records</title>
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
                <a href="customers.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="text-sm font-bold">Customers</span>
                </a>
                <a href="products.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m-8-4V7m8 4v10M4 7l8 4"></path></svg>
                    <span class="text-sm font-bold">Products</span>
                </a>
                <a href="sales.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-indigo-600 text-white shadow-indigo-100 shadow-md transition-all">
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
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <p class="text-indigo-600 font-bold text-xs uppercase tracking-widest mb-1">Financial Log</p>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Sales Journal</h1>
            </div>
            <div class="bg-slate-900 text-white px-8 py-6 rounded-[2.5rem] shadow-2xl shadow-indigo-100 border border-slate-800">
                <p class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-1">Gross Revenue</p>
                <p class="text-4xl font-black tracking-tight">$<?= number_format($total_revenue, 2) ?></p>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="mb-8 p-5 rounded-2xl border text-[10px] font-black uppercase tracking-widest <?= $status === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100' ?>">
                <?= h($message) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div class="lg:col-span-4">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <h3 class="text-xs font-black text-slate-800 mb-6 uppercase tracking-widest text-center">Post Transaction</h3>
                    <form method="POST" class="space-y-5">
                        <select name="customer_id" required class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm text-slate-700">
                            <option value="">Select Client...</option>
                            <?php while ($c = $customers->fetch_assoc()): ?>
                                <option value="<?= $c['customer_id'] ?>"><?= h($c['first_name'] . ' ' . $c['last_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <select name="product_id" required class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm text-slate-700">
                            <option value="">Select Item...</option>
                            <?php while ($p = $products->fetch_assoc()): ?>
                                <option value="<?= $p['product_id'] ?>" <?= $p['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                                    <?= h($p['product_name']) ?> ($<?= number_format($p['price'], 2) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="number" name="quantity" min="1" value="1" required class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm">
                            <input type="date" name="sale_date" value="<?= date('Y-m-d') ?>" required class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm">
                        </div>
                        <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-indigo-100">
                            Commit Entry
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-8 bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Historical Data</h3>
                    <span class="text-[10px] text-slate-500 font-bold uppercase"><?= count($sales_data) ?> Ledger Entries</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-[10px] text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5">Entity / Timestamp</th>
                                <th class="px-8 py-5">Object / Qty</th>
                                <th class="px-8 py-5 text-right">Valuation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($sales_data as $row): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-6">
                                        <div class="text-sm font-black text-slate-900"><?= h($row['first_name'] . ' ' . $row['last_name']) ?></div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase mt-1"><?= date('M d, Y', strtotime($row['sale_date'])) ?></div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-xs font-bold text-slate-700"><?= h($row['product_name']) ?></div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase mt-1">Units: <?= $row['quantity'] ?></div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="text-sm font-black text-slate-900">$<?= number_format($row['total_amount'], 2) ?></div>
                                        <div class="text-[10px] text-slate-300 font-mono mt-1 uppercase tracking-widest">Entry: #<?= $row['sale_id'] ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>