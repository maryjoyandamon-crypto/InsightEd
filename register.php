<?php
include 'connection.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $mname = mysqli_real_escape_string($conn, $_POST['mname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $teacher_id = mysqli_real_escape_string($conn, $_POST['teacher_id']);
    $school_name = mysqli_real_escape_string($conn, $_POST['school_name']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $name_pattern = "/^[a-zA-Z.\s]+$/";

    $has_uppercase = preg_match('@[A-Z]@', $password);
    $has_lowercase = preg_match('@[a-z]@', $password);
    $has_number    = preg_match('@[0-9]@', $password);
    $has_special   = preg_match('@[^\w]@', $password);

    if (!preg_match($name_pattern, $fname) || !preg_match($name_pattern, $lname)) {
        $message = "Numbers and special characters are not allowed in names.";
        $messageType = "error";
    } elseif (strlen($password) < 12 || !$has_uppercase || !$has_lowercase || !$has_number || !$has_special) {
        // Weak Password Check
        $message = "Weak Password! Use 12+ characters with uppercase, lowercase, numbers, and symbols.";
        $messageType = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $messageType = "error";
    } else {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR teacher_id = ?");
        $check_stmt->bind_param("ss", $email, $teacher_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Email or Teacher ID already registered!";
            $messageType = "error";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (fname, mname, lname, teacher_id, school_name, subject, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Teacher', 'Pending')");
            $stmt->bind_param("ssssssss", $fname, $mname, $lname, $teacher_id, $school_name, $subject, $email, $hashed_password);

            if ($stmt->execute()) {
                session_start();
                $_SESSION['login_success'] = "Request sent! Please wait for Admin approval.";
                header("Location: login.php");
                exit();
            } else {
                $message = "Registration failed. Please try again.";
                $messageType = "error";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEd | Teacher Registration</title>
    <link rel="stylesheet" href="css/register.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <main class="container-register">
        <div class="register-card">
            <div class="brand-area">
                <div class="logo-container">
                    <img src="images/logo.png" alt="Logo" class="logo">
                </div>
                <h1>InsightEd</h1>
                <p>Junior High School Analytics Portal</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $messageType; ?>" id="alertBox">
                    <span><?php echo ($messageType == 'error' ? '⚠️' : '✅'); ?> <?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" id="regForm" autocomplete="off">
                <div class="row">
                    <div class="input-group">
                        <label>First Name</label>
                        <input type="text" name="fname" required value="<?php echo isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <label>Middle Name</label>
                        <input type="text" name="mname" value="<?php echo isset($_POST['mname']) ? htmlspecialchars($_POST['mname']) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <label>Last Name</label>
                        <input type="text" name="lname" required value="<?php echo isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : ''; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="input-group">
                        <label>Teacher ID</label>
                        <input type="text" name="teacher_id" placeholder="T-2026-01" required value="<?php echo isset($_POST['teacher_id']) ? htmlspecialchars($_POST['teacher_id']) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <label>Subject Specialization</label>
                        <select name="subject" required>
                            <option value="" disabled <?php echo !isset($_POST['subject']) ? 'selected' : ''; ?>>Select Subject</option>
                            <option value="Mathematics" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                            <option value="Science" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Science') ? 'selected' : ''; ?>>Science</option>
                            <option value="English" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'English') ? 'selected' : ''; ?>>English</option>
                            <option value="Filipino" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Filipino') ? 'selected' : ''; ?>>Filipino</option>
                            <option value="Araling Panlipunan" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Araling Panlipunan') ? 'selected' : ''; ?>>Araling Panlipunan</option>
                            <option value="MAPEH" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'MAPEH') ? 'selected' : ''; ?>>MAPEH</option>
                            <option value="TLE" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'TLE') ? 'selected' : ''; ?>>TLE</option>
                            <option value="EsP" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'EsP') ? 'selected' : ''; ?>>EsP</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label>School Name</label>
                    <input type="text" name="school_name" placeholder="Enter School Name" required value="<?php echo isset($_POST['school_name']) ? htmlspecialchars($_POST['school_name']) : ''; ?>">
                </div>

                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="email@gmail.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="row">
                    <div class="input-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="pass1" required placeholder="Min. 12 characters">
                            <i data-lucide="eye" class="toggle-password" onclick="togglePass('pass1', this)"></i>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Confirm</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="pass2" required>
                            <i data-lucide="eye" class="toggle-password" onclick="togglePass('pass2', this)"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="register-btn">SUBMIT REQUEST</button>
            </form>

            <div class="login-link">
                <p>Already a member? <a href="login.php">Log In</a></p>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        function togglePass(id, icon) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = "password";
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let particlesArray = [];
        const maxDistance = 120;

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
                if (this.x > canvas.width || this.x < 0) this.speedX *= -1;
                if (this.y > canvas.height || this.y < 0) this.speedY *= -1;
                this.x += this.speedX;
                this.y += this.speedY;
            }
        }

        function init() {
            particlesArray = [];
            let n = (canvas.width * canvas.height) / 12000;
            for (let i = 0; i < n; i++) { particlesArray.push(new Particle()); }
        }

        function animate() {
            ctx.clearRect(0, 0, innerWidth, innerHeight);
            for (let i = 0; i < particlesArray.length; i++) {
                particlesArray[i].update();
                particlesArray[i].draw();
                for (let j = i; j < particlesArray.length; j++) {
                    const dx = particlesArray[i].x - particlesArray[j].x;
                    const dy = particlesArray[i].y - particlesArray[j].y;
                    const dist = Math.sqrt(dx*dx + dy*dy);
                    if (dist < maxDistance) {
                        ctx.strokeStyle = `rgba(52, 152, 219, ${1 - dist/maxDistance})`;
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
            canvas.width = innerWidth; 
            canvas.height = innerHeight; 
            init(); 
        });

        init(); animate();

        const alertBox = document.getElementById('alertBox');
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>