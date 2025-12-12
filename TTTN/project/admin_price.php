<?php
include 'config.php'; // tạo $conn (mysqli)
session_start();

$admin_id = $_SESSION['admin_id'] ?? null; // lấy id admin từ session

if (!$admin_id)
{
    header('Location: Login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name      = trim($_POST['name'] ?? '');
        $value     = trim($_POST['value'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive  = isset($_POST['is_active']) ? 1 : 0;

        if ($name !== '' && $value !== '') {
            $stmt = $conn->prepare(
                "INSERT INTO price (name, value, sort_order, is_active)
                 VALUES (?, ?, ?, ?)"
            );
            // ssii = string, string, int, int
            $stmt->bind_param('ssii', $name, $value, $sortOrder, $isActive);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: admin_price.php');
        exit;
    }   
     if ($action === 'update') {
        $id        = (int)($_POST['id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $value     = trim($_POST['value'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive  = isset($_POST['is_active']) ? 1 : 0;

        if ($id > 0 && $name !== '' && $value !== '') {
            $stmt = $conn->prepare(
                "UPDATE price
                 SET name = ?, value = ?, sort_order = ?, is_active = ?
                 WHERE id = ?"
            );
            // ssiii = string, string, int, int, int
            $stmt->bind_param('ssiii', $name, $value, $sortOrder, $isActive, $id);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: admin_price.php');
        exit;
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM price WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: admin_price.php');
        exit;
    }
    // ====== LOAD CATEGORIES ======
}
$result = $conn->query("SELECT * FROM price ORDER BY sort_order ASC, name ASC");
$price = [];
if ($result) {
    $price = $result->fetch_all(MYSQLI_ASSOC); // mysqli_result::fetch_all
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Cấu hình danh mục giá</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/admin_style.css">

</head>
<body class="bg-gray-100 min-h-screen">
<?php include 'admin_header.php'; ?>
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6">Cấu hình danh mục giá</h1>

    <!-- Form thêm danh mục -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4">Thêm danh mục mới</h2>
        <form method="post" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên hiển thị</label>
                    <input type="text" name="name" required
                           class="w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ví dụ: Gaming, Văn phòng">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giá trị (value)</label>
                    <input type="text" name="value" required
                           class="w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ví dụ: Gaming">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự</label>
                    <input type="number" name="sort_order" value="0"
                           class="w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_active" id="create_is_active"
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                <label for="create_is_active" class="text-sm text-gray-700">Kích hoạt</label>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 text-sm">
                Thêm danh mục
            </button>
        </form>
    </div>

    <!-- Danh sách danh mục -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Danh sách danh mục</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="border-b">
                    <th class="px-3 py-2 text-left text-gray-500">ID</th>
                    <th class="px-3 py-2 text-left text-gray-500">Tên hiển thị</th>
                    <th class="px-3 py-2 text-left text-gray-500">Value</th>
                    <th class="px-3 py-2 text-left text-gray-500">Thứ tự</th>
                    <th class="px-3 py-2 text-left text-gray-500">Kích hoạt</th>
                    <th class="px-3 py-2 text-right text-gray-500">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($price as $cat): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo (int)$cat['id']; ?>">
                            <td class="px-3 py-2 align-middle">
                                <?php echo (int)$cat['id']; ?>
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>"
                                       class="w-full rounded border-gray-200 text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="value" value="<?php echo htmlspecialchars($cat['value']); ?>"
                                       class="w-full rounded border-gray-200 text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="sort_order" value="<?php echo (int)$cat['sort_order']; ?>"
                                       class="w-20 rounded border-gray-200 text-sm">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input type="checkbox" name="is_active"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    <?php echo $cat['is_active'] ? 'checked' : ''; ?>>
                            </td>
                            <td class="px-3 py-2 text-right space-x-2">
                                <button type="submit" name="action" value="update"
                                        class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700">
                                    Lưu
                                </button>
                        </form>
                        <form method="post" class="inline"
                              onsubmit="return confirm('Xóa danh mục này?');">
                            <input type="hidden" name="id" value="<?php echo (int)$cat['id']; ?>">
                            <button type="submit" name="action" value="delete"
                                    class="px-3 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                Xóa
                            </button>
                        </form>
                            </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($price)): ?>
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-gray-400">
                            Chưa có danh mục nào.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>