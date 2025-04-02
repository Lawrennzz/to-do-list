<?php
require_once 'includes/db_connect.php';

$query = "DESCRIBE tasks";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Field: " . $row['Field'] . " - Type: " . $row['Type'] . "<br>";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
