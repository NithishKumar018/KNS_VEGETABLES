<?php
include 'db.php';

$product_id   = $_POST['product_id'];
$customer_name = $_POST['customer_name'];
$sale_type     = $_POST['sale_type'];
$quantity      = floatval($_POST['quantity']);
$rate          = floatval($_POST['rate']);
$date          = date("Y-m-d");

// Save sale
$sql = "INSERT INTO sales (product_id, customer_name, sale_type, quantity, rate, date) 
        VALUES ('$product_id', '$customer_name', '$sale_type', '$quantity', '$rate', '$date')";

if ($conn->query($sql)) {
    if ($sale_type == "Wholesale") {
        // Reduce stock for wholesale only
        $update = "UPDATE stocks SET quantity = quantity - $quantity 
                   WHERE product_id = '$product_id' 
                   AND DATE(date_added) = CURDATE()";
        $conn->query($update);
    }
    echo "✅ Sale saved successfully!";
} else {
    echo "❌ Error: " . $conn->error;
}
$conn->close();
?>
