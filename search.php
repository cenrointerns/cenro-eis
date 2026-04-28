<?php
$conn=new mysqli("localhost","root","","cenro");

$search=$_GET['search']??"";

$result=$conn->query("SELECT * FROM employees WHERE name LIKE '%$search%'");

$image_folder="assets/image/employee/";

echo "<table><tr><th>ID</th><th>Image</th><th>Name</th><th>Status</th><th>Actions</th></tr>";

while($row=$result->fetch_assoc()){
echo "<tr onclick='showProfile(".json_encode($row).")'>";
echo "<td>{$row['employee_id']}</td>";

if($row['image']){
echo "<td><img src='{$image_folder}{$row['image']}' width='50'></td>";
}else{
echo "<td><img src='{$image_folder}default.png' width='50'></td>";
}

echo "<td>{$row['name']}</td>";
echo "<td>{$row['status']}</td>";
echo "<td><a href='?delete={$row['employee_id']}'>Delete</a></td>";
echo "</tr>";
}
echo "</table>";