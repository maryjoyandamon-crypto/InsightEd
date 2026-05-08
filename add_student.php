<?php
session_start();
include 'connection.php';

$success = false;
$error_msg = "";

if (isset($_POST['submit'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    
    if (!preg_match("/^[a-zA-Z. ]*$/", $fullname)) {
        $error_msg = "Name can only contain letters, dots, and spaces.";
    } else {
        $age = mysqli_real_escape_string($conn, $_POST['age']);
        $school_year   = mysqli_real_escape_string($conn, $_POST['school_year']); 
        $subject       = mysqli_real_escape_string($conn, $_POST['subject']);
        $section       = mysqli_real_escape_string($conn, $_POST['section']);
        $hours         = mysqli_real_escape_string($conn, $_POST['hours']);
        $attendance    = mysqli_real_escape_string($conn, $_POST['attendance']);
        $participation = mysqli_real_escape_string($conn, $_POST['participation']);
        $score         = mysqli_real_escape_string($conn, $_POST['score']);
        $grade         = mysqli_real_escape_string($conn, $_POST['grade']);

        $query = "INSERT INTO students (fullname, age, subject, section, school_year, weekly_self_study_hours, attendance_percentage, class_participation, total_score, grade) 
                  VALUES ('$fullname', '$age', '$subject', '$section', '$school_year', '$hours', '$attendance', '$participation', '$score', '$grade')";

        if (mysqli_query($conn, $query)) {
            $success = true;
        } else {
            $error_msg = mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student | InsightEd</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_student.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Register Student</h1>
                    <p>Input student metrics for performance tracking.</p>
                </div>
            </header>

            <div class="form-container-glass">
                <div class="form-header-box">
                    <div class="header-info">
                        <i data-lucide="user-plus" class="header-icon"></i>
                        <div>
                            <h3>Student Information</h3>
                            <p>Metrics are used for AI-driven performance prediction.</p>
                        </div>
                    </div>
                </div>

                <form action="" method="POST" class="styled-form">
                    <div class="input-grid">
                        <div class="form-group full-width">
                            <label><i data-lucide="user"></i> Full Name</label>
                            <input type="text" name="fullname" required pattern="[a-zA-Z. ]+" placeholder="Ex: Juan Q. Dela Cruz">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="calendar"></i> School Year</label>
                            <select name="school_year" required>
                                <option value="2026">2026-2027</option>
                                <option value="2025">2025-2026</option>
                                <option value="2024">2024-2025</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="hash"></i> Age</label>
                            <input type="number" name="age" required min="1" max="99" placeholder="15">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="book-open"></i> Subject</label>
                            <input type="text" name="subject" required placeholder="Ex: English">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="layers"></i> Section</label>
                            <input type="text" name="section" required placeholder="Ex: Grade 7 - Aphrodite">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="clock"></i> Study Hours / Week</label>
                            <input type="number" step="0.1" name="hours" id="hours" required min="0" max="40" oninput="calculateTotalScore()" placeholder="0.0">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="check-circle"></i> Attendance %</label>
                            <input type="number" step="0.1" name="attendance" id="attendance" required min="0" max="100" oninput="calculateTotalScore()" placeholder="0.0">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="message-square"></i> Participation (0-10)</label>
                            <input type="number" step="0.1" name="participation" id="participation" required min="0" max="10" oninput="calculateTotalScore()" placeholder="0.0">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="bar-chart"></i> Total Score (Auto)</label>
                            <input type="number" step="0.1" name="score" id="score" class="readonly-input" readonly required placeholder="Calculated...">
                        </div>

                        <div class="form-group full-width">
                            <label><i data-lucide="brain"></i> AI Predicted Grade</label>
                            <input type="text" name="grade" id="predicted_grade" class="readonly-input" readonly placeholder="Waiting for data...">
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="students.php" class="btn-cancel">Discard</a>
                        <button type="submit" name="submit" class="btn-save">
                            <span>Save Student Record</span>
                            <i data-lucide="save"></i>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        <?php if($success): ?>
            Swal.fire({
                title: 'Success!',
                text: 'Student Record Added!',
                icon: 'success',
                confirmButtonColor: '#2ecc71',
                background: '#121212',
                color: '#fff'
            }).then(() => {
                window.location.href = 'students.php';
            });
        <?php endif; ?>

        <?php if($error_msg): ?>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo $error_msg; ?>',
                icon: 'error',
                confirmButtonColor: '#e74c3c',
                background: '#121212',
                color: '#fff'
            });
        <?php endif; ?>

        function calculateTotalScore() {
            const hours = parseFloat(document.getElementById('hours').value) || 0;
            const attendance = parseFloat(document.getElementById('attendance').value) || 0;
            const participation = parseFloat(document.getElementById('participation').value) || 0;

            let scoreFromHours = (hours / 40) * 50;
            let scoreFromAttendance = (attendance / 100) * 30;
            let scoreFromParticipation = (participation / 10) * 20;
            let total = scoreFromHours + scoreFromAttendance + scoreFromParticipation;
            
            document.getElementById('score').value = total.toFixed(1);
            const gradeField = document.getElementById('predicted_grade');

            if (hours > 0 || attendance > 0 || participation > 0) {
                const studentData = {
                    "weekly_self_study_hours": hours,
                    "attendance_percentage": attendance,
                    "class_participation": participation,
                    "total_score": parseFloat(total.toFixed(1))
                };

                $.ajax({
                    url: "https://insighted-mcck.onrender.com",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(studentData),
                    success: function(response) {
                        gradeField.value = response.predicted_grade;
                    },
                    error: function() {
                        gradeField.value = "AI Server Offline";
                    }
                });
            }
        }

        // Particle Logic
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

        function init() {
            particlesArray = [];
            for (let i = 0; i < 100; i++) particlesArray.push(new Particle());
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particlesArray.forEach(p => { 
                p.update(); 
                p.draw(); 
            });
            requestAnimationFrame(animate);
        }

        init();
        animate();
    </script>
</body>
</html>