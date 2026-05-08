<aside class="sidebar">
    <div class="sidebar-header">
        <img src="images/logo.png" alt="InsightEd Logo" class="side-logo">
        <h2>InsightEd</h2>
    </div>
    
    <nav class="nav-menu">
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        
        <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>

        <a href="students.php" class="nav-link <?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
            <i data-lucide="users"></i>
            <span>Students</span>
        </a>

        <a href="predict.php" class="nav-link <?php echo ($current_page == 'predict.php') ? 'active' : ''; ?>">
            <i data-lucide="brain-circuit"></i>
            <span>Predict</span>
        </a>

        <a href="reports.php" class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
            <i data-lucide="file-bar-chart"></i>
            <span>Reports</span>
        </a>

        <div class="nav-divider"></div>

        <a href="settings.php" class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
            <i data-lucide="settings"></i>
            <span>Settings</span>
        </a>

        <a href="logout.php" class="nav-link logout">
            <i data-lucide="log-out"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>

<script>
    lucide.createIcons();
</script>