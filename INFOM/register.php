<?php
include 'db.php';

$message = '';
$isError = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    $bcryptHash = password_hash($password, PASSWORD_BCRYPT);
    $sha256Hash = hash("sha256", $password);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, password_bcrypt, password_sha256, role) 
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $username, $bcryptHash, $sha256Hash, $role);
    
    if ($stmt->execute()) {
        $message = "Registration successful! You can now login.";
    } else {
        $isError = true;
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalesApp | Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 flex flex-col min-h-screen">

    <nav class="bg-white border-b shadow-sm">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
            <h1 class="font-bold text-lg">SalesApp</h1>
            <div class="space-x-6 text-sm font-semibold">
                <a href="index.php" class="hover:text-black">Home</a>
                <a href="login.php" class="hover:text-black text-gray-900">Login</a>
            </div>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center px-6 py-12">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md border border-gray-100">
            <h2 class="text-2xl font-black mb-6 text-center">Create Account</h2>

            <?php if ($message): ?>
                <div class="<?php echo $isError ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-600 border-green-100'; ?> p-3 rounded-lg text-sm mb-4 border">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Username</label>
                    <input type="text" name="username" required 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-200 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Password</label>
                    <input type="password" name="password" required 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-200 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Role</label>
                    <select name="role" 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-200 bg-white outline-none transition">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit" 
                    class="w-full bg-gray-900 text-white py-3 rounded-lg font-bold hover:bg-black transition mt-2">
                    Register
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Already have an account? <a href="login.php" class="text-gray-900 font-bold hover:underline">Login</a>
            </p>
        </div>
    </main>

</body>
</html>