<?php
session_start();
require_once '../common/config.php';

if(!isset($_SESSION['delivery_id'])) {
    header('Location: login.php');
    exit;
}

$delivery_id = $_SESSION['delivery_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'get_profile') {
        $stmt = $conn->prepare("SELECT * FROM delivery_partners WHERE id = ?");
        $stmt->bind_param("i", $delivery_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_assoc());
        exit;
    }
    
    if($action === 'update_profile') {
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $vehicle_info = $_POST['vehicle_info'] ?? '';
        
        $stmt = $conn->prepare("UPDATE delivery_partners SET name = ?, phone = ?, vehicle_info = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $vehicle_info, $delivery_id);
        
        if($stmt->execute()) {
            $_SESSION['delivery_name'] = $name;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
    
    if($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Delivery Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
      @keyframes slideIn { from { opacity:0; transform: translateY(20px);} to { opacity:1; transform: translateY(0);} }
      .premium-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
      .premium-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1px solid rgba(0,0,0,0.05);
      }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">
    <div class="premium-gradient text-white p-6 flex items-center justify-between sticky top-0 z-50 shadow-2xl">
        <a href="index.php" class="w-14 h-14 bg-white/20 backdrop-blur-lg rounded-2xl flex items-center justify-center hover:bg-white/30 transition-all shadow-lg">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <h1 class="text-2xl font-bold tracking-tight">Profile</h1>
        <div class="w-14"></div>
    </div>

    <div class="premium-card rounded-3xl shadow-xl p-6 mb-4 border border-black/5">
        <form id="profileForm" onsubmit="updateProfile(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Full Name</label>
                    <input type="text" name="name" id="name" required class="w-full px-4 py-3 border-2 rounded-xl focus:ring-2 focus:ring-purple-300 focus:border-purple-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Email</label>
                    <input type="email" id="email" disabled class="w-full px-4 py-3 border-2 rounded-xl bg-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Phone</label>
                    <input type="tel" name="phone" id="phone" required class="w-full px-4 py-3 border-2 rounded-xl focus:ring-2 focus:ring-purple-300 focus:border-purple-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Vehicle Info</label>
                    <input type="text" name="vehicle_info" id="vehicle_info" required class="w-full px-4 py-3 border-2 rounded-xl focus:ring-2 focus:ring-purple-300 focus:border-purple-400">
                </div>
                <button type="submit" class="w-full premium-gradient text-white py-4 rounded-2xl font-bold shadow-lg hover:shadow-xl transition-all">
                    Update Profile
                </button>
            </div>
        </form>
    </div>

    <button onclick="logout()" class="w-full bg-gradient-to-r from-rose-500 to-red-500 text-white py-4 rounded-2xl font-bold shadow-lg flex items-center justify-center gap-2">
        <i class="fas fa-sign-out-alt"></i>
        Logout
    </button>

    <div class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-xl border-t border-gray-200/50 px-8 py-4 flex justify-around shadow-2xl">
        <a href="index.php" class="flex flex-col items-center text-gray-400 hover:text-gray-600 transition-colors">
            <div class="w-12 h-12 flex items-center justify-center mb-1">
                <i class="fas fa-motorcycle text-2xl"></i>
            </div>
            <span class="text-xs font-medium">Orders</span>
        </a>
        <a href="earnings.php" class="flex flex-col items-center text-gray-400 hover:text-gray-600 transition-colors">
            <div class="w-12 h-12 flex items-center justify-center mb-1">
                <i class="fas fa-rupee-sign text-2xl"></i>
            </div>
            <span class="text-xs font-medium">Earnings</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center transform scale-110">
            <div class="w-12 h-12 premium-gradient rounded-2xl flex items-center justify-center mb-1 shadow-lg">
                <i class="fas fa-user text-white text-xl"></i>
            </div>
            <span class="text-xs font-bold text-purple-600">Profile</span>
        </a>
    </div>

    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => e.preventDefault());

        async function loadProfile() {
            const formData = new FormData();
            formData.append('action', 'get_profile');
            
            const response = await fetch('profile.php', {
                method: 'POST',
                body: formData
            });
            const profile = await response.json();
            
            document.getElementById('name').value = profile.name;
            document.getElementById('email').value = profile.email;
            document.getElementById('phone').value = profile.phone;
            document.getElementById('vehicle_info').value = profile.vehicle_info;
        }

        async function updateProfile(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'update_profile');
            
            const response = await fetch('profile.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if(data.success) {
                alert('Profile updated successfully!');
            }
        }

        async function logout() {
            if(!confirm('Are you sure you want to logout?')) return;
            
            const formData = new FormData();
            formData.append('action', 'logout');
            
            await fetch('profile.php', {
                method: 'POST',
                body: formData
            });
            
            window.location.href = 'login.php';
        }

        loadProfile();
    </script>
</body>
</html>
