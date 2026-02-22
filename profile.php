<?php
require_once 'common/config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customerId = $_SESSION['customer_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $phone, $address, $customerId);
    
    if ($stmt->execute()) {
        $_SESSION['customer_name'] = $name;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get customer details
$customer = $conn->query("SELECT * FROM customers WHERE id = $customerId")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profile - Hunger Food</title>
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
<body class="bg-gray-50 pb-20">
    <header class="bg-gradient-to-br from-primary to-orange-600 text-white p-6">
        <div class="max-w-7xl mx-auto">
            <a href="index.php" class="text-white mb-4 inline-block">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-primary font-bold text-3xl">
                    <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                </div>
                <div>
                    <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($customer['name']); ?></h1>
                    <p class="text-white/90"><?php echo htmlspecialchars($customer['email']); ?></p>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="bg-white rounded-2xl shadow-md p-6 mb-4">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Edit Profile</h2>
            <form id="profileForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea name="address" rows="3" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-orange-600 transition transform active:scale-95">
                    Update Profile
                </button>
            </form>
        </div>

        <button onclick="logout()" class="w-full bg-red-500 text-white py-3 rounded-xl font-semibold hover:bg-red-600 transition transform active:scale-95">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </main>

    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-around">
            <a href="index.php" class="flex flex-col items-center text-gray-400 hover:text-primary transition">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs">Home</span>
            </a>
            <a href="cart.php" class="flex flex-col items-center text-gray-400 hover:text-primary transition">
                <i class="fas fa-shopping-cart text-xl mb-1"></i>
                <span class="text-xs">Cart</span>
            </a>
            <a href="my_orders.php" class="flex flex-col items-center text-gray-400 hover:text-primary transition">
                <i class="fas fa-receipt text-xl mb-1"></i>
                <span class="text-xs">Orders</span>
            </a>
            <a href="profile.php" class="flex flex-col items-center text-primary">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs font-semibold">Profile</span>
            </a>
        </div>
    </nav>

    <script>
        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'update_profile');

            const response = await fetch('profile.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                alert('Profile updated successfully!');
                location.reload();
            } else {
                alert(result.message);
            }
        });

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'profile.php?logout=1';
            }
        }
    </script>
</body>
</html>
