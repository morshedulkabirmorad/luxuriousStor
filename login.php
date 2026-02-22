<?php
session_start();
require_once '../common/config.php';

if(isset($_SESSION['delivery_id'])) {
    header('Location: index.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $stmt = $conn->prepare("SELECT * FROM delivery_partners WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $partner = $result->fetch_assoc();
            if(password_verify($password, $partner['password'])) {
                if($partner['approved'] == 1) {
                    $_SESSION['delivery_id'] = $partner['id'];
                    $_SESSION['delivery_name'] = $partner['name'];
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Your account is pending approval']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Partner not found']);
        }
        exit;
    }
    
    if($action === 'signup') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
        $phone = $_POST['phone'] ?? '';
        $vehicle_info = $_POST['vehicle_info'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO delivery_partners (name, email, password, phone, vehicle_info, approved) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sssss", $name, $email, $password, $phone, $vehicle_info);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Registration successful! Wait for admin approval']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Partner Login - Hunger Food</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-[#FF6347] mb-2">Hunger Food</h1>
                <p class="text-gray-600">Delivery Partner Portal</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex mb-6 bg-gray-100 rounded-lg p-1">
                    <button onclick="showLogin()" id="loginTab" class="flex-1 py-2 rounded-lg font-medium transition-all bg-white text-[#FF6347] shadow">Login</button>
                    <button onclick="showSignup()" id="signupTab" class="flex-1 py-2 rounded-lg font-medium transition-all text-gray-600">Sign Up</button>
                </div>

                <form id="loginForm" onsubmit="handleLogin(event)">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FF6347] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FF6347] focus:border-transparent">
                        </div>
                        <button type="submit" class="w-full bg-[#FF6347] text-white py-3 rounded-lg font-medium hover:bg-[#ff4529] transition-colors">
                            Login
                        </button>
                    </div>
                </form>

                <form id="signupForm" onsubmit="handleSignup(event)" class="hidden">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="name" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FF6347] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FF6347] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FF6347] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input type="tel" name="phone" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FF6347] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Vehicle Info</label>
                            <input type="text" name="vehicle_info" placeholder="e.g., Bike - DL01AB1234" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FF6347] focus:border-transparent">
                        </div>
                        <button type="submit" class="w-full bg-[#FF6347] text-white py-3 rounded-lg font-medium hover:bg-[#ff4529] transition-colors">
                            Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => e.preventDefault());
        document.addEventListener('touchmove', e => { if(e.scale !== 1) e.preventDefault(); }, {passive: false});

        function showLogin() {
            document.getElementById('loginForm').classList.remove('hidden');
            document.getElementById('signupForm').classList.add('hidden');
            document.getElementById('loginTab').classList.add('bg-white', 'text-[#FF6347]', 'shadow');
            document.getElementById('loginTab').classList.remove('text-gray-600');
            document.getElementById('signupTab').classList.remove('bg-white', 'text-[#FF6347]', 'shadow');
            document.getElementById('signupTab').classList.add('text-gray-600');
        }

        function showSignup() {
            document.getElementById('loginForm').classList.add('hidden');
            document.getElementById('signupForm').classList.remove('hidden');
            document.getElementById('signupTab').classList.add('bg-white', 'text-[#FF6347]', 'shadow');
            document.getElementById('signupTab').classList.remove('text-gray-600');
            document.getElementById('loginTab').classList.remove('bg-white', 'text-[#FF6347]', 'shadow');
            document.getElementById('loginTab').classList.add('text-gray-600');
        }

        async function handleLogin(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'login');

            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if(data.success) {
                window.location.href = 'index.php';
            } else {
                alert(data.message);
            }
        }

        async function handleSignup(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'signup');

            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            alert(data.message);
            
            if(data.success) {
                showLogin();
                e.target.reset();
            }
        }
    </script>
</body>
</html>
