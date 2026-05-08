<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_img'])) {
    $file = $_FILES['profile_img'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $fileSize = $file['size'];
    
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png');

    if (in_array($fileExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 5000000) { 
                $fileNameNew = "profile_" . $teacher_id . "_" . time() . "." . $fileExt;
                $fileDestination = 'images/' . $fileNameNew;

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    $update_sql = "UPDATE users SET profile_picture = '$fileNameNew' WHERE teacher_id = '$teacher_id'";
                    if (mysqli_query($conn, $update_sql)) {
                        $_SESSION['profile_pic'] = $fileNameNew;
                        $success = "Profile updated successfully!";
                    }
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "File too large (Max 5MB).";
            }
        } else {
            $error = "Upload error occurred.";
        }
    } else {
        $error = "Invalid file type.";
    }
}

$query = mysqli_query($conn, "SELECT * FROM users WHERE teacher_id = '$teacher_id'");
$user = mysqli_fetch_assoc($query);
$profile_pic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_avatar.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings | InsightEd</title>
    <link rel="shortcut icon" href="images/favicon.ico?v=1.1" type="image/x-icon">
    <link rel="stylesheet" href="css/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="profile-page">
    <canvas id="particleCanvas"></canvas>

    <div class="profile-container">
        <div class="glass-profile-card">
            <div class="profile-header">
                <a href="dashboard.php" class="back-link"><i data-lucide="arrow-left"></i> Dashboard</a>
                <h2>Teacher <span>Profile</span></h2>
            </div>

            <?php if($success): ?>
                <div class="alert success"><i data-lucide="check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert danger"><i data-lucide="alert-triangle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="avatar-upload-section">
                <div class="avatar-holder">
                    <img src="images/<?php echo htmlspecialchars($profile_pic); ?>?t=<?php echo time(); ?>" alt="Profile" class="main-avatar">
                    <form action="profile.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                        <label for="profile_img" class="upload-badge">
                            <i data-lucide="camera"></i>
                        </label>
                        <input type="file" name="profile_img" id="profile_img" onchange="this.form.submit()" style="display: none;">
                    </form>
                </div>
                <h3><?php echo htmlspecialchars($user['fname'] . " " . $user['lname']); ?></h3>
                <p class="role-text">Faculty Member</p>
            </div>

            <div class="profile-details-grid">
                <div class="detail-item">
                    <label><i data-lucide="book-open"></i> Assigned Subject</label>
                    <div class="value-box"><?php echo htmlspecialchars($user['subject'] ?? 'General'); ?></div>
                </div>
                <div class="detail-item">
                    <label><i data-lucide="mail"></i> Email Address</label>
                    <div class="value-box"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></div>
                </div>
                <div class="detail-item">
                    <label><i data-lucide="fingerprint"></i> Teacher ID</label>
                    <div class="value-box"><?php echo htmlspecialchars($user['teacher_id']); ?></div>
                </div>
            </div>

            <div class="profile-footer">
                <p>Manage your account settings through your department head.</p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        let particlesArray = [];
        const maxDistance = 150;

        function initCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }

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
            initCanvas();
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
                for (let j = i + 1; j < particlesArray.length; j++) {
                    const dx = particlesArray[i].x - particlesArray[j].x;
                    const dy = particlesArray[i].y - particlesArray[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    if (distance < maxDistance) {
                        ctx.strokeStyle = `rgba(52, 152, 219, ${1 - distance/maxDistance})`;
                        ctx.lineWidth = 0.6;
                        ctx.beginPath();
                        ctx.moveTo(particlesArray[i].x, particlesArray[i].y);
                        ctx.lineTo(particlesArray[j].x, particlesArray[j].y);
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(animate);
        }

        window.addEventListener('resize', init);
        init();
        animate();
    </script>
</body>
</html>