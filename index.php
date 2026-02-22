<?php
session_start();
require_once '../common/config.php';

if(!isset($_SESSION['delivery_id'])) {
    header('Location: login.php');
    exit;
}

$delivery_id = $_SESSION['delivery_id'];
$delivery_name = $_SESSION['delivery_name'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'get_orders') {
        $stmt = $conn->prepare("SELECT o.*, r.name as restaurant_name, r.address as restaurant_address, 
                                c.name as customer_name, c.phone as customer_phone, c.address as customer_address 
                                FROM orders o 
                                JOIN restaurants r ON o.restaurant_id = r.id 
                                JOIN customers c ON o.customer_id = c.id 
                                WHERE (o.status = 'Ready for Pickup' AND o.delivery_partner_id IS NULL) 
                                   OR o.delivery_partner_id = ? 
                                ORDER BY o.created_at DESC");
        $stmt->bind_param("i", $delivery_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($orders);
        exit;
    }
    
    if($action === 'accept_order') {
        $order_id = $_POST['order_id'] ?? 0;
        
        $stmt = $conn->prepare("UPDATE orders SET delivery_partner_id = ?, status = 'Picked Up' WHERE id = ? AND delivery_partner_id IS NULL");
        $stmt->bind_param("ii", $delivery_id, $order_id);
        
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }
    
    if($action === 'update_status') {
        $order_id = $_POST['order_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND delivery_partner_id = ?");
        $stmt->bind_param("sii", $status, $order_id, $delivery_id);
        
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }
    
    if($action === 'update_location') {
        $order_id = $_POST['order_id'] ?? 0;
        $location_link = $_POST['location_link'] ?? '';
        
        $stmt = $conn->prepare("UPDATE orders SET location_link = ? WHERE id = ? AND delivery_partner_id = ?");
        $stmt->bind_param("sii", $location_link, $order_id, $delivery_id);
        
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Orders - Delivery Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .order-card {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
        .premium-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .premium-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">
    <div class="premium-gradient text-white p-6 flex items-center justify-between sticky top-0 z-50 shadow-2xl">
        <div>
            <h1 class="text-3xl font-bold tracking-tight"><?php echo htmlspecialchars($delivery_name); ?></h1>
            <p class="text-sm opacity-90 flex items-center gap-2 mt-1">
                <i class="fas fa-motorcycle"></i>
                <span class="font-medium">Delivery Partner</span>
            </p>
        </div>
        <a href="profile.php" class="w-14 h-14 bg-white/20 backdrop-blur-lg rounded-2xl flex items-center justify-center hover:bg-white/30 transition-all shadow-lg">
            <i class="fas fa-user text-xl"></i>
        </a>
    </div>

    <div class="p-4 pb-24">
        <div class="flex gap-3 mb-6">
            <button onclick="filterOrders('available')" id="availableTab" class="flex-1 py-4 px-5 rounded-2xl font-bold transition-all shadow-lg premium-card text-purple-600 border-2 border-purple-200">
                <i class="fas fa-bell mr-2"></i>Available
            </button>
            <button onclick="filterOrders('active')" id="activeTab" class="flex-1 py-4 px-5 rounded-2xl font-bold transition-all bg-white/60 text-gray-500 border-2 border-transparent">
                <i class="fas fa-motorcycle mr-2"></i>Active
            </button>
        </div>

        <div id="ordersContainer" class="space-y-4">
            <div class="text-center py-16">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full shimmer"></div>
                <p class="text-gray-600 font-semibold">Loading orders...</p>
            </div>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-xl border-t border-gray-200/50 px-8 py-4 flex justify-around shadow-2xl">
        <a href="index.php" class="flex flex-col items-center transform scale-110">
            <div class="w-12 h-12 premium-gradient rounded-2xl flex items-center justify-center mb-1 shadow-lg">
                <i class="fas fa-motorcycle text-xl text-white"></i>
            </div>
            <span class="text-xs font-bold text-purple-600">Orders</span>
        </a>
        <a href="earnings.php" class="flex flex-col items-center text-gray-400 hover:text-gray-600 transition-colors">
            <div class="w-12 h-12 flex items-center justify-center mb-1">
                <i class="fas fa-rupee-sign text-2xl"></i>
            </div>
            <span class="text-xs font-medium">Earnings</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center text-gray-400 hover:text-gray-600 transition-colors">
            <div class="w-12 h-12 flex items-center justify-center mb-1">
                <i class="fas fa-user text-2xl"></i>
            </div>
            <span class="text-xs font-medium">Profile</span>
        </a>
    </div>

    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => e.preventDefault());
        document.addEventListener('touchmove', e => { if(e.scale !== 1) e.preventDefault(); }, {passive: false});

        let allOrders = [];
        let currentFilter = 'available';

        async function loadOrders() {
            const formData = new FormData();
            formData.append('action', 'get_orders');
            
            const response = await fetch('index.php', {
                method: 'POST',
                body: formData
            });
            allOrders = await response.json();
            console.log('[v0] Loaded orders:', allOrders);
            displayOrders();
        }

        function filterOrders(filter) {
            currentFilter = filter;
            
            document.getElementById('availableTab').className = filter === 'available' 
                ? 'flex-1 py-4 px-5 rounded-2xl font-bold transition-all shadow-lg premium-card text-purple-600 border-2 border-purple-200'
                : 'flex-1 py-4 px-5 rounded-2xl font-bold transition-all bg-white/60 text-gray-500 border-2 border-transparent';
            
            document.getElementById('activeTab').className = filter === 'active'
                ? 'flex-1 py-4 px-5 rounded-2xl font-bold transition-all shadow-lg premium-card text-purple-600 border-2 border-purple-200'
                : 'flex-1 py-4 px-5 rounded-2xl font-bold transition-all bg-white/60 text-gray-500 border-2 border-transparent';
            
            displayOrders();
        }

        function displayOrders() {
            const container = document.getElementById('ordersContainer');
            
            const filteredOrders = allOrders.filter(order => {
                if(currentFilter === 'available') {
                    return order.delivery_partner_id === null && order.status === 'Ready for Pickup';
                } else {
                    return order.delivery_partner_id !== null && order.status !== 'Delivered';
                }
            });
            
            console.log('[v0] Filtered orders:', filteredOrders);
            
            if(filteredOrders.length === 0) {
                const emptyMessage = currentFilter === 'available' 
                    ? '<div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-purple-100 to-blue-100 rounded-3xl flex items-center justify-center"><i class="fas fa-box-open text-5xl text-purple-300"></i></div><p class="text-gray-600 text-xl font-bold">No available orders</p><p class="text-gray-400 text-sm mt-2">New orders will appear here when ready</p>'
                    : '<div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-3xl flex items-center justify-center"><i class="fas fa-motorcycle text-5xl text-blue-300"></i></div><p class="text-gray-600 text-xl font-bold">No active deliveries</p><p class="text-gray-400 text-sm mt-2">Accept orders to start delivering</p>';
                
                container.innerHTML = `
                    <div class="text-center py-20">
                        ${emptyMessage}
                    </div>
                `;
                return;
            }
            
            container.innerHTML = filteredOrders.map(order => `
                <div class="order-card premium-card rounded-3xl shadow-xl p-6 hover:shadow-2xl transition-all">
                    <div class="flex justify-between items-start mb-5">
                        <div>
                            <p class="font-black text-3xl bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">#${order.id}</p>
                            <span class="inline-block px-4 py-2 rounded-full text-xs font-bold ${getStatusColor(order.status)} mt-2 shadow-md">
                                ${order.status}
                            </span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 mb-1 font-medium">Order Amount</p>
                            <p class="font-black text-3xl bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">₹${order.total_amount}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3 mb-5">
                        <div class="flex items-start gap-4 p-4 bg-gradient-to-r from-orange-50 to-red-50 rounded-2xl border border-orange-100">
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                <i class="fas fa-store text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-800 text-lg">${order.restaurant_name}</p>
                                <p class="text-sm text-gray-600 mt-1 leading-relaxed">${order.restaurant_address}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                <i class="fas fa-user text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-800 text-lg">${order.customer_name}</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-phone mr-2"></i>${order.customer_phone}
                                </p>
                                <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                                    <i class="fas fa-map-marker-alt mr-2"></i>${order.customer_address}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    ${getActionButtons(order)}
                </div>
            `).join('');
        }

        function getStatusColor(status) {
            const colors = {
                'Ready for Pickup': 'bg-gradient-to-r from-yellow-400 to-orange-400 text-white',
                'Picked Up': 'bg-gradient-to-r from-blue-400 to-indigo-500 text-white',
                'Out for Delivery': 'bg-gradient-to-r from-blue-500 to-blue-600 text-white',
                'Delivered': 'bg-gradient-to-r from-green-400 to-emerald-500 text-white'
            };
            return colors[status] || 'bg-gray-200 text-gray-800';
        }

        function getActionButtons(order) {
            if(order.delivery_partner_id === null && order.status === 'Ready for Pickup') {
                return `
                    <button onclick="acceptOrder(${order.id})" class="w-full premium-gradient text-white py-5 rounded-2xl font-black text-lg shadow-xl hover:shadow-2xl transition-all transform hover:scale-105">
                        <i class="fas fa-check-circle mr-2"></i>Accept Order
                    </button>
                `;
            }
            
            if(order.status === 'Picked Up' && order.delivery_partner_id !== null) {
                return `
                    <button onclick="updateStatus(${order.id}, 'Out for Delivery')" class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-5 rounded-2xl font-black text-lg shadow-xl hover:shadow-2xl transition-all">
                        <i class="fas fa-motorcycle mr-2"></i>Start Delivery
                    </button>
                `;
            }
            
            if(order.status === 'Out for Delivery') {
                return `
                    <div class="space-y-3">
                        <input type="text" id="location_${order.id}" placeholder="Paste Google Maps link" class="w-full px-5 py-4 border-2 border-purple-200 rounded-2xl text-sm focus:border-purple-400 focus:outline-none font-medium">
                        <button onclick="updateLocation(${order.id})" class="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white py-4 rounded-2xl font-bold shadow-xl hover:shadow-2xl transition-all">
                            <i class="fas fa-map-marker-alt mr-2"></i>Share Location
                        </button>
                        <button onclick="updateStatus(${order.id}, 'Delivered')" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-5 rounded-2xl font-black text-lg shadow-xl hover:shadow-2xl transition-all">
                            <i class="fas fa-check-circle mr-2"></i>Mark Delivered
                        </button>
                    </div>
                `;
            }
            return '';
        }

        async function acceptOrder(orderId) {
            const formData = new FormData();
            formData.append('action', 'accept_order');
            formData.append('order_id', orderId);
            
            const response = await fetch('index.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if(result.success) {
                loadOrders();
                filterOrders('active');
            }
        }

        async function updateStatus(orderId, status) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('order_id', orderId);
            formData.append('status', status);
            
            await fetch('index.php', {
                method: 'POST',
                body: formData
            });
            loadOrders();
        }

        async function updateLocation(orderId) {
            const locationLink = document.getElementById(`location_${orderId}`).value;
            if(!locationLink) {
                alert('Please enter location link');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'update_location');
            formData.append('order_id', orderId);
            formData.append('location_link', locationLink);
            
            await fetch('index.php', {
                method: 'POST',
                body: formData
            });
            alert('Location shared successfully!');
        }

        loadOrders();
        setInterval(loadOrders, 10000);
    </script>
</body>
</html>
