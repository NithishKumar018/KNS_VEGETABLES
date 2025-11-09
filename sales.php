<?php
include 'db.php';

$today = date("Y-m-d");

$sql = "SELECT p.id, p.name, p.image_url, s.quantity, s.rate 
        FROM products p
        JOIN stocks s ON p.id = s.product_id
        WHERE DATE(s.date_added) = CURDATE()";
$result = $conn->query($sql);
?>
<?php include 'navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KNS Vegetables - Sales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body { background-color: #f8f9fa; padding: 0px; }
    .product-card {
      background: #fff;
      border-radius: 12px;
      padding: 10px;
      text-align: center;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }
    .product-card:hover { transform: scale(1.03); }
    .product-img {
      width: 100%;
      height: 100px;
      border-radius: 10px;
      object-fit: cover;
    }
    .product-name { font-weight: 600; margin-top: 8px; font-size: 16px; }
    .product-qty { font-size: 14px; color: #555; }
    .btn-sale {
      background-color: #198754;
      border: none;
      color: white;
      width: 100%;
      margin-top: 8px;
      border-radius: 8px;
    }
    .btn-sale:hover { background-color: #157347; }

    /* FORCE uppercase visual also */
    .text-uppercase { text-transform: uppercase; }
  </style>
</head>
<body>

<div class="container">
  <h5 class="text-center mb-3">Today's Available Products</h5>
  <div class="row g-2">

    <?php
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        echo "
        <div class='col-6 col-md-4'>
          <div class='product-card'>
            <img src='{$row['image_url']}' class='product-img'>
            <div class='product-name'>{$row['name']}</div>
            <div class='product-qty'>Available: <span id='avail_{$row['id']}'>{$row['quantity']}</span> kg</div>
            <button class='btn-sale btn btn-sm' 
                onclick='openSaleModal({$row['id']}, \"{$row['name']}\", {$row['rate']}, {$row['quantity']})'>
                Sell
            </button>
          </div>
        </div>";
      }
    } else {
      echo "<p class='text-center text-muted'>No stock available for today</p>";
    }
    ?>

  </div>
</div>


<!-- Sale Modal -->
<div class="modal fade" id="saleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="saleForm">

        <div class="modal-header">
          <h5 class="modal-title">Sell Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <input type="hidden" id="product_id" name="product_id">

          <div class="mb-2">
            <label class="form-label">Product</label>
            <input type="text" id="product_name" class="form-control" readonly>
          </div>

          <div class="mb-2">
            <label class="form-label">Customer Name</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control text-uppercase" required>
          </div>

          <div class="mb-2">
            <label class="form-label">Sale Type</label>
            <select name="sale_type" id="sale_type" class="form-select" required>
              <option value="">Select</option>
              <option value="Wholesale">Wholesale</option>
              <option value="Retail">Retail</option>
            </select>
          </div>

          <div class="mb-2">
            <label class="form-label">Quantity (kg)</label>
            <input type="number" name="quantity" id="quantity" class="form-control" required>
            <small class="text-muted">Available: <span id="available_qty_text"></span> kg</small>
          </div>

          <div class="mb-2">
            <label class="form-label">Rate (₹)</label>
            <input type="number" step="0.01" id="rate" name="rate" class="form-control" required>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Save Sale</button>
        </div>

      </form>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
let availableQty = 0;

function openSaleModal(id, name, rate, quantity) {
  document.getElementById('product_id').value = id;
  document.getElementById('product_name').value = name;
  document.getElementById('rate').value = rate;
  document.getElementById('available_qty_text').innerText = quantity;
  availableQty = quantity;

  const qtyInput = document.getElementById('quantity');
  qtyInput.value = "";
  qtyInput.removeAttribute('max');

  const saleType = document.getElementById('sale_type');
  saleType.addEventListener('change', function() {
    if (this.value === "Wholesale") {
      qtyInput.addEventListener('input', enforceLimit);
    } else {
      qtyInput.removeEventListener('input', enforceLimit);
    }
  });

  new bootstrap.Modal(document.getElementById('saleModal')).show();
}

function enforceLimit() {
  if (parseFloat(this.value) > availableQty) {
    alert("Cannot exceed available quantity (" + availableQty + " kg) for Wholesale");
    this.value = availableQty;
  }
}

// ✅ convert customer name to uppercase WHILE typing
document.getElementById("customer_name").addEventListener("input", function () {
    this.value = this.value.toUpperCase();
});

document.getElementById('saleForm').addEventListener('submit', function(e) {
  e.preventDefault();
  fetch('save_sale.php', {
    method: 'POST',
    body: new FormData(this)
  })
  .then(res => res.text())
  .then(data => {
    alert(data);
    location.reload();
  });
});
</script>

</body>
</html>
