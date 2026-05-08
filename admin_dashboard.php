<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_query = "SELECT * FROM users WHERE user_id = '$admin_id'";
$admin_res = mysqli_query($conn, $admin_query);
$admin_data = mysqli_fetch_assoc($admin_res);

$profile_pic = !empty($admin_data['profile_picture']) ? $admin_data['profile_picture'] : 'default_avatar.png';

$pending_query = "SELECT * FROM users WHERE role = 'Teacher' AND status = 'Pending' ORDER BY user_id DESC";
$pending_result = mysqli_query($conn, $pending_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEd | Admin Portal</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
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
                <a href="admin_dashboard.php" class="nav-link active"><i data-lucide="shield-check"></i> Approvals</a>
                <a href="manage_teachers.php" class="nav-link"><i data-lucide="users"></i> Manage Teachers</a>
                <a href="admin_profile.php" class="nav-link"><i data-lucide="user-cog"></i> Profile Settings</a>
                <div class="nav-divider"></div>
                <a href="logout.php" class="nav-link logout"><i data-lucide="log-out"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="header-title">
                    <h1>Registration Requests</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($admin_data['fname']); ?>!</p>
                </div>
                
                <div class="admin-profile-box" onclick="location.href='admin_profile.php'">
                    <div class="admin-text">
                        <span class="admin-name"><?php echo htmlspecialchars($admin_data['fname'] . " " . $admin_data['lname']); ?></span>
                        <span class="admin-role">System Administrator</span>
                    </div>
                    <div class="admin-avatar-wrapper">
                        <img src="uploads/<?php echo $profile_pic; ?>" alt="Admin" class="admin-avatar">
                    </div>
                </div>
            </header>

            <section class="glass-card">
                <div class="card-header">
                    <h3>Pending for Review</h3>
                    <span class="badge"><?php echo mysqli_num_rows($pending_result); ?> New Requests</span>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Teacher Name</th>
                                <th>Teacher ID</th>
                                <th>Subject/Level</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($pending_result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($pending_result)): ?>
                                <tr>
                                    <td>
                                        <div class="name-cell">
                                            <strong><?php echo htmlspecialchars($row['fname'] . " " . $row['lname']); ?></strong>
                                        </div>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($row['teacher_id']); ?></code></td>
                                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="process_approval.php?id=<?php echo $row['user_id']; ?>&action=approve" class="btn approve">Approve</a>
                                            <a href="process_approval.php?id=<?php echo $row['user_id']; ?>&action=decline" class="btn decline" onclick="return confirm('Are you sure?')">Decline</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-msg">
                                        <p>No pending registration requests at the moment.</p>
                                    </td>
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

        document.querySelectorAll('.nav-link').forEach(link => {
            link.onmousemove = (e) => {
                const rect = link.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                link.style.setProperty('--mouse-x', `${x}px`);
                link.style.setProperty('--mouse-y', `${y}px`);
            };
        });

        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        let particlesArray = [];

        class Particle {
            constructor(x, y, dx, dy, size) {
                this.x = x; this.y = y; this.dx = dx; this.dy = dy; this.size = size;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(46, 204, 113, 0.2)';
                ctx.fill();
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
                let size = Math.random() * 2 + 1;
                let x = Math.random() * innerWidth;
                let y = Math.random() * innerHeight;
                let dx = (Math.random() - 0.5) * 1.1;
                let dy = (Math.random() - 0.5) * 1.1;
                particlesArray.push(new Particle(x, y, dx, dy, size));
            }
        }

        function animate() {
            requestAnimationFrame(animate);
            ctx.clearRect(0, 0, innerWidth, innerHeight);
            for (let a = 0; a < particlesArray.length; a++) {
                for (let b = a; b < particlesArray.length; b++) {
                    let distance = ((particlesArray[a].x - particlesArray[b].x) ** 2) + 
                                   ((particlesArray[a].y - particlesArray[b].y) ** 2);
                    if (distance < 15000) {
                        ctx.strokeStyle = `rgba(52, 152, 219, ${1 - distance/15000})`;
                        ctx.lineWidth = 0.5;
                        ctx.beginPath();
                        ctx.moveTo(particlesArray[a].x, particlesArray[a].y);
                        ctx.lineTo(particlesArray[b].x, particlesArray[b].y);
                        ctx.stroke();
                    }
                }
            }
            particlesArray.forEach(p => p.update());
        }

        window.addEventListener('resize', () => {
            canvas.width = innerWidth; canvas.height = innerHeight;
            init();
        });
        init(); animate();
    </script>
</body>
</html>