<?php
session_start();
include 'connection.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT * FROM students WHERE student_id = '$id'";
    $result = mysqli_query($conn, $query);
    $student = mysqli_fetch_assoc($result);

    if (!$student) {
        echo "<script>alert('Student not found!'); window.location.href='students.php';</script>";
        exit();
    }
} else {
    header("Location: students.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile | <?php echo htmlspecialchars($student['fullname']); ?></title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_student.css">
    <link rel="stylesheet" href="css/view_details.css"> 
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Student Profile</h1>
                    <p>Detailed academic record for #<?php echo str_pad($student['student_id'], 4, "0", STR_PAD_LEFT); ?></p>
                </div>
                <div class="header-right">
                    <a href="students.php" class="btn-cancel" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="arrow-left"></i> Back to List
                    </a>
                </div>
            </header>

            <div class="form-container-glass">
                <div class="form-header-box">
                    <div class="header-info">
                        <i data-lucide="user" class="header-icon"></i>
                        <div>
                            <h3><?php echo htmlspecialchars($student['fullname']); ?></h3>
                            <p>Enrolled in <?php echo htmlspecialchars($student['subject']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div class="info-box">
                        <label><i data-lucide="fingerprint"></i> Student ID</label>
                        <span>#<?php echo $student['student_id']; ?></span>
                    </div>
                    <div class="info-box">
                        <label><i data-lucide="calendar"></i> School Year</label>
                        <span><?php echo htmlspecialchars($student['school_year'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-box">
                        <label><i data-lucide="hash"></i> Age</label>
                        <span><?php echo $student['age']; ?> Years Old</span>
                    </div>
                    <div class="info-box">
                        <label><i data-lucide="layers"></i> Section</label>
                        <span><?php echo htmlspecialchars($student['section']); ?></span>
                    </div>
                    <div class="info-box">
                        <label><i data-lucide="clock"></i> Study Hours</label>
                        <span><?php echo $student['weekly_self_study_hours']; ?> hrs/week</span>
                    </div>
                    <div class="info-box">
                        <label><i data-lucide="check-square"></i> Attendance</label>
                        <span><?php echo $student['attendance_percentage']; ?>%</span>
                    </div>
                    <div class="info-box">
                        <label><i data-lucide="message-circle"></i> Participation</label>
                        <span><?php echo $student['class_participation']; ?> / 10</span>
                    </div>
                    <div class="info-box">
                        <label><i data-lucide="bar-chart-3"></i> Total Score</label>
                        <span><?php echo $student['total_score']; ?></span>
                    </div>
                    <div class="info-box">
                        <label><i data-lucide="calendar-days"></i> Date Added</label>
                        <span><?php echo date('F d, Y', strtotime($student['created_at'])); ?></span>
                    </div>
                </div>

                <div class="prediction-box" style="margin-top: 30px; padding: 40px; border-radius: 24px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); text-align: center;">
                    <label style="color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; margin-bottom: 15px; display: block;">AI Analysis Result</label>
                    <?php 
                        $g = $student['grade'];
                        if($g == 'A') {
                            echo "<h1 style='color: #2ecc71; font-size: 3rem; font-weight: 800;'>HIGH PERFORMING</h1><p style='color: #a8e6cf;'>Student shows consistent excellence and high engagement.</p>";
                        } elseif($g == 'B' || $g == 'C') {
                            echo "<h1 style='color: #3498db; font-size: 3rem; font-weight: 800;'>AVERAGE</h1><p style='color: #d1d8e0;'>Student is performing within expected academic standards.</p>";
                        } else {
                            echo "<h1 style='color: #e74c3c; font-size: 3rem; font-weight: 800;'>AT-RISK</h1><p style='color: #fab1a0;'>Requires immediate academic intervention and support.</p>";
                        }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let particlesArray = [];

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

        function initParticles() {
            particlesArray = [];
            let numberOfParticles = (canvas.width * canvas.height) / 15000;
            for (let i = 0; i < numberOfParticles; i++) {
                particlesArray.push(new Particle());
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < particlesArray.length; i++) {
                particlesArray[i].update();
                particlesArray[i].draw();

                for (let j = i; j < particlesArray.length; j++) {
                    const dx = particlesArray[i].x - particlesArray[j].x;
                    const dy = particlesArray[i].y - particlesArray[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < 150) {
                        ctx.strokeStyle = `rgba(52, 152, 219, ${1 - distance/150})`;
                        ctx.lineWidth = 0.5;
                        ctx.beginPath();
                        ctx.moveTo(particlesArray[i].x, particlesArray[i].y);
                        ctx.lineTo(particlesArray[j].x, particlesArray[j].y);
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(animateParticles);
        }

        window.addEventListener('resize', () => {
            canvas.width = innerWidth;
            canvas.height = innerHeight;
            initParticles();
        });

        initParticles();
        animateParticles();
    </script>
</body>
</html>