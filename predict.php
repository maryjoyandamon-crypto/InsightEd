<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$my_subject = $_SESSION['subject'] ?? 'General';
$students_query = mysqli_query($conn, "SELECT * FROM students WHERE subject = '$my_subject' ORDER BY fullname ASC");
$student_count = mysqli_num_rows($students_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEd | Student Predictor</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/predict.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Student Predictor</h1>
                    <p>AI-driven academic performance forecasting for <strong><?php echo $my_subject; ?></strong></p>
                </div>
            </header>

            <div class="predict-grid">
                <div class="glass-card list-card">
                    <div class="card-header">
                        <h3><i data-lucide="users"></i> Students</h3>
                    </div>
                    <div class="student-list custom-scrollbar">
                        <?php if($student_count > 0): ?>
                            <?php while($s = mysqli_fetch_assoc($students_query)): ?>
                                <div class="student-item-glass" onclick="loadStudent(<?php echo $s['student_id']; ?>, event)">
                                    <div class="student-info">
                                        <strong><?php echo htmlspecialchars($s['fullname']); ?></strong>
                                        <span><?php echo htmlspecialchars($s['subject']); ?> | <?php echo htmlspecialchars($s['section']); ?></span>
                                    </div>
                                    <i data-lucide="chevron-right" class="arrow-icon"></i>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i data-lucide="database-zap"></i>
                                <p>No students found. Please add a student to start predicting.</p>
                                <a href="add_student.php" class="btn-add-now">Register Student</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="glass-card detail-card" id="prediction_box" style="display:none;">
                    <div class="detail-header">
                        <div class="profile-info">
                            <h2 id="name"></h2>
                            <div class="pills-row">
                                <span class="badge-glass"><i data-lucide="book-open"></i> <span id="subject"></span></span>
                                <span class="badge-glass"><i data-lucide="layers"></i> <span id="section"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="metrics-grid">
                        <div class="metric-box">
                            <i data-lucide="calendar-check" class="m-blue"></i>
                            <span>Attendance</span>
                            <strong id="att">0</strong><small>%</small>
                        </div>
                        <div class="metric-box">
                            <i data-lucide="clock" class="m-orange"></i>
                            <span>Self-Study</span>
                            <strong id="hrs">0</strong><small>hrs</small>
                        </div>
                        <div class="metric-box">
                            <i data-lucide="message-square" class="m-green"></i>
                            <span>Participation</span>
                            <strong id="part">0</strong><small>%</small>
                        </div>
                        <div class="metric-box">
                            <i data-lucide="trending-up" class="m-purple"></i>
                            <span>Current Score</span>
                            <strong id="score">0</strong>
                        </div>
                    </div>

                    <button class="btn-predict-glass" onclick="predict()">
                        <i data-lucide="sparkles"></i> Run AI Prediction
                    </button>

                    <div id="result" class="prediction-result-area"></div>
                </div>

                <div class="glass-card detail-card" id="placeholder_box">
                    <div class="placeholder-content">
                        <i data-lucide="mouse-pointer-2" class="placeholder-icon"></i>
                        <h3>Select a Student</h3>
                        <p>Click a name from the list to view metrics and run analysis.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        let current_id = null;

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

        function initParticles() {
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

        initParticles();
        animate();

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            initParticles();
        });

        function loadStudent(id, event) {
            current_id = id;
            $('.student-item-glass').removeClass('active');
            $(event.currentTarget).addClass('active');
            $('#placeholder_box').hide();

            $.post("get_student_info.php", {student_id: id}, function(data) {
                $('#prediction_box').css('display', 'flex').hide().fadeIn(400);
                $('#name').text(data.fullname);
                $('#subject').text(data.subject);
                $('#section').text(data.section);
                $('#att').text(data.attendance_percentage);
                $('#hrs').text(data.weekly_self_study_hours);
                $('#part').text(data.class_participation);
                $('#score').text(data.total_score);
                $('#result').html('');
                lucide.createIcons();
            }, "json");
        }

        function predict() {
            if (!current_id) return;
            $('#result').html('<div class="loading-shimmer">Analyzing performance data...</div>');
            $.ajax({
                url: "save_prediction.php",
                type: "POST",
                data: { id: current_id },
                success: function(res) {
                    $('#result').html(res);
                    lucide.createIcons();
                },
                error: function() {
                    $('#result').html('<div class="error-msg">Prediction failed.</div>');
                }
            });
        }
    </script>
</body>
</html>