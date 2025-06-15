-- Create the database
CREATE DATABASE IF NOT EXISTS shohochor_db;
USE shohochor_db;

-- Users table for authentication and user profiles
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50),
    profile_image VARCHAR(255),
    bio TEXT,
    role ENUM('admin', 'elderly', 'caregiver', 'family_member', 'healthcare_provider') DEFAULT 'elderly',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(100),
    reset_token VARCHAR(100),
    reset_token_expiry DATETIME,
    remember_token VARCHAR(100),
    login_attempts INT DEFAULT 0,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Emergency contacts table
CREATE TABLE emergency_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    relationship VARCHAR(50),
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    is_emergency TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id)
);

-- Medications table
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    instructions TEXT,
    prescribing_doctor VARCHAR(100),
    pharmacy VARCHAR(100),
    refill_reminder_days INT DEFAULT 7,
    refill_count INT DEFAULT 0,
    morning TINYINT(1) DEFAULT 0,
    afternoon TINYINT(1) DEFAULT 0,
    evening TINYINT(1) DEFAULT 0,
    night TINYINT(1) DEFAULT 0,
    with_food TINYINT(1) DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id)
);

-- Medication logs table
CREATE TABLE medication_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medication_id INT NOT NULL,
    user_id INT NOT NULL,
    scheduled_time DATETIME NOT NULL,
    taken_time DATETIME,
    status ENUM('pending', 'taken', 'skipped', 'missed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (medication_id) REFERENCES medications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (medication_id),
    INDEX (scheduled_time)
);

-- Health metrics table
CREATE TABLE health_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    metric_type ENUM('blood_pressure', 'heart_rate', 'blood_sugar', 'oxygen_level', 'weight', 'temperature', 'sleep', 'steps', 'other') NOT NULL,
    systolic INT,
    diastolic INT,
    pulse INT,
    blood_sugar DECIMAL(5,1),
    oxygen_level DECIMAL(4,1),
    weight DECIMAL(5,2),
    temperature DECIMAL(4,1),
    sleep_hours DECIMAL(4,1),
    steps INT,
    other_value VARCHAR(50),
    notes TEXT,
    recorded_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (metric_type),
    INDEX (recorded_at)
);

-- Appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    doctor_name VARCHAR(100),
    facility_name VARCHAR(100),
    appointment_type ENUM('doctor', 'test', 'therapy', 'other') DEFAULT 'doctor',
    date DATE NOT NULL,
    time TIME NOT NULL,
    duration INT DEFAULT 30, -- in minutes
    location TEXT,
    is_video_call TINYINT(1) DEFAULT 0,
    video_call_link VARCHAR(255),
    reminder_sent TINYINT(1) DEFAULT 0,
    notes TEXT,
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (date)
);

-- Social connections table
CREATE TABLE social_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    connection_name VARCHAR(100) NOT NULL,
    relationship VARCHAR(50),
    phone VARCHAR(20),
    email VARCHAR(100),
    profile_image VARCHAR(255),
    connection_type ENUM('family', 'friend', 'caregiver', 'healthcare_provider', 'other') DEFAULT 'family',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id)
);

-- Scheduled calls table
CREATE TABLE scheduled_calls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    connection_id INT NOT NULL,
    scheduled_time DATETIME NOT NULL,
    duration INT DEFAULT 30, -- in minutes
    call_type ENUM('voice', 'video') DEFAULT 'voice',
    reminder_sent TINYINT(1) DEFAULT 0,
    status ENUM('scheduled', 'completed', 'missed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (connection_id) REFERENCES social_connections(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (connection_id),
    INDEX (scheduled_time)
);

-- Communities table
CREATE TABLE communities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    category ENUM('hobbies', 'health', 'local', 'religion', 'technology', 'other') DEFAULT 'other',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Community membership table
CREATE TABLE community_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    community_id INT NOT NULL,
    role ENUM('member', 'moderator', 'admin') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, community_id),
    INDEX (user_id),
    INDEX (community_id)
);

