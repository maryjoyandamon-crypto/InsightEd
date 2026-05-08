<?php
session_start();
$error_msg = "";

if (!isset($_SESSION['otp'])) {
    header("Location: forgot_password.php");
    exit();
}

if (isset($_POST['verify_code'])) {
    $user_otp = $_POST['digit1'] . $_POST['digit2'] . $_POST['digit3'] . $_POST['digit4'];

    if ($user_otp == $_SESSION['otp']) {
        $_SESSION['verified'] = true; 
        header("Location: new_password.php");
        exit();
    } else {
        $error_msg = "Invalid verification code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEd | Verification</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/verify.css">
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <?php if ($error_msg != ""): ?>
        <div class="toast error" id="errorToast">
            <span class="toast-icon">⚠️</span>
            <span class="toast-msg"><?php echo $error_msg; ?></span>
        </div>
    <?php endif; ?>

    <main class="login-wrapper">
        <section class="forgot-box">
            <div class="brand-section">
                <div class="logo-container">
                    <img src="images/logo.png" alt="logo" class="forgot-logo">
                </div>
                <h1 class="forgot-header">Verification</h1>
                <p class="forgot-text">Sent to: <b style="color: #2ecc71;"><?php echo $_SESSION['email_reset']; ?></b></p>
            </div>

            <form action="verify_otp.php" method="POST" autocomplete="off">
                <div class="otp-wrapper">
                    <input type="text" name="digit1" maxlength="1" class="otp-circle" required autofocus oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    <input type="text" name="digit2" maxlength="1" class="otp-circle" required oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    <input type="text" name="digit3" maxlength="1" class="otp-circle" required oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    <input type="text" name="digit4" maxlength="1" class="otp-circle" required oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                </div>
                <button type="submit" name="verify_code" class="btn-reset">VERIFY CODE</button>
            </form>
            
            <div class="footer-links">
                <p>Wrong email? <a href="forgot_password.php" class="back-link">Change Email</a></p>
            </div>
        </section>
    </main>

    <script>
        const inputs = document.querySelectorAll('.otp-circle');
        inputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < inputs.length - 1) inputs[index + 1].focus();
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) inputs[index - 1].focus();
            });
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
        window.addEventListener('resize', () => { canvas.width = innerWidth; canvas.height = innerHeight; init(); });
        init(); animate();

        const toast = document.getElementById('errorToast');
        if (toast) {
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translate(0, -20px)';
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    </script>
</body>
</html>