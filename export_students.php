<?php
include 'connection.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=InsightEd_Student_Records.xls");
header("Pragma: no-cache");
header("Expires: 0");

$query = "SELECT * FROM students ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<style>
    /* Gipugngan ang background nga molapas */
    .table-header th {
        background-color: #2ecc71 !important;
        color: #ffffff;
        font-weight: bold;
        text-align: center;
        border: 1px solid #27ae60;
    }
    .main-title {
        background-color: #1a1a1a;
        color: #ffffff;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        height: 40px;
    }
    th, td {
        border: 1px solid #dee2e6;
        padding: 8px;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    .status-high { color: #2ecc71; font-weight: bold; }
    .status-avg { color: #f39c12; font-weight: bold; }
    .status-risk { color: #e74c3c; font-weight: bold; }
</style>

<table border="1">
    <thead>
        <tr>
            <th colspan="6" class="main-title">
                INSIGHT-ED STUDENT ACADEMIC RECORDS
            </th>
        </tr>
        <tr class="table-header">
            <th width="100">Student ID</th>
            <th width="250">Full Name</th>
            <th width="150">Subject</th>
            <th width="120">School Year</th>
            <th width="80">Grade</th>
            <th width="180">Performance Status</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $status = '';
                $style = '';
                
                if ($row['grade'] == 'A') {
                    $status = 'High Performing';
                    $style = 'status-high';
                } elseif ($row['grade'] == 'B' || $row['grade'] == 'C') {
                    $status = 'Average';
                    $style = 'status-avg';
                } else {
                    $status = 'At-Risk';
                    $style = 'status-risk';
                }

                echo "<tr>";
                echo "<td style='text-align: center;'>#" . str_pad($row['student_id'], 4, "0", STR_PAD_LEFT) . "</td>";
                echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                echo "<td style='text-align: center;'>" . htmlspecialchars($row['subject']) . "</td>";
                echo "<td style='text-align: center;'>" . htmlspecialchars($row['school_year']) . "</td>";
                echo "<td style='text-align: center; font-weight: bold;'>" . $row['grade'] . "</td>";
                echo "<td class='$style'>$status</td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>