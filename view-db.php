<?php
require 'config/database.php'; // Pastikan koneksi database sudah benar

// Nama database
$database_name = 'ryan_task_db';

// Query untuk mendapatkan daftar tabel
$query_tables = "SHOW TABLES FROM $database_name";
$result_tables = $conn->query($query_tables);

if ($result_tables->num_rows > 0) {
    echo "<h1>Database: $database_name</h1>";
    echo "<h2>Daftar Tabel:</h2>";

    // Loop untuk menampilkan tabel
    while ($row = $result_tables->fetch_array()) {
        $table_name = $row[0];
        echo "<h3>Tabel: $table_name</h3>";

        // Query untuk mendapatkan isi tabel
        $query_data = "SELECT * FROM $table_name";
        $result_data = $conn->query($query_data);

        if ($result_data->num_rows > 0) {
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr>";

            // Header kolom
            $fields = $result_data->fetch_fields();
            foreach ($fields as $field) {
                echo "<th>" . htmlspecialchars($field->name) . "</th>";
            }
            echo "</tr>";

            // Data baris
            while ($data = $result_data->fetch_assoc()) {
                echo "<tr>";
                foreach ($data as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Tabel ini kosong.</p>";
        }
    }
} else {
    echo "<p>Database tidak memiliki tabel.</p>";
}
?>
