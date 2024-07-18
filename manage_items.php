<?php
// manage_items.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grocery_shop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['success' => false, 'message' => ''];

if ($_POST['newItemName']) {
    // Add new item
    $name = $_POST['newItemName'];
    $cost = $_POST['newItemCost'];
    $quantity = $_POST['newItemQuantity'];

    $sql = "INSERT INTO items (name, cost, quantity) VALUES ('$name', $cost, $quantity)";
    if ($conn->query($sql) === TRUE) {
        $response['success'] = true;
        $response['message'] = "New item added successfully.";
    } else {
        $response['message'] = "Error adding item: " . $conn->error;
    }
} 
elseif ($_POST['updateItemName']) {
    // Update item quantity
    $name = $_POST['updateItemName'];
    $quantity = $_POST['updateItemQuantity'];

    $sql = "UPDATE items SET quantity = $quantity WHERE name = '$name'";
    if ($conn->query($sql) === TRUE) {
        $response['success'] = true;
        $response['message'] = "Item quantity updated successfully.";
    } else {
        $response['message'] = "Error updating item: " . $conn->error;
    }
}

echo json_encode($response);

$conn->close();
?>
