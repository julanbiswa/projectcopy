<?php
// search.php

// Include the database connection file
include("conn.php");

// Set the content type to application/json
header('Content-Type: application/json');

// Check if a search query is provided
if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
} else {
    $searchQuery = '';
}

$results = [];

try {
    // Sanitize the search query to prevent SQL injection
    $searchTerm = "%" . $searchQuery . "%";

    // Prepared statement to safely query the database
    // We search by both 'name' and 'model' as requested
    if (!empty($searchQuery)) {
        $stmt = $conn->prepare("SELECT * FROM machinery WHERE name LIKE ? OR model LIKE ? ORDER BY id DESC");
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
    } else {
        // If the search query is empty, return all items
        $stmt = $conn->prepare("SELECT * FROM machinery ORDER BY id DESC");
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all results into an array
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    $stmt->close();

    // Return the results as a JSON array
    echo json_encode($results);

} catch (Exception $e) {
    // Handle potential errors and return an error message as JSON
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Database query failed: " . $e->getMessage()]);
}

// Close the database connection
$conn->close();
?>
