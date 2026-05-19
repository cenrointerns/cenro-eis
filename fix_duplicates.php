<?php
// fix_duplicates.php - Run this to clean all duplicate IDs at once
include 'config.php';

echo "<h2>Cleaning Duplicate IDs</h2>";

// Find all duplicate IDs
$query = "SELECT id, COUNT(*) as count, MIN(id) as min_id 
          FROM employees 
          GROUP BY id 
          HAVING COUNT(*) > 1";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $duplicateId = $row['id'];
        $count = $row['count'];
        
        // Delete duplicates (keep the first one, delete the rest)
        $deleteQuery = "DELETE FROM employees 
                       WHERE id = '$duplicateId' 
                       LIMIT " . ($count - 1);
        
        if (mysqli_query($conn, $deleteQuery)) {
            echo "<p style='color: green;'>✓ Cleaned $count duplicates for ID: $duplicateId</p>";
        }
    }
    echo "<p><a href='employee_list.php'>Go back to Employee List</a></p>";
} else {
    echo "<p style='color: green;'>✓ No duplicate IDs found!</p>";
    echo "<p><a href='employee_list.php'>Go back to Employee List</a></p>";
}

mysqli_close($conn);
?>