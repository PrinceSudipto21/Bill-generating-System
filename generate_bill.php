<?php
// generate_bill.php

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

$response = ['success' => false, 'message' => '', 'bill' => null];

$phone = $_POST['phone'];
$name = $_POST['name'];
$address = $_POST['address'];
$items = $_POST['item'];
$quantities = $_POST['quantity'];

// Check if customer exists
$sql = "SELECT * FROM customers WHERE phone = '$phone'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Customer exists, use existing details
    $customer = $result->fetch_assoc();
    $name = $customer['name'];
    $address = $customer['address'];
} else {
    // Customer doesn't exist, validate required fields
    if (empty($name) || empty($address)) {
        $response['message'] = "Name and address are required for new customers.";
        echo json_encode($response);
        exit();
    }

    // Insert new customer
    $sql = "INSERT INTO customers (phone, name, address) VALUES ('$phone', '$name', '$address')";
    if ($conn->query($sql) !== TRUE) {
        $response['message'] = "Error adding new customer: " . $conn->error;
        echo json_encode($response);
        exit();
    }
}

$total_cost = 0;
$bill_items = [];

// Insert sales records and update item quantities
for ($i = 0; $i < count($items); $i++) {
    $item_name = $items[$i];
    $quantity = $quantities[$i];
    
    // Get item details
    $sql = "SELECT * FROM items WHERE name = '$item_name'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $item_id = $item['id'];
        $item_cost = $item['cost'];
        $item_quantity = $item['quantity'];
        
        if ($item_quantity >= $quantity) {
            // Calculate total cost for the item
            $cost = $item_cost * $quantity;
            $total_cost += $cost;
            
            // Insert sales record
            $sql = "INSERT INTO sales (customer_phone, item_id, quantity, total_cost) VALUES ('$phone', $item_id, $quantity, $cost)";
            $conn->query($sql);
            
            // Update item quantity
            $new_quantity = $item_quantity - $quantity;
            $sql = "UPDATE items SET quantity = $new_quantity WHERE id = $item_id";
            $conn->query($sql);

            // Add item to bill
            $bill_items[] = [
                'name' => $item_name,
                'quantity' => $quantity,
                'cost' => $item_cost,
                'total_cost' => $cost
            ];
        } else {
            $response['message'] = "Insufficient quantity for $item_name.";
            echo json_encode($response);
            exit();
        }
    } else {
        $response['message'] = "Item $item_name not found.";
        echo json_encode($response);
        exit();
    }
}

// Return success response with bill details
$response['success'] = true;
$response['bill'] = [
    'customer' => [
        'name' => $name,
        'address' => $address,
        'phone' => $phone
    ],
    'items' => $bill_items,
    'total_cost' => $total_cost
];
echo json_encode($response);

$conn->close();
?>
