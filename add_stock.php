<?php
include 'db.php';

// ---------- Handle Add / Edit / Delete ----------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == "add") {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $rate = !empty($_POST['rate']) ? $_POST['rate'] : 'NULL';
        $conn->query("INSERT INTO stocks (product_id, quantity, rate) VALUES ($product_id, $quantity, $rate)");
    } elseif ($action == "edit") {
        $id = $_POST['id'];
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $rate = !empty($_POST['rate']) ? $_POST['rate'] : 'NULL';
        $conn->query("UPDATE stocks SET product_id=$product_id, quantity=$quantity, rate=$rate WHERE id=$id");
    } elseif ($action == "delete") {
        $id = $_POST['id'];
        $conn->query("DELETE FROM stocks WHERE id=$id");
    }
    exit();
}

// ---------- AJAX: Fetch Stocks ----------
if (isset($_GET['ajax'])) {
    $search = $_GET['search'] ?? '';
    $date = $_GET['date'] ?? '';
    $query = "
        SELECT s.*, p.name 
        FROM stocks s 
        JOIN products p ON s.product_id = p.id 
        WHERE p.name LIKE CONCAT('%', ?, '%')
    ";
    if ($date) $query .= " AND DATE(s.date_added) = ?";
    $query .= " ORDER BY s.id DESC";

    $stmt = $conn->prepare($query);
    if ($date) $stmt->bind_param("ss", $search, $date);
    else $stmt->bind_param("s", $search);
    $stmt->execute();
    $stocks = $stmt->get_result();

    $rows = [];
    while ($row = $stocks->fetch_assoc()) $rows[] = $row;
    header('Content-Type: application/json');
    echo json_encode($rows);
    exit();
}

// ---------- Fetch Products for Dropdown ----------
$products = $conn->query("SELECT * FROM products ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KNS Vegetables - Manage Stocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#f8fafc; font-family:'Poppins',sans-serif; padding-bottom:80px; }
.topbar { background:linear-gradient(135deg,#2ecc71,#27ae60); color:white; padding:15px; text-align:center;
          border-radius:0 0 25px 25px; box-shadow:0 3px 6px rgba(0,0,0,0.1);}
.topbar h4{font-weight:600;margin:0;}
.filter-bar{display:flex;justify-content:space-between;align-items:center;margin:15px;}
.filter-bar input{border-radius:10px;border:1px solid #ccc;padding:7px 10px;width:48%;}
.stock-card{background:white;border-radius:15px;box-shadow:0 2px 6px rgba(0,0,0,0.08);
            margin:8px 0;padding:12px 15px;display:flex;justify-content:space-between;align-items:center;}
.stock-info h6{font-weight:600;margin:0;}
.action-buttons button{border:none;background:none;font-size:18px;margin-left:8px;color:#555;}
.action-buttons button:hover{color:#27ae60;}
.btn-add{position:fixed;bottom:20px;right:20px;background:linear-gradient(135deg,#27ae60,#2ecc71);
         color:white;border:none;border-radius:50%;width:60px;height:60px;font-size:28px;
         display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(0,0,0,0.2);}
.modal-content{border-radius:15px;padding:15px;}
.btn-save{background:linear-gradient(135deg,#27ae60,#2ecc71);border:none;border-radius:10px;
          color:white;font-weight:500;width:100%;padding:10px;}
</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="filter-bar container">
  <input type="text" id="search" placeholder="🔍 Search product...">
  <input type="date" id="filterDate" value="<?php echo date('Y-m-d'); ?>">
</div>

<div class="container" id="stockList">
  <p class="text-center text-muted mt-4">Loading...</p>
</div>

<!-- Floating Add Button -->
<button class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
  <i class="fa-solid fa-plus"></i>
</button>

<!-- Add Stock Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <h5 class="text-center mb-3">Add Stock</h5>
      <form id="addForm">
        <input type="hidden" name="action" value="add">
        <div class="mb-3">
          <label>Product</label>
          <select name="product_id" class="form-control" required>
            <option value="">Select Product</option>
            <?php while ($p = $products->fetch_assoc()) { ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="mb-3"><label>Quantity</label>
          <input type="number" step="0.01" name="quantity" class="form-control" required>
        </div>
        <div class="mb-3"><label>Rate (optional)</label>
          <input type="number" step="0.01" name="rate" class="form-control">
        </div>
        <button class="btn-save">Save</button>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <h5 class="text-center mb-3">Edit Stock</h5>
      <form id="editForm">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit-id">
        <div class="mb-3">
          <label>Product</label>
          <select name="product_id" id="edit-product" class="form-control" required></select>
        </div>
        <div class="mb-3"><label>Quantity</label>
          <input type="number" step="0.01" name="quantity" id="edit-quantity" class="form-control" required>
        </div>
        <div class="mb-3"><label>Rate (optional)</label>
          <input type="number" step="0.01" name="rate" id="edit-rate" class="form-control">
        </div>
        <button class="btn-save">Update</button>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ---------- Load Stocks Realtime ----------
async function loadStocks() {
  const search = document.getElementById('search').value;
  const date = document.getElementById('filterDate').value;
  const res = await fetch(`add_stock.php?ajax=1&search=${search}&date=${date}`);
  const data = await res.json();
  const list = document.getElementById('stockList');
  list.innerHTML = '';
  if (data.length === 0) {
    list.innerHTML = `<p class='text-center text-muted mt-4'>No records found</p>`;
    return;
  }
  data.forEach(s => {
    list.innerHTML += `
      <div class="stock-card">
        <div class="stock-info">
          <h6>${s.name}</h6>
          <small>Qty: ${s.quantity} ${s.rate ? ' | Rate: ₹'+s.rate : ''}</small>
        </div>
        <div class="action-buttons">
          <button onclick="openEdit(${s.id}, ${s.product_id}, '${s.quantity}', '${s.rate||''}')">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button onclick="deleteStock(${s.id})">
            <i class="fa-solid fa-trash text-danger"></i>
          </button>
        </div>
      </div>`;
  });
}
loadStocks();
document.getElementById('search').addEventListener('input', loadStocks);
document.getElementById('filterDate').addEventListener('change', loadStocks);

// ---------- Add ----------
document.getElementById('addForm').addEventListener('submit', async e=>{
  e.preventDefault();
  const form = new FormData(e.target);
  await fetch('add_stock.php', {method:'POST', body:form});
  e.target.reset();
  bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
  loadStocks();
});

// ---------- Delete ----------
async function deleteStock(id){
  if(!confirm('Delete this stock entry?')) return;
  const form = new FormData();
  form.append('action','delete'); form.append('id',id);
  await fetch('add_stock.php',{method:'POST',body:form});
  loadStocks();
}

// ---------- Edit ----------
async function openEdit(id, product_id, qty, rate){
  document.getElementById('edit-id').value = id;
  document.getElementById('edit-quantity').value = qty;
  document.getElementById('edit-rate').value = rate;
  const res = await fetch('add_stock.php'); // reload product list
  const html = await res.text();
  const parser = new DOMParser();
  const products = parser.parseFromString(html, 'text/html').querySelectorAll('select[name="product_id"] option');
  const select = document.getElementById('edit-product');
  select.innerHTML = '';
  products.forEach(opt=>{
    const clone = opt.cloneNode(true);
    if (clone.value == product_id) clone.selected = true;
    select.appendChild(clone);
  });
  new bootstrap.Modal(document.getElementById('editModal')).show();
}

document.getElementById('editForm').addEventListener('submit', async e=>{
  e.preventDefault();
  const form = new FormData(e.target);
  await fetch('add_stock.php',{method:'POST',body:form});
  bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
  loadStocks();
});
</script>
</body>
</html>
