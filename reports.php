<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['subject'])) {
    header("Location: login.php");
    exit();
}

$my_dept = $_SESSION['subject'];
$filter_value = isset($_GET['filter_key']) ? mysqli_real_escape_string($conn, $_GET['filter_key']) : 'All';
$selected_year = isset($_GET['school_year']) ? mysqli_real_escape_string($conn, $_GET['school_year']) : 'All';

$chart_where = "WHERE subject = '$my_dept'";
if ($filter_value != 'All') {
    $parts = explode('|', $filter_value);
    $chart_where .= " AND subject = '" . mysqli_real_escape_string($conn, $parts[0]) . "' AND section = '" . mysqli_real_escape_string($conn, $parts[1]) . "'";
}
if ($selected_year != 'All') {
    $chart_where .= " AND school_year = '" . mysqli_real_escape_string($conn, $selected_year) . "'";
}

$passing_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM students $chart_where AND grade IN ('A', 'B', 'C')");
$at_risk_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM students $chart_where AND grade IN ('D', 'F')");

$passing_count = mysqli_fetch_assoc($passing_query)['total'] ?? 0;
$at_risk_count = mysqli_fetch_assoc($at_risk_query)['total'] ?? 0;

$dropdown_query = mysqli_query($conn, "SELECT DISTINCT subject, section FROM students WHERE subject = '$my_dept' ORDER BY subject ASC");
$year_query = mysqli_query($conn, "SELECT DISTINCT school_year FROM students WHERE subject = '$my_dept' ORDER BY school_year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InsightEd | Reports</title>
    <link rel="stylesheet" href="css/dashboard.css"> 
    <link rel="stylesheet" href="css/reports.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="reports-body">
    <canvas id="particleCanvas"></canvas>
    <div class="main-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="content-area">
            <header class="report-header-glass">
                <div class="header-info">
                    <h1>Class Performance <span>Reports</span></h1>
                    <p>Dept: <strong><?php echo strtoupper($my_dept); ?></strong></p>
                </div>
                <button class="btn-print" onclick="exportToPDF()"><i data-lucide="file-down"></i> Export PDF</button>
            </header>

            <div class="reports-layout-grid">
                <aside class="glass-card filter-panel">
                    <h3><i data-lucide="filter"></i> Filters</h3>
                    <form action="" method="GET">
                        <div class="input-group-glass">
                            <label>School Year</label>
                            <select name="school_year" id="sy_select">
                                <option value="All">All Years</option>
                                <?php while($y = mysqli_fetch_assoc($year_query)): ?>
                                    <option value="<?= $y['school_year']; ?>" <?= ($selected_year == $y['school_year']) ? 'selected' : ''; ?>>
                                        SY <?= $y['school_year'] . "-" . ($y['school_year'] + 1); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="input-group-glass">
                            <label>Class Selection</label> 
                            <select name="filter_key" id="class_select">
                                <option value="All">All Classes</option>
                                <?php mysqli_data_seek($dropdown_query, 0); 
                                while($row = mysqli_fetch_assoc($dropdown_query)): 
                                    $val = $row['subject'] . "|" . $row['section']; ?>
                                    <option value="<?= $val; ?>" <?= ($filter_value == $val) ? 'selected' : ''; ?>>
                                        <?= $row['subject'] . " - " . $row['section']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit-glass">Generate</button>
                    </form>
                </aside>

                <section class="analysis-main">
                    <div class="glass-card">
                        <h3><i data-lucide="pie-chart"></i> Distribution Analysis</h3>
                        <div class="charts-flex-row">
                            <div class="chart-wrapper-small"><canvas id="pieChart"></canvas></div>
                            <div class="chart-wrapper-small"><canvas id="barChart"></canvas></div>
                        </div>
                        <div class="insight-pill-container">
                            <div class="pill passing">Passing: <?= $passing_count; ?></div>
                            <div class="pill at-risk">At-Risk: <?= $at_risk_count; ?></div>
                        </div>
                    </div>
                    <div class="glass-card progress-card">
                        <h3><i data-lucide="trending-up"></i> Weekly Progress</h3>
                        <div class="line-chart-wrapper"><canvas id="lineChart"></canvas></div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        function exportToPDF() {
            const sy = document.getElementById('sy_select').value;
            const classKey = encodeURIComponent(document.getElementById('class_select').value);
            window.open(`export_pdf.php?school_year=${sy}&filter_key=${classKey}`, '_blank');
        }

        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Passing', 'At-Risk'],
                datasets: [{
                    data: [<?= $passing_count; ?>, <?= $at_risk_count; ?>],
                    backgroundColor: ['#2ecc71', '#e74c3c'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: ['Prev Period', 'Current'],
                datasets: [
                    { label: 'Passing', data: [10, <?= $passing_count; ?>], backgroundColor: '#2ecc71' },
                    { label: 'At-Risk', data: [5, <?= $at_risk_count; ?>], backgroundColor: '#e74c3c' }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: ['W1','W2','W3','W4','W5','W6','W7','W8'],
                datasets: [{
                    label: 'Class Avg %',
                    data: [70, 72, 75, 74, 80, 82, 85, 88],
                    borderColor: '#2ecc71',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(46, 204, 113, 0.1)'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>
</html>