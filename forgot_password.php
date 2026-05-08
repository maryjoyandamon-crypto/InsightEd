<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

include "connection.php";
session_start();

$message = "";
$messageType = "error";

if (isset($_POST['submit_reset'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        $sql = "SELECT * FROM users WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $otp = rand(1000, 9999);
            $_SESSION['otp'] = $otp;
            $_SESSION['email_reset'] = $email;
            $_SESSION['otp_timestamp'] = time();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'maryjoy.andamon@bisu.edu.ph';
                $mail->Password   = 'pksl vtzo hpct owcc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('maryjoy.andamon@bisu.edu.ph', 'InsightEd System');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'InsightEd | Password Reset Code';
                $mail->Body    = "<div style='font-family: Arial; padding: 20px; border: 1px solid #2ecc71; border-radius: 10px;'>
                                    <h2 style='color: #2ecc71;'>Password Reset</h2>
                                    <p>Ang imong verification code kay:</p>
                                    <h1 style='background: #f4f4f4; padding: 20px; text-align: center; letter-spacing: 10px; color: #1e8449;'>$otp</h1>
                                  </div>";

                $mail->send();
                header("Location: verify_otp.php");
                exit();
            } catch (Exception $e) {
                $message = "Mail Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "The email address you entered is not registered.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEd | Reset Access</title>
    <link rel="stylesheet" href="css/forgot.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <canvas id="particleCanvas"></canvas>

    <?php if (!empty($message)): ?>
        <div class="toast <?php echo $messageType; ?>" id="errorToast">
            <span class="toast-icon">⚠️</span>
            <span class="toast-msg"><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <main class="login-wrapper">
        <div class="forgot-box">
            <div class="brand-section">
                <div class="logo-container">
                    <img src="images/logo.png" alt="InsightEd Logo" class="forgot-logo">
                </div>
                <h1 class="forgot-header">Reset Access</h1>
                <p class="forgot-text">Enter your email to receive a secure 4-digit code.</p>
            </div>

            <form action="forgot_password.php" method="POST" autocomplete="off">
                <div class="input-container">
                    <label>Email Address</label>
                    <input type="email" name="email" class="forgot-input" placeholder="example@gmail.com" required>
                </div>
                <button type="submit" name="submit_reset" class="btn-reset">SEND CODE</button>
            </form>

            <div class="footer-links">
                <p>Remembered? <a href="login.php" class="back-link">Back to login</a></p>
            </div>
        </div>
    </main>

    <script>
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
                toast.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    </script>
</body>
</html>