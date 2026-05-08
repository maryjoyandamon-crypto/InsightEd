<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['teacher_id'])) { exit("Access Denied"); }

$my_dept = $_SESSION['subject'];
$filter_value = $_GET['filter_key'] ?? 'All';
$selected_year = $_GET['school_year'] ?? 'All';

$chart_where = "WHERE subject = '$my_dept'";
if ($filter_value != 'All') {
    $parts = explode('|', $filter_value);
    $chart_where .= " AND subject = '" . mysqli_real_escape_string($conn, $parts[0]) . "' AND section = '" . mysqli_real_escape_string($conn, $parts[1]) . "'";
}
if ($selected_year != 'All') {
    $chart_where .= " AND school_year = '" . mysqli_real_escape_string($conn, $selected_year) . "'";
}

$p_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM students $chart_where AND total_score >= 75");
$a_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM students $chart_where AND total_score < 75");
$passing = mysqli_fetch_assoc($p_q)['total'] ?? 0;
$at_risk = mysqli_fetch_assoc($a_q)['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Academic_Report_<?= date('Y-m-d'); ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px; color: #333; background: #fff; }
        .header { border-bottom: 2px solid #2ecc71; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { border: 1px solid #eee; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .no-print { background: #2ecc71; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px; font-weight: bold; }
        @media print { 
            .no-print { display: none; } 
            @page { margin: 0; } 
            body { margin: 1.6cm; } 
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <button class="no-print" onclick="window.print()">Download Report as PDF</button>

    <div class="header">
        <div>
            <h1 style="margin:0;">InsightEd <span style="color:#2ecc71;">Class Report</span></h1>
            <p>Department: <b><?= strtoupper($my_dept); ?></b> | SY: <b><?= $selected_year; ?></b></p>
        </div>
        <div style="text-align:right">
            <p>Generated: <?= date('F d, Y h:i A'); ?></p>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="margin-top:0">Student Distribution</h3>
            <div style="height:250px"><canvas id="pChart"></canvas></div>
            <p style="text-align:center; font-weight:bold;">Passing: <?= $passing; ?> | At-Risk: <?= $at_risk; ?></p>
        </div>
        <div class="card">
            <h3 style="margin-top:0">Performance Overview</h3>
            <div style="height:250px"><canvas id="bChart"></canvas></div>
        </div>
    </div>

    <div class="card" style="margin-top:30px">
        <h3 style="margin-top:0">Weekly Learning Trend</h3>
        <div style="height:300px"><canvas id="lChart"></canvas></div>
    </div>

    <script>
        const commonOptions = { responsive: true, maintainAspectRatio: false };
        
        new Chart(document.getElementById('pChart'), {
            type: 'pie',
            data: {
                labels: ['Passing', 'At-Risk'],
                datasets: [{ data: [<?= $passing; ?>, <?= $at_risk; ?>], backgroundColor: ['#2ecc71', '#e74c3c'] }]
            },
            options: commonOptions
        });

        new Chart(document.getElementById('bChart'), {
            type: 'bar',
            data: {
                labels: ['Current Data'],
                datasets: [
                    { label: 'Passing', data: [<?= $passing; ?>], backgroundColor: '#2ecc71' },
                    { label: 'At-Risk', data: [<?= $at_risk; ?>], backgroundColor: '#e74c3c' }
                ]
            },
            options: commonOptions
        });

        new Chart(document.getElementById('lChart'), {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                datasets: [{
                    label: 'Class Performance',
                    data: [78, 82, 80, 85, 89],
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: commonOptions
        });
    </script>
</body>
</html>