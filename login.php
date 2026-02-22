<?php
require_once 'common/config.php';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    exit();
}

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $stmt = $conn->prepare("INSERT INTO customers (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $phone, $address);
    
    if ($stmt->execute()) {
        $_SESSION['customer_id'] = $conn->insert_id;
        $_SESSION['customer_name'] = $name;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
    }
    exit();
}

// Check if already logged in
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Hunger Food</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        body {
            touch-action: pan-x pan-y;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF6347',
                        secondary: '#FFF5F3'
                    }
                }
            }
        }
        
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('gesturestart', e => e.preventDefault());
    </script>
</head>
<body class="bg-gradient-to-br from-primary to-orange-600 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-block bg-white rounded-full p-4 mb-4 shadow-lg">
                <i class="fas fa-utensils text-primary text-4xl"></i>
            </div>
            <h1 class="text-white text-4xl font-bold">Hunger Food</h1>
            <p class="text-white/90 mt-2">Delicious food, delivered fast</p>
        </div>

        <div id="loginForm" class="bg-white rounded-3xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Welcome Back</h2>
            <form id="loginFormElement" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-orange-600 transition transform active:scale-95 shadow-lg">
                    Login
                </button>
            </form>
            <p class="text-center mt-6 text-gray-600">
                Don't have an account? 
                <button onclick="toggleForms()" class="text-primary font-semibold">Sign Up</button>
            </p>
        </div>

        <div id="signupForm" class="bg-white rounded-3xl shadow-2xl p-8 hidden">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Create Account</h2>
            <form id="signupFormElement" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="name" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="tel" name="phone" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <div class="relative">
                        <i class="fas fa-map-marker-alt absolute left-4 top-4 text-gray-400"></i>
                        <textarea name="address" required rows="2" class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"></textarea>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" required class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-orange-600 transition transform active:scale-95 shadow-lg">
                    Sign Up
                </button>
            </form>
            <p class="text-center mt-6 text-gray-600">
                Already have an account? 
                <button onclick="toggleForms()" class="text-primary font-semibold">Login</button>
            </p>
        </div>
    </div>

    <script>
        function toggleForms() {
            document.getElementById('loginForm').classList.toggle('hidden');
            document.getElementById('signupForm').classList.toggle('hidden');
        }

        // Login form submission
        document.getElementById('loginFormElement').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'login');

            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                window.location.href = 'index.php';
            } else {
                alert(result.message);
            }
        });

        // Signup form submission
        document.getElementById('signupFormElement').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'signup');

            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                window.location.href = 'index.php';
            } else {
                alert(result.message);
            }
        });
    </script>
</body>
</html>
