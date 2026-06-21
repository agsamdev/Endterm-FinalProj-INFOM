<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT username, password_bcrypt, role FROM users WHERE username=?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password_bcrypt"])) {
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["logged_in"] = true; // Added to match your index logic

            if ($user["role"] === "admin") {
                header("Location: admin.php");
            } else {
                header("Location: user.php");
            }
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalesApp | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 flex flex-col min-h-screen">

    <nav class="bg-white border-b shadow-sm">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
            <h1 class="font-bold text-lg">SalesApp</h1>
            <div class="space-x-6 text-sm font-semibold">
                <a href="index.php" class="hover:text-black">Home</a>
                <a href="register.php" class="hover:text-black">Register</a>
            </div>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center px-6 py-12">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md border border-gray-100">
            <h2 class="text-2xl font-black mb-6 text-center">Login</h2>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4 border border-red-100">
                    <?php echo htmlspecialchars($error); ?>
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

                <button type="submit" 
                    class="w-full bg-gray-900 text-white py-3 rounded-lg font-bold hover:bg-black transition mt-2">
                    Login
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Don't have an account? <a href="register.php" class="text-gray-900 font-bold hover:underline">Register</a>
            </p>
        </div>
    </main>

</body>
</html>