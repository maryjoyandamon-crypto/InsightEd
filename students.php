<?php
include 'connection.php';

$query = "SELECT * FROM students ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEd | Students Management</title>
    <link rel="stylesheet" href="css/dashboard.css"> 
    <link rel="stylesheet" href="css/students.css">  
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .left-actions {
            display: flex !important;
            gap: 12px;
            align-items: center;
        }

        .btn-primary-glass {
            display: inline-flex !important;
            width: auto !important;
            padding: 10px 20px !important;
            background: #2ecc71;
            color: #050c09;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-secondary-glass {
            display: inline-flex !important;
            width: auto !important;
            padding: 10px 20px !important;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid rgba(46, 204, 113, 0.15);
            border-radius: 12px !important;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-primary-glass:hover, .btn-secondary-glass:hover {
            transform: translateY(-2px);
            transition: 0.3s;
        }
    </style>
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?> 

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Students Management</h1>
                    <p>Academic performance database and records.</p>
                </div>
                <div class="search-box-glass">
                    <i data-lucide="search"></i>
                    <input type="text" id="studentSearch" placeholder="Search students..." onkeyup="filterTable()">
                </div>
            </header>

            <div class="action-bar-glass">
                <div class="left-actions">
                    <a href="add_student.php" class="btn-primary-glass">
                        <i data-lucide="user-plus"></i> Add New Student
                    </a>
                    
                    <a href="export_students.php" class="btn-primary-glass">
                        <i data-lucide="download"></i> Export Records
                    </a>
                </div>
            </div>

            <div class="table-container-glass">
                <table class="glass-table" id="studentTable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): 
                                $status_class = '';
                                $status_text = '';
                                if ($row['grade'] == 'A') {
                                    $status_class = 'status-high'; 
                                    $status_text = 'High Performing';
                                } elseif ($row['grade'] == 'B' || $row['grade'] == 'C') {
                                    $status_class = 'status-avg'; 
                                    $status_text = 'Average';
                                } else {
                                    $status_class = 'status-risk'; 
                                    $status_text = 'At-Risk';
                                }
                            ?>
                            <tr>
                                <td class="id-cell">#<?php echo str_pad($row['student_id'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td class="name-cell"><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td class="grade-cell"><?php echo $row['grade']; ?></td>
                                <td><span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <a href="view_student.php?id=<?php echo $row['student_id']; ?>" class="action-btn view" title="View Profile">
                                            <i data-lucide="eye"></i>
                                        </a>
                                        
                                        <a href="edit_student.php?id=<?php echo $row['student_id']; ?>" class="action-btn edit" title="Edit Record">
                                            <i data-lucide="edit-3"></i>
                                        </a>
                                        
                                        <a href="delete_student.php?id=<?php echo $row['student_id']; ?>" 
                                        class="action-btn delete" 
                                        title="Delete Student"
                                        onclick="return confirm('Are you sure you want to delete this student?');">
                                            <i data-lucide="trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-state" style="text-align: center; padding: 40px; color: #94a3b8;">No students found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        function filterTable() {
            let input = document.getElementById("studentSearch");
            let filter = input.value.toUpperCase();
            let table = document.getElementById("studentTable");
            let tr = table.getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                let tdName = tr[i].getElementsByTagName("td")[1];
                let tdID = tr[i].getElementsByTagName("td")[0];
                if (tdName || tdID) {
                    let txtValueName = tdName.textContent || tdName.innerText;
                    let txtValueID = tdID.textContent || tdID.innerText;
                    tr[i].style.display = (txtValueName.toUpperCase().indexOf(filter) > -1 || txtValueID.toUpperCase().indexOf(filter) > -1) ? "" : "none";
                }
            }
        }

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
                ctx.fillStyle = 'rgba(46, 204, 113, 0.15)';
                ctx.fill();
            }
            update() {
                if (this.x > canvas.width || this.x < 0) this.dx = -this.dx;
                if (this.y > canvas.height || this.y < 0) this.dy = -this.dy;
                this.x += this.dx; this.y += this.dy;
                this.draw();
            }
        }

        function initParticles() {
            particlesArray = [];
            let numberOfParticles = (canvas.width * canvas.height) / 15000;
            for (let i = 0; i < numberOfParticles; i++) {
                particlesArray.push(new Particle(Math.random() * innerWidth, Math.random() * innerHeight, (Math.random() - 0.5) * 0.8, (Math.random() - 0.5) * 0.8, Math.random() * 2 + 1));
            }
        }

        function animateParticles() {
            requestAnimationFrame(animateParticles);
            ctx.clearRect(0, 0, innerWidth, innerHeight);
            for (let a = 0; a < particlesArray.length; a++) {
                for (let b = a; b < particlesArray.length; b++) {
                    let distance = ((particlesArray[a].x - particlesArray[b].x) ** 2) + ((particlesArray[a].y - particlesArray[b].y) ** 2);
                    if (distance < 12000) {
                        ctx.strokeStyle = `rgba(52, 152, 219, ${1 - distance/12000})`;
                        ctx.lineWidth = 0.5;
                        ctx.beginPath();
                        ctx.moveTo(particlesArray[a].x, particlesArray[a].y);
                        ctx.lineTo(particlesArray[b].x, particlesArray[b].y);
                        ctx.stroke();
                    }
                }
                particlesArray[a].update();
            }
        }

        window.addEventListener('resize', () => {
            canvas.width = innerWidth; canvas.height = innerHeight;
            initParticles();
        });

        initParticles();
        animateParticles();
    </script>
</body>
</html>