-- Community events table
CREATE TABLE community_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    community_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    duration INT DEFAULT 60, -- in minutes
    location TEXT,
    is_virtual TINYINT(1) DEFAULT 0,
    virtual_link VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (community_id),
    INDEX (event_date)
);

-- Life stories table
CREATE TABLE life_stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    is_public TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id)
);

-- Consultation requests table
CREATE TABLE consultation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    organization VARCHAR(100),
    primary_need VARCHAR(100) NOT NULL,
    specific_needs TEXT,
    preferred_date DATE,
    preferred_time VARCHAR(50),
    status ENUM('pending', 'contacted', 'scheduled', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (status),
    INDEX (preferred_date)
);

-- Email queue table
CREATE TABLE email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(100) NOT NULL,
    to_name VARCHAR(100),
    from_email VARCHAR(100) NOT NULL,
    from_name VARCHAR(100),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    headers TEXT,
    attachments TEXT,
    priority TINYINT(1) DEFAULT 3, -- 1 highest, 5 lowest
    max_attempts TINYINT(1) DEFAULT 3,
    attempts TINYINT(1) DEFAULT 0,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    last_attempt_at DATETIME,
    sent_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (priority)
);

-- News articles table
CREATE TABLE news_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('health', 'local', 'technology', 'lifestyle', 'world', 'other') DEFAULT 'health',
    image VARCHAR(255),
    source VARCHAR(100),
    author VARCHAR(100),
    published_at DATE NOT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (category),
    INDEX (published_at),
    INDEX (is_featured)
);

-- Health tips table
CREATE TABLE health_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('general', 'nutrition', 'exercise', 'mental', 'sleep', 'other') DEFAULT 'general',
    source VARCHAR(100),
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (category),
    INDEX (is_featured)
);

-- Emergency alerts table
CREATE TABLE emergency_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alert_type ENUM('fall', 'medical', 'sos', 'other') NOT NULL,
    location TEXT,
    status ENUM('active', 'resolved', 'false_alarm') DEFAULT 'active',
    resolved_by INT,
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (user_id),
    INDEX (alert_type),
    INDEX (status)
);

-- User settings table
CREATE TABLE user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    text_size VARCHAR(10) DEFAULT 'medium',
    high_contrast TINYINT(1) DEFAULT 0,
    voice_assistance TINYINT(1) DEFAULT 1,
    email_notifications TINYINT(1) DEFAULT 1,
    sms_notifications TINYINT(1) DEFAULT 0,
    medication_reminders TINYINT(1) DEFAULT 1,
    appointment_reminders TINYINT(1) DEFAULT 1,
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Mood tracking table
CREATE TABLE mood_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mood ENUM('very_happy', 'happy', 'neutral', 'sad', 'very_sad') NOT NULL,
    notes TEXT,
    recorded_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (recorded_at)
);

-- Insert sample admin user
INSERT INTO users (first_name, last_name, email, password, role, email_verified) 
VALUES ('Admin', 'User', 'admin@shohochor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Insert sample health tips
INSERT INTO health_tips (title, content, category, source, is_featured) VALUES
('Stay Hydrated Daily', 'Drink at least 8 glasses of water each day to maintain proper hydration, especially important for elderly individuals.', 'general', 'World Health Organization', 1),
('Daily Walking Benefits', 'Just 30 minutes of walking each day can improve cardiovascular health, strengthen bones, and enhance mood.', 'exercise', 'American Heart Association', 1),
('Mind Exercises', 'Regular puzzles, crosswords, or reading can help maintain cognitive function and reduce risk of dementia.', 'mental', 'Alzheimer\'s Association', 0);

-- Insert sample communities
INSERT INTO communities (name, description, category) VALUES
('Local Garden Club', 'Connect with other garden enthusiasts in your community to share tips and experiences.', 'hobbies'),
('Senior Technology Group', 'Learn about technology together in a friendly, supportive environment.', 'technology'),
('Health & Wellness Circle', 'Share health tips and support each other on your wellness journey.', 'health');