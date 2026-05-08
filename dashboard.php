<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE teacher_id = '$teacher_id'");
$user = mysqli_fetch_assoc($query);

$fname = $user['fname'] ?? 'Teacher';
$lname = $user['lname'] ?? '';
$subject = $user['subject'] ?? 'General';
$profile_pic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_avatar.png';

$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students"))['c'] ?? 0;
$high_perf = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students WHERE grade = 'A'"))['c'] ?? 0;
$average = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students WHERE grade IN ('B', 'C')"))['c'] ?? 0;
$at_risk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students WHERE grade IN ('D', 'F') OR grade IS NULL OR grade = '' OR grade = '0'"))['c'] ?? 0;

$academic_year = "2026-2027"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEd | Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="images/logo.png" alt="Logo" class="side-logo">
                <h2>InsightEd</h2>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-link active"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="students.php" class="nav-link"><i data-lucide="users"></i> Students</a>
                <a href="predict.php" class="nav-link"><i data-lucide="brain-circuit"></i> Prediction</a>
                <a href="reports.php" class="nav-link"><i data-lucide="file-bar-chart"></i> Reports</a>
                <div class="nav-divider"></div>
                <a href="settings.php" class="nav-link"><i data-lucide="settings"></i> Settings</a>
                <a href="logout.php" class="nav-link logout"><i data-lucide="log-out"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Welcome, <?php echo htmlspecialchars($fname); ?>! 👋</h1>
                    <p>Monitoring <?php echo htmlspecialchars($subject); ?> classes</p>
                </div>
                
                <div class="header-right">
                    <div class="academic-badge">A.Y. <?php echo $academic_year; ?></div>
                    <div class="profile-box" onclick="location.href='profile.php'">
                        <div class="profile-text">
                            <span class="profile-name"><?php echo htmlspecialchars($fname . " " . $lname); ?></span>
                            <span class="profile-role">Faculty Member</span>
                        </div>
                        <div class="profile-avatar-wrapper">
                            <img src="images/<?php echo $profile_pic; ?>" alt="Profile" class="profile-avatar">
                        </div>
                    </div>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i data-lucide="users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i data-lucide="trending-up"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $high_perf; ?></h3>
                        <p>High</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i data-lucide="award"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $average; ?></h3>
                        <p>Average</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i data-lucide="alert-circle"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $at_risk; ?></h3>
                        <p>At-Risk</p>
                    </div>
                </div>
            </section>

            <div class="chart-box-wrapper">
                <div class="chart-label">Performance Analytics</div>
                <div style="height: 300px; width: 100%;">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['High Performing', 'Average', 'At-Risk'],
                datasets: [{
                    label: 'Students',
                    data: [<?php echo $high_perf; ?>, <?php echo $average; ?>, <?php echo $at_risk; ?>],
                    backgroundColor: ['#2ecc71', '#f39c12', '#e74c3c'],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#888' } },
                    x: { grid: { display: false }, ticks: { color: '#fff' } }
                },
                plugins: { legend: { display: false } }
            }
        });

        document.querySelectorAll('.nav-link').forEach(link => {
            link.onmousemove = (e) => {
                const rect = link.getBoundingClientRect();
                link.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
                link.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
            };
        });

        const canvas = document.getElementById('particleCanvas');
        const pCtx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        let particlesArray = [];

        class Particle {
            constructor(x, y, dx, dy, size) {
                this.x = x; this.y = y; this.dx = dx; this.dy = dy; this.size = size;
            }
            draw() {
                pCtx.beginPath();
                pCtx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                pCtx.fillStyle = 'rgba(46, 204, 113, 0.15)';
                pCtx.fill();
            }
            update() {
                if (this.x > canvas.width || this.x < 0) this.dx = -this.dx;
                if (this.y > canvas.height || this.y < 0) this.dy = -this.dy;
                this.x += this.dx; this.y += this.dy;
                this.draw();
            }
        }

        function init() {
            particlesArray = [];
            for (let i = 0; i < 70; i++) {
                particlesArray.push(new Particle(Math.random() * innerWidth, Math.random() * innerHeight, (Math.random() - 0.5) * 1, (Math.random() - 0.5) * 1, Math.random() * 2 + 1));
            }
        }

        function animate() {
            requestAnimationFrame(animate);
            pCtx.clearRect(0, 0, innerWidth, innerHeight);
            for (let a = 0; a < particlesArray.length; a++) {
                for (let b = a; b < particlesArray.length; b++) {
                    let distance = ((particlesArray[a].x - particlesArray[b].x) ** 2) + ((particlesArray[a].y - particlesArray[b].y) ** 2);
                    if (distance < 15000) {
                        pCtx.strokeStyle = `rgba(52, 152, 219, ${1 - distance/15000})`;
                        pCtx.lineWidth = 0.5;
                        pCtx.beginPath();
                        pCtx.moveTo(particlesArray[a].x, particlesArray[a].y);
                        pCtx.lineTo(particlesArray[b].x, particlesArray[b].y);
                        pCtx.stroke();
                    }
                }
            }
            particlesArray.forEach(p => p.update());
        }

        window.addEventListener('resize', () => { canvas.width = innerWidth; canvas.height = innerHeight; init(); });
        init(); animate();
    </script>
</body>
</html>