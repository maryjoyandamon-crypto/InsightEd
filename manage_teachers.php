<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$teachers_query = "SELECT * FROM users WHERE role = 'Teacher' AND status = 'Approved' ORDER BY lname ASC";
$teachers_result = mysqli_query($conn, $teachers_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers | Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="images/logo.png" alt="Logo" class="logo">
                <h2>Admin Panel</h2>
            </div>
            <nav class="nav-menu">
                <a href="admin_dashboard.php" class="nav-link"><i data-lucide="shield-check"></i> Approvals</a>
                <a href="manage_teachers.php" class="nav-link active"><i data-lucide="users"></i> Manage Teachers</a>
                <div class="nav-divider"></div>
                <a href="logout.php" class="nav-link logout"><i data-lucide="log-out"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="header-title">
                    <h1>Teacher Directory</h1>
                    <p>View and manage all registered teachers in the system.</p>
                </div>
            </header>

            <section class="glass-card">
                <div class="card-header">
                    <h3>Active Teachers</h3>
                    <span class="badge"><?php echo mysqli_num_rows($teachers_result); ?> Total</span>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Teacher ID</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($teachers_result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($teachers_result)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['lname'] . ", " . $row['fname']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($row['teacher_id']); ?></code></td>
                                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                    <td><span style="color: #2ecc71;">● Active</span></td>
                                    <td>
                                        <a href="process_approval.php?id=<?php echo $row['user_id']; ?>&action=decline" class="btn decline" onclick="return confirm('Deactivate this teacher?')">Deactivate</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-msg">No approved teachers yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        lucide.createIcons();

        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        let particlesArray = [];
        const maxDistance = 150;

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2 + 1;
                this.speedX = (Math.random() - 0.5) * 1.2;
                this.speedY = (Math.random() - 0.5) * 1.2;
            }
            draw() {
                ctx.fillStyle = 'rgba(46, 204, 113, 0.4)';
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                if (this.x > canvas.width || this.x < 0) this.speedX *= -1;
                if (this.y > canvas.height || this.y < 0) this.speedY *= -1;
            }
        }

        function init() {
            particlesArray = [];
            let count = (canvas.width * canvas.height) / 15000;
            for (let i = 0; i < count; i++) {
                particlesArray.push(new Particle());
            }
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < particlesArray.length; i++) {
                particlesArray[i].update();
                particlesArray[i].draw();

                for (let j = i; j < particlesArray.length; j++) {
                    const dx = particlesArray[i].x - particlesArray[j].x;
                    const dy = particlesArray[i].y - particlesArray[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < maxDistance) {
                        ctx.strokeStyle = `rgba(52, 152, 219, ${1 - distance/maxDistance})`;
                        ctx.lineWidth = 0.5;
                        ctx.beginPath();
                        ctx.moveTo(particlesArray[i].x, particlesArray[i].y);
                        ctx.lineTo(particlesArray[j].x, particlesArray[j].y);
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(animate);
        }

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            init();
        });

        init();
        animate();
    </script>
</body>
</html>