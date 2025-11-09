<?php
include "db.php";

$pid = $_GET['product_id'];

$sql = "SELECT quantity FROM stocks WHERE product_id='$pid' ORDER BY id DESC LIMIT 1";
$res = $conn->query($sql);
$row = $res->fetch_assoc();

echo $row['quantity'];
