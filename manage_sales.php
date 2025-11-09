<?php
include 'db.php';

// 🟠 Fetch Sale (for Edit)
if (isset($_GET['action']) && $_GET['action'] === 'get') {
    $id = $_GET['id'];
    $sql = "SELECT * FROM sales WHERE id=$id";
    $res = $conn->query($sql);
    echo json_encode($res->fetch_assoc());
    exit;
}

// 🟢 Update Sale & Adjust Stock
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = $_POST['id'];
    $newQty = floatval($_POST['quantity']);
    $rate = floatval($_POST['rate']);

    $sale = $conn->query("SELECT product_id, quantity FROM sales WHERE id=$id")->fetch_assoc();
    $product_id = $sale['product_id'];
    $oldQty = floatval($sale['quantity']);

    $difference = $oldQty - $newQty; 

    $conn->query("UPDATE sales SET quantity='$newQty', rate='$rate' WHERE id='$id'");

    if ($difference != 0) {
        $conn->query("UPDATE stocks SET quantity = quantity + $difference WHERE product_id=$product_id");
    }

    echo "✅ Sale updated & stock adjusted!";
    exit;
}

// 🔴 Delete Sale & Restore Stock
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];

    $sale = $conn->query("SELECT product_id, quantity FROM sales WHERE id=$id")->fetch_assoc();
    $product_id = $sale['product_id'];
    $qty = floatval($sale['quantity']);

    $conn->query("UPDATE stocks SET quantity = quantity + $qty WHERE product_id=$product_id");

    $conn->query("DELETE FROM sales WHERE id=$id");

    echo "✅ Sale deleted & stock restored!";
    exit;
}

// ---------- LOAD PAGE DATA ----------
$dateFilter = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
$search = isset($_GET['search']) ? $_GET['search'] : "";

$sql = "SELECT s.id, p.name AS product_name, s.customer_name, s.sale_type, s.quantity, s.rate, s.date
        FROM sales s
        JOIN products p ON s.product_id = p.id
        WHERE DATE(s.date) = '$dateFilter'
        AND (p.name LIKE '%$search%' OR s.customer_name LIKE '%$search%')
        ORDER BY s.id DESC";
$result = $conn->query($sql);
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KNS Vegetables - Manage Sales</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { background-color: #f8f9fa; }
.table-container {
    background: white; border-radius: 8px; padding: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
</style>
</head>

<body>

<div class="container mt-3 pb-5">

<h4 class="text-center mb-3">Manage Sales</h4>

<div class="d-flex gap-2 mb-3">
    <input type="date" id="dateFilter" value="<?php echo $dateFilter; ?>" class="form-control w-auto">
    <input type="text" id="searchBar" class="form-control" placeholder="Search product or customer..." value="<?php echo $search; ?>">
</div>

<div class="table-container">
<table class="table table-striped align-middle" id="salesTable">
<thead class="table-success">
<tr>
    <th>ID</th>
    <th>Date</th>
    <th>Product</th>
    <th>Customer</th>
    <th>Type</th>
    <th>Qty </th>
    <th>Rate (₹)</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<?php
$totalAmount = 0;
$totalQty = 0;
$wholesaleQty = 0;
$retailQty = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // Wholesale / Retail qty count
        if (strtolower($row['sale_type']) === 'wholesale') {
            $wholesaleQty += $row['quantity'];
        } else {
            $retailQty += $row['quantity'];
        }

        $amount = $row['quantity'] * $row['rate'];
        $totalAmount += $amount;
        $totalQty += $row['quantity'];

        echo "
        <tr>
            <td>{$row['id']}</td>
            <td>{$row['date']}</td>
            <td>{$row['product_name']}</td>
            <td>{$row['customer_name']}</td>
            <td>{$row['sale_type']}</td>
            <td>{$row['quantity']}</td>
            <td>{$row['rate']}</td>
            <td>
                <button class='btn btn-sm btn-link text-primary' onclick='editSale({$row['id']})'><i class=\"bi bi-pencil-square\"></i></button>
                <button class='btn btn-sm btn-link text-danger' onclick='deleteSale({$row['id']})'><i class=\"bi bi-trash\"></i></button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center text-muted'>No sales found</td></tr>";
}
?>
</tbody>
</table>
</div>

<!-- ✅ Totals Section -->
<div class="mt-3 fw-bold text-end">
    Wholesale Qty: <?php echo $wholesaleQty; ?> kg |
    Retail Qty: <?php echo $retailQty; ?> kg |
    Total: <?php echo $totalQty; ?> kg |
</div>

</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form id="editForm">
    <div class="modal-header">
        <h5 class="modal-title">Edit Sale</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="action" value="update">
        <input type="hidden" id="edit_id" name="id">
        <label>Quantity</label>
        <input type="number" step="0.01" id="edit_quantity" name="quantity" class="form-control mb-2" required>
        <label>Rate (₹)</label>
        <input type="number" step="0.01" id="edit_rate" name="rate" class="form-control" required>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-success w-100">Update</button>
    </div>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// 🔍 Search + Date Filter
document.getElementById('searchBar').addEventListener('input', applyFilter);
document.getElementById('dateFilter').addEventListener('change', applyFilter);

function applyFilter() {
  const date = document.getElementById('dateFilter').value;
  const search = document.getElementById('searchBar').value;
  window.location = `?date=${date}&search=${search}`;
}

// ✏️ Edit Sale
function editSale(id) {
  fetch(`?action=get&id=${id}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById('edit_id').value = data.id;
      document.getElementById('edit_quantity').value = data.quantity;
      document.getElementById('edit_rate').value = data.rate;
      new bootstrap.Modal(document.getElementById('editModal')).show();
    });
}

// 💾 Save Edit
document.getElementById('editForm').addEventListener('submit', e => {
  e.preventDefault();
  fetch('', { method: 'POST', body: new FormData(e.target) })
    .then(res => res.text())
    .then(msg => { alert(msg); location.reload(); });
});

// 🗑 Delete Sale
function deleteSale(id) {
  if (confirm("Delete this sale?")) {
    fetch(`?action=delete&id=${id}`)
      .then(res => res.text())
      .then(msg => { alert(msg); location.reload(); });
  }
}
</script>

</body>
</html>
