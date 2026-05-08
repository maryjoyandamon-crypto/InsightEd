<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$my_subject = $_SESSION['subject'] ?? 'General';
$tid = $_SESSION['teacher_id'];
$class_query = mysqli_query($conn, "SELECT * FROM classes WHERE teacher_id = '$tid' ORDER BY school_year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | InsightEd</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/settings.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="settings-body">
    <canvas id="particleCanvas"></canvas>

    <div class="main-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="content-area">
            <header class="settings-header-glass">
                <div class="header-info">
                    <h1>Class <span>Management</span></h1>
                    <p>Organize your academic years and sections</p>
                </div>
                <div class="dept-badge"><?php echo strtoupper($my_subject); ?> SUBJECT</div>
            </header>

            <div class="settings-grid">
                <aside class="glass-card form-section">
                    <h3 id="form_title"><i data-lucide="plus-circle"></i> Add New Class</h3>
                    <form action="process_settings.php" method="POST">
                        <input type="hidden" name="class_id" id="class_id">
                        
                        <div class="input-group-glass">
                            <label>School Year</label>
                            <select name="school_year" id="sy_input" required>
                                <option value="2025-2026">2025-2026</option>
                                <option value="2024-2025">2024-2025</option>
                            </select>
                        </div>
                        <div class="input-group-glass">
                            <label>Section Name</label>
                            <input type="text" name="section" id="section_input" placeholder="e.g. Grade 7 - Aphrodite" required>
                        </div>
                        <div class="input-group-glass">
                            <label>Subject</label>
                            <input type="text" name="subject" id="subject_input" placeholder="e.g. English" required>
                        </div>
                        
                        <button type="submit" class="btn-primary-glass" id="submit_btn">Save Record</button>
                        <button type="button" class="btn-cancel-glass" id="cancel_btn" style="display:none;" onclick="resetForm()">Cancel Edit</button>
                    </form>
                </aside>

                <section class="glass-card table-section">
                    <div class="card-header">
                        <h3><i data-lucide="list"></i> Active Class List</h3>
                        <span class="count-pill"><?php echo mysqli_num_rows($class_query); ?> Total</span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="glass-table">
                            <thead>
                                <tr>
                                    <th>School Year</th>
                                    <th>Section</th>
                                    <th>Subject</th>
                                    <th style="text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($class_query) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($class_query)): ?>
                                    <tr>
                                        <td><strong><?php echo $row['school_year']; ?></strong></td>
                                        <td><?php echo $row['section']; ?></td>
                                        <td><span class="sub-tag"><?php echo $row['subject']; ?></span></td>
                                        <td class="action-cell">
                                            <button class="action-btn edit" title="Edit" onclick="editClass('<?php echo $row['id']; ?>', '<?php echo $row['school_year']; ?>', '<?php echo $row['section']; ?>', '<?php echo $row['subject']; ?>')"><i data-lucide="edit-3"></i></button>
                                            <button class="action-btn delete" title="Delete" onclick="confirmDelete('<?php echo $row['id']; ?>', '<?php echo $row['subject']; ?>')"><i data-lucide="trash-2"></i></button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="empty-row">No classes found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        function confirmDelete(id, sub) {
            if(confirm(`Sigurado ka? Ang pag-delete sa "${sub}" mahimong maka-apekto sa ubang data.`)) {
                window.location.href = `delete_class.php?id=${id}`;
            }
        }

        function editClass(id, sy, section, subject) {
            document.getElementById('class_id').value = id;
            document.getElementById('sy_input').value = sy;
            document.getElementById('section_input').value = section;
            document.getElementById('subject_input').value = subject;
            document.getElementById('form_title').innerHTML = "<i data-lucide='edit-3'></i> Edit Class";
            document.getElementById('submit_btn').innerText = "Update Record";
            document.getElementById('cancel_btn').style.display = "block";
            lucide.createIcons();
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        function resetForm() {
            document.getElementById('class_id').value = "";
            document.getElementById('section_input').value = "";
            document.getElementById('subject_input').value = "";
            document.getElementById('form_title').innerHTML = "<i data-lucide='plus-circle'></i> Add New Class";
            document.getElementById('submit_btn').innerText = "Save Record";
            document.getElementById('cancel_btn').style.display = "none";
            lucide.createIcons();
        }

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
    </script>
</body>
</html>