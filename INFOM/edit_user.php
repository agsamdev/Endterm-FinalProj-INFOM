<?php
session_start();
require 'db.php';

// 1. SECURITY HELPER
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// 2. Auth Check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';

// 3. FETCH USER DATA
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$user_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: users.php");
    exit;
}

// 4. HANDLE UPDATE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_role = $_POST['role'];
    $new_password = $_POST['password'];

    if (empty($new_username)) {
        $error = "Username cannot be empty.";
    } else {
        if (!empty($new_password)) {
            // Update with password change
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("sssi", $new_username, $new_role, $hashed_password, $user_id);
        } else {
            // Update without touching password
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $new_username, $new_role, $user_id);
        }

        if ($update_stmt->execute()) {
            header("Location: users.php?success=User updated successfully");
            exit;
        } else {
            $error = "Update failed. Username might already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | SalesApp</title>
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
                <a href="users.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-indigo-600 text-white shadow-indigo-100 shadow-md transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-9-3.375M15 6.75a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-sm font-bold">Back to Users</span>
                </a>
            </nav>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-10 min-h-screen">
        <header class="mb-12">
            <p class="text-indigo-600 font-bold text-xs uppercase tracking-widest mb-1">Administration</p>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Edit Profile</h1>
        </header>

        <div class="max-w-2xl bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-10">
            <?php if($error): ?>
                <div class="mb-6 p-4 bg-rose-50 text-rose-700 border border-rose-100 rounded-2xl text-xs font-bold uppercase tracking-widest">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Username</label>
                    <input type="text" name="username" value="<?= h($user['username']) ?>" required
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-bold text-slate-700">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Access Role</label>
                    <select name="role" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-bold text-slate-700">
                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Standard User</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-50">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="••••••••"
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-bold text-slate-700">
                </div>

                <div class="flex items-center space-x-4 pt-6">
                    <button type="submit" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                        Update User
                    </button>
                    <a href="users.php" class="text-slate-400 font-bold text-xs uppercase tracking-widest hover:text-slate-600 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>