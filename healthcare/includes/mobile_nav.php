
<nav class="mobile-navigation">
    <a href="index.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="patients.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'patients.php' ? 'active' : ''; ?>">
        <i class="fas fa-user-injured"></i>
        <span>Patients</span>
    </a>
    <a href="appointments.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">
        <i class="fas fa-calendar-check"></i>
        <span>Appointments</span>
    </a>
    <a href="health-records.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'health-records.php' ? 'active' : ''; ?>">
        <i class="fas fa-heartbeat"></i>
        <span>Records</span>
    </a>
    <a href="profile.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
        <i class="fas fa-user-md"></i>
        <span>Profile</span>
    </a>
</nav>

<div id="mobile-google-translate"></div>