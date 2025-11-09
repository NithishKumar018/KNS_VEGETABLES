<?php
include "db.php";

$pid = $_GET['product_id'];

$sql = "SELECT id, customer_name, quantity 
        FROM sales 
        WHERE product_id='$pid' AND DATE(created_at)=CURDATE()";

$res = $conn->query($sql);

$data=[];
while($r=$res->fetch_assoc()) $data[]=$r;

echo json_encode($data);
