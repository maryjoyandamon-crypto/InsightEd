<?php
session_start();
include 'connection.php';

$message = "";
$messageType = "error"; 

if (isset($_SESSION['login_success'])) {
    $message = $_SESSION['login_success'];
    $messageType = "success"; 
    unset($_SESSION['login_success']); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format!";
    } else {
        $sql = "SELECT * FROM users WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            if ($row['status'] === 'Pending') {
                $message = "Your account is still pending approval.";
            } elseif ($row['status'] === 'Deactivated') {
                $message = "This account has been deactivated.";
            } elseif ($row['status'] === 'Approved') {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['teacher_id'] = $row['teacher_id'];
                    $_SESSION['fname'] = $row['fname'];
                    $_SESSION['subject'] = $row['subject'];
                    $_SESSION['role'] = $row['role']; 

                    if ($row['role'] === 'Admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: dashboard.php"); 
                    }
                    exit();
                } else { 
                    $message = "Incorrect password!"; 
                }
            } else {
                $message = "Account status unknown. Please contact admin.";
            }
        } else { 
            $message = "No account found with that email!"; 
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEd | Secure Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <?php if (!empty($message)): ?>
        <div class="toast <?php echo $messageType; ?>" id="errorToast">
            <span class="toast-icon"><?php echo ($messageType === 'success' ? '✅' : '⚠️'); ?></span>
            <span class="toast-msg"><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <main class="login-wrapper">
        <div class="login-card">
            <div class="brand-section">
                <div class="logo-container">
                    <img src="images/logo.png" alt="InsightEd Logo" class="brand-logo">
                </div>
                <h1>InsightEd</h1>
                <p>Teacher Gateway | Data Insights</p>
            </div>

            <form action="login.php" method="post" id="loginForm" autocomplete="off">
                <div class="input-field">
                    <label>Email Address</label>
                    <input type="email" name="email" id="email" placeholder="email@gmail.com" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="input-field">
                    <div class="field-header">
                        <label>Password</label>
                        <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                    </div>
                    <div class="password-wrapper" style="position: relative; display: flex; align-items: center;">
                        <input type="password" name="password" id="password" placeholder="••••••••" required style="width: 100%;">
                        <i data-lucide="eye" id="toggleEye" style="position: absolute; right: 15px; cursor: pointer; color: white; opacity: 0.7; width: 18px;"></i>
                    </div>
                </div>

                <button type="submit" class="login-btn">LOGIN</button>
            </form>

            <div class="card-footer">
                <p>New Teacher? <a href="register.php">Request Access</a></p>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        const toggleEye = document.getElementById('toggleEye');
        const passwordInput = document.getElementById('password');
        toggleEye.addEventListener('click', () => {
            const isPass = passwordInput.type === 'password';
            passwordInput.type = isPass ? 'text' : 'password';
            toggleEye.setAttribute('data-lucide', isPass ? 'eye-off' : 'eye');
            lucide.createIcons();
        });

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
                this.speedX = (Math.random() - 0.5) * 1.5;
                this.speedY = (Math.random() - 0.5) * 1.5;
            }
            draw() {
                ctx.fillStyle = 'rgba(46, 204, 113, 0.5)';
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
            for (let i = 0; i < 100; i++) { particlesArray.push(new Particle()); }
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
            canvas.width = innerWidth; canvas.height = innerHeight; init();
        });

        init(); animate();

        const toast = document.getElementById('errorToast');
        if (toast) {
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    </script>
</body>
</html>