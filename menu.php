<?php
session_start();
require_once '../common/config.php';

if(!isset($_SESSION['restaurant_id'])) {
    header('Location: login.php');
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'get_items') {
        $stmt = $conn->prepare("SELECT * FROM menu_items WHERE restaurant_id = ? ORDER BY id DESC");
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($items);
        exit;
    }
    
    if($action === 'add_item') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $image_url = $_POST['image_url'] ?? '/placeholder.svg?height=200&width=200';
        
        $stmt = $conn->prepare("INSERT INTO menu_items (restaurant_id, name, description, price, image_url, is_available) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("issds", $restaurant_id, $name, $description, $price, $image_url);
        
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }
    
    if($action === 'toggle_availability') {
        $item_id = $_POST['item_id'] ?? 0;
        $is_available = $_POST['is_available'] ?? 0;
        
        $stmt = $conn->prepare("UPDATE menu_items SET is_available = ? WHERE id = ? AND restaurant_id = ?");
        $stmt->bind_param("iii", $is_available, $item_id, $restaurant_id);
        
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }
    
    if($action === 'delete_item') {
        $item_id = $_POST['item_id'] ?? 0;
        
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ? AND restaurant_id = ?");
        $stmt->bind_param("ii", $item_id, $restaurant_id);
        
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
    <title>Menu Management - Restaurant Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="bg-[#FF6347] text-white p-4 flex items-center justify-between sticky top-0 z-50 shadow-lg">
        <a href="index.php" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl font-bold">Menu Management</h1>
        <button onclick="showAddModal()" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <div class="p-4 pb-24">
        <div id="menuContainer" class="space-y-4"></div>
    </div>

    <div id="addModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Add Menu Item</h2>
            <form onsubmit="addItem(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Item Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Description</label>
                        <textarea name="description" required class="w-full px-4 py-2 border rounded-lg" rows="3"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Price (₹)</label>
                        <input type="number" name="price" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Product Image URL</label>
                        <input type="url" name="image_url" id="imageUrl" placeholder="https://example.com/image.jpg" class="w-full px-4 py-2 border rounded-lg" oninput="updateImagePreview()">
                        <p class="text-xs text-gray-500 mt-1">Add a product image URL (recommended size: 400x400px)</p>
                    </div>
                    <div id="imagePreviewContainer" class="hidden">
                        <label class="block text-sm font-medium mb-2">Image Preview</label>
                        <img id="imagePreview" src="/placeholder.svg" alt="Preview" class="w-full h-48 object-cover rounded-lg border">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-[#FF6347] text-white py-2 rounded-lg font-medium">Add Item</button>
                        <button type="button" onclick="hideAddModal()" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg font-medium">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3 flex justify-around">
        <a href="index.php" class="flex flex-col items-center text-gray-400">
            <i class="fas fa-receipt text-xl mb-1"></i>
            <span class="text-xs">Orders</span>
        </a>
        <a href="menu.php" class="flex flex-col items-center text-[#FF6347]">
            <i class="fas fa-utensils text-xl mb-1"></i>
            <span class="text-xs font-medium">Menu</span>
        </a>
        <a href="reports.php" class="flex flex-col items-center text-gray-400">
            <i class="fas fa-chart-line text-xl mb-1"></i>
            <span class="text-xs">Reports</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center text-gray-400">
            <i class="fas fa-user text-xl mb-1"></i>
            <span class="text-xs">Profile</span>
        </a>
    </div>

    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => e.preventDefault());

        function updateImagePreview() {
            const imageUrl = document.getElementById('imageUrl').value;
            const preview = document.getElementById('imagePreview');
            const container = document.getElementById('imagePreviewContainer');
            
            if (imageUrl) {
                preview.src = imageUrl;
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }

        async function loadMenu() {
            const formData = new FormData();
            formData.append('action', 'get_items');
            
            const response = await fetch('menu.php', {
                method: 'POST',
                body: formData
            });
            const items = await response.json();
            
            const container = document.getElementById('menuContainer');
            
            if(items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-utensils text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">No menu items yet</p>
                        <button onclick="showAddModal()" class="mt-4 bg-[#FF6347] text-white px-6 py-2 rounded-lg">
                            Add First Item
                        </button>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = items.map(item => `
                <div class="bg-white rounded-xl shadow-sm p-4 flex gap-4">
                    <img src="${item.image_url}" alt="${item.name}" class="w-20 h-20 rounded-lg object-cover">
                    <div class="flex-1">
                        <h3 class="font-bold">${item.name}</h3>
                        <p class="text-sm text-gray-600 mb-2">${item.description}</p>
                        <p class="font-bold text-[#FF6347]">₹${item.price}</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <button onclick="toggleAvailability(${item.id}, ${item.is_available ? 0 : 1})" 
                                class="px-3 py-1 rounded-lg text-xs font-medium ${item.is_available ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                            ${item.is_available ? 'Available' : 'Unavailable'}
                        </button>
                        <button onclick="deleteItem(${item.id})" class="px-3 py-1 bg-red-100 text-red-800 rounded-lg text-xs font-medium">
                            Delete
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function showAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function hideAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('imagePreviewContainer').classList.add('hidden');
        }

        async function addItem(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'add_item');
            
            const response = await fetch('menu.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if(data.success) {
                hideAddModal();
                e.target.reset();
                loadMenu();
            }
        }

        async function toggleAvailability(itemId, isAvailable) {
            const formData = new FormData();
            formData.append('action', 'toggle_availability');
            formData.append('item_id', itemId);
            formData.append('is_available', isAvailable);
            
            await fetch('menu.php', {
                method: 'POST',
                body: formData
            });
            loadMenu();
        }

        async function deleteItem(itemId) {
            if(!confirm('Delete this item?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete_item');
            formData.append('item_id', itemId);
            
            await fetch('menu.php', {
                method: 'POST',
                body: formData
            });
            loadMenu();
        }

        loadMenu();
    </script>
</body>
</html>
