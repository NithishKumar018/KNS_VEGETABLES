<?php
include "db.php";
$id = $_GET['id'];

$conn->query("UPDATE sales SET quantity = quantity - 1 WHERE id=$id");
$conn->query("DELETE FROM sales WHERE quantity <= 0");

echo "ok";
?>
