<?php
include "connection.php";
session_start();

$error_msg = "";
$success_msg = "";

if (!isset($_SESSION['email_reset'])) {
    header("Location: forgot_password.php");
    exit();
}

if (isset($_POST['submit_password'])) {
    $new_pass = mysqli_real_escape_string($conn, $_POST['new_password']);
    $conf_pass = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $email = $_SESSION['email_reset'];

    if (strlen($new_pass) < 8) {
        $error_msg = "Password must be at least 8 characters long.";
    } elseif ($new_pass !== $conf_pass) {
        $error_msg = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password='$hashed_password' WHERE email='$email'";
        
        if (mysqli_query($conn, $sql)) {
            $success_msg = "Password updated successfully! Redirecting...";
            session_destroy();
            header("refresh:2;url=login.php");
        } else {
            $error_msg = "Error updating password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEd | New Password</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/new_password.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="auth-page">
    <canvas id="particleCanvas"></canvas>

    <div class="auth-container">
        <div class="glass-auth-card">
            <div class="auth-header">
                <div class="logo-container">
                    <div class="logo-circle">
                        <img src="images/logo.png" alt="logo" class="auth-logo">
                    </div>
                </div>
                <h2 class="gradient-header">New Password</h2>
                <p>Ensure your account is secure with a strong password.</p>
            </div>

            <?php if ($error_msg != ""): ?>
                <div class="alert danger">
                    <i data-lucide="alert-circle"></i>
                    <span><?php echo $error_msg; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success_msg != ""): ?>
                <div class="alert success">
                    <i data-lucide="check-circle"></i>
                    <span><?php echo $success_msg; ?></span>
                </div>
            <?php endif; ?>

            <form action="new_password.php" method="POST" class="auth-form">
                <div class="input-group">
                    <label><i data-lucide="lock"></i> New Password</label>
                    <input type="password" name="new_password" placeholder="At least 8 characters" required autofocus>
                </div>

                <div class="input-group">
                    <label><i data-lucide="shield-check"></i> Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat new password" required>
                </div>

                <button type="submit" name="submit_password" class="btn-primary">
                    RESET PASSWORD <i data-lucide="arrow-right"></i>
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Changed your mind? <a href="login.php" class="back-link">Back to Login</a></p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        let particlesArray = [];
        const maxDistance = 150; // Distance para sa linya

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2 + 1;
                this.speedX = (Math.random() - 0.5) * 1.5;
                this.speedY = (Math.random() - 0.5) * 1.5;
            }
            draw() {
                // Tuldok: Match sa Login (Greenish-Blue Tint)
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
            for (let i = 0; i < 100; i++) {
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
                        // Lines: Gi-match nako sa Blue (rgba 52, 152, 219) parehas sa imong Login
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
            canvas.width = innerWidth;
            canvas.height = innerHeight;
            init();
        });

        init();
        animate();
    </script>
</body>
</html>