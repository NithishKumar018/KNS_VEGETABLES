<?php
include "db.php";
include "navbar.php";
date_default_timezone_set("Asia/Kolkata");
$today = date("Y-m-d");

/************** ✅ TODAY STOCK (OPENING, FIXED) **************/
$sql_today_stock = "
SELECT p.id, p.name, SUM(s.quantity) AS qty
FROM stocks s
JOIN products p ON p.id = s.product_id
WHERE DATE(s.date_added)='$today'
GROUP BY p.id";
$res_today = $conn->query($sql_today_stock);

$today_stock_total = 0;
$today_stock_list = [];
while($r = $res_today->fetch_assoc()){
    $today_stock_total += $r['qty'];
    $today_stock_list[$r['name']] = $r['qty'];
}

/************** ✅ TODAY WHOLESALE SALES **************/
$sql_sold = "
SELECT p.id, p.name, SUM(s.quantity) AS qty
FROM sales s
JOIN products p ON p.id = s.product_id
WHERE DATE(s.date)='$today' AND s.sale_type='wholesale'
GROUP BY p.id";
$res_sold = $conn->query($sql_sold);

$today_sold_total = 0;
$today_sold_list = [];
while($r = $res_sold->fetch_assoc()){
    $today_sold_total += $r['qty'];
    $today_sold_list[$r['name']] = $r['qty'];
}

/************** ✅ AVAILABLE STOCK TODAY ONLY **************/
$today_available = [];
$low_stock_today = [];

foreach($today_stock_list as $name => $qty){
    $sold = $today_sold_list[$name] ?? 0;
    $available = $qty - $sold;
    
    $today_available[$name] = $available;

    if($available < 5){
        $low_stock_today[$name] = $available;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.card-box{padding:18px;border-radius:15px;background:white;box-shadow:0 3px 8px rgba(0,0,0,.1);cursor:pointer;text-align:center;}
.card-box:hover{background:#e9f7ff;}
.value{font-size:26px;font-weight:700;}
</style>
</head>

<body class="bg-light">
<div class="container py-4">

<h3 class="fw-bold text-center mb-4">📊 Daily Stock Dashboard</h3>

<!-- ✅ Cards Section -->
<div class="row mb-4 g-3">
<div class="col-4">
<div class="card-box" data-bs-toggle="modal" data-bs-target="#todayStockModal">
<div>Today's Stock</div>
<div class="value text-success"><?= $today_stock_total ?> KG</div>
</div>
</div>

<div class="col-4">
<div class="card-box" data-bs-toggle="modal" data-bs-target="#todaySoldModal">
<div>Today's Sale</div>
<div class="value text-danger"><?= $today_sold_total ?> KG</div>
</div>
</div>

<div class="col-4">
<div class="card-box" data-bs-toggle="modal" data-bs-target="#lowStockModal">
<div>Low Stock (&lt;5 KG)</div>
<div class="value text-warning"><?= count($low_stock_today) ?></div>
</div>
</div>
</div>

<!-- ✅ Today Stock Summary -->
<h5 class="fw-bold mb-2">📦 Today Stock Summary</h5>
<table class="table table-bordered bg-white table-sm">
<thead class="table-success"><tr><th>Product</th><th>Available (KG)</th></tr></thead>
<tbody>
<?php foreach($today_available as $name=>$qty){ ?>
<tr>
<td><?= $name ?></td>
<td class="<?= $qty<5?'text-danger fw-bold':'' ?>"><?= $qty ?> KG</td>
</tr>
<?php } ?>
</tbody>
</table>

<hr>

<!-- ✅ Chart -->
<h5 class="text-center fw-bold">📈 Today's Sales Chart</h5>
<canvas id="salesChart" height="130"></canvas>

<script>
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data:{
        labels: <?= json_encode(array_keys($today_sold_list)) ?>,
        datasets:[{ data: <?= json_encode(array_values($today_sold_list)) ?> }]
    },
    options:{responsive:true}
});
</script>

<!-- ✅ Reusable Modal Function -->
<?php
function popupBox($id,$title,$data){
?>
<div class="modal fade" id="<?= $id ?>">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5><?= $title ?></h5></div>
<div class="modal-body">
<table class="table table-bordered table-sm">
<tr><th>Product</th><th>KG</th></tr>
<?php foreach($data as $n=>$v){ ?>
<tr><td><?= $n ?></td><td><?= $v ?> KG</td></tr>
<?php } ?>
</table>
</div></div></div></div>
<?php } ?>

<?php 
popupBox("todayStockModal","Today's Stock",$today_stock_list);
popupBox("todaySoldModal","Today's Sales",$today_sold_list);
popupBox("lowStockModal","Low Stock (<5 KG)",$low_stock_today);
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>
