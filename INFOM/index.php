<?php
session_start();

// 1. ROLE-BASED REDIRECT LOGIC
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Check the user's role and redirect to the appropriate dashboard
    if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') {
        header("Location: admin.php");
        exit;
    } else {
        // Redirect standard users to the user portal
        header("Location: admin.php");
        exit;
    }
}

// 2. SECURITY HELPER
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalesApp | Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-[#F8FAFC] text-slate-900">

<nav class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-100">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <span class="text-sm font-black tracking-widest text-slate-800 uppercase">Sales<span class="text-indigo-600">App</span></span>
        </div>

        <div class="space-x-6 text-xs font-black uppercase tracking-widest">
            <a href="index.php" class="text-indigo-600">Home</a>
            <a href="login.php" class="text-slate-500 hover:text-indigo-600 transition">Login</a>
            <a href="register.php" class="text-slate-500 hover:text-indigo-600 transition">Register</a>
        </div>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-6 py-24 text-center">
    <p class="text-indigo-600 font-bold text-xs uppercase tracking-[0.3em] mb-4">Enterprise Resource Planning</p>
    <h2 class="text-6xl font-black text-slate-900 tracking-tighter mb-6 leading-tight">
        Modernize your <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500">Sales Workflow.</span>
    </h2>
    <p class="text-slate-500 text-lg mb-10 max-w-2xl mx-auto font-medium">
        A strategic terminal for managing customers, inventory, and real-time transactions with precision.
    </p>

    <div class="flex justify-center space-x-4">
        <a href="login.php" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 hover:scale-[1.03] active:scale-95 transition-all shadow-xl shadow-indigo-100">
            Get Started
        </a>
        <a href="register.php" class="bg-white text-slate-900 border border-slate-200 px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 transition-all">
            Create Account
        </a>
    </div>
</main>

</body>
</html>