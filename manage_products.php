<?php
include 'db.php';

// ---------- Handle Add / Edit / Delete ----------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == "add") {
        $name = $_POST['name'];
        $url = $_POST['image_url'];
        $conn->query("INSERT INTO products (name, image_url) VALUES ('$name', '$url')");
    } elseif ($action == "edit") {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $url = $_POST['image_url'];
        $conn->query("UPDATE products SET name='$name', image_url='$url' WHERE id=$id");
    } elseif ($action == "delete") {
        $id = $_POST['id'];
        $conn->query("DELETE FROM products WHERE id=$id");
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ---------- Fetch Products ----------
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE CONCAT('%', ?, '%') ORDER BY id DESC");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
}
?>
<?php include 'navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KNS Vegetables - Manage Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: #f5f7fa;
      font-family: 'Poppins', sans-serif;
      padding-bottom: 80px;
    }

    .topbar {
      background: linear-gradient(135deg, #2ecc71, #27ae60);
      color: white;
      padding: 15px;
      text-align: center;
      border-radius: 0 0 25px 25px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }

    .topbar h4 {
      font-weight: 600;
      margin: 0;
    }

    .search-bar {
      margin-top: 15px;
      display: flex;
      justify-content: center;
    }

    .search-bar input {
      width: 90%;
      border-radius: 10px;
      border: 1px solid #ccc;
      padding: 8px 12px;
    }

    .product-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      margin: 10px 0;
      padding: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: transform 0.2s ease-in-out;
    }

    .product-card:hover {
      transform: scale(1.02);
    }

    .product-img {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      object-fit: cover;
      border: 2px solid #f0f0f0;
    }

    .product-info {
      flex: 1;
      margin-left: 12px;
    }

    .product-info h6 {
      font-size: 16px;
      font-weight: 600;
      margin: 0;
    }

    .action-buttons button {
      border: none;
      background: none;
      font-size: 18px;
      margin-left: 8px;
      color: #555;
    }

    .action-buttons button:hover {
      color: #27ae60;
    }

    .btn-add {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: linear-gradient(135deg, #27ae60, #2ecc71);
      color: white;
      border: none;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      font-size: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      transition: all 0.3s ease-in-out;
    }

    .btn-add:hover {
      transform: scale(1.1);
      box-shadow: 0 5px 12px rgba(0,0,0,0.25);
    }

    .modal-content {
      border-radius: 15px;
      padding: 15px;
    }

    .form-control {
      border-radius: 10px;
    }

    .btn-save {
      background: linear-gradient(135deg, #27ae60, #2ecc71);
      border: none;
      border-radius: 10px;
      color: white;
      font-weight: 500;
      width: 100%;
      padding: 10px;
    }

    .btn-save:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>
  <div class="search-bar">
    <form method="GET" class="d-flex w-100 justify-content-center">
      <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search product..." oninput="this.form.submit()">
    </form>
  </div>

  <div class="container mt-3">
    <?php if ($result->num_rows == 0) { ?>
      <p class="text-center text-muted mt-4">No products found</p>
    <?php } else { ?>
      <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="product-card">
          <div class="d-flex align-items-center">
            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="product-img" alt="">
            <div class="product-info">
              <h6><?php echo htmlspecialchars($row['name']); ?></h6>
            </div>
          </div>
          <div class="action-buttons">
            <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['image_url']); ?>')">
              <i class="fa-solid fa-pen"></i>
            </button>
            <form method="POST" class="d-inline">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <button onclick="return confirm('Delete this product?')">
                <i class="fa-solid fa-trash text-danger"></i>
              </button>
            </form>
          </div>
        </div>
      <?php } ?>
    <?php } ?>
  </div>

  <!-- Floating Add Button -->
  <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="fa-solid fa-plus"></i>
  </button>

  <!-- Add Product Modal -->
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <h5 class="text-center mb-3">Add Product</h5>
        <form method="POST">
          <input type="hidden" name="action" value="add">
          <div class="mb-3">
            <label>Product Name</label>
            <input type="text" class="form-control" name="name" placeholder="Enter product name" required>
          </div>
          <div class="mb-3">
            <label>Image URL</label>
            <input type="text" class="form-control" name="image_url" placeholder="Paste image URL" required>
          </div>
          <button class="btn-save">Save</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Product Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <h5 class="text-center mb-3">Edit Product</h5>
        <form method="POST">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" id="edit-id" name="id">
          <div class="mb-3">
            <label>Product Name</label>
            <input type="text" class="form-control" id="edit-name" name="name" required>
          </div>
          <div class="mb-3">
            <label>Image URL</label>
            <input type="text" class="form-control" id="edit-url" name="image_url" required>
          </div>
          <button class="btn-save">Update</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function openEditModal(id, name, url) {
      document.getElementById('edit-id').value = id;
      document.getElementById('edit-name').value = name;
      document.getElementById('edit-url').value = url;
      new bootstrap.Modal(document.getElementById('editModal')).show();
    }
  </script>

</body>
</html>
