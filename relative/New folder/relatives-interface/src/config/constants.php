CREATE TABLE elderly_relatives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    date_of_birth DATE,
    health_status TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE health_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT,
    update_date DATE,
    blood_pressure VARCHAR(20),
    glucose_level VARCHAR(20),
    notes TEXT,
    FOREIGN KEY (relative_id) REFERENCES elderly_relatives(id)
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT,
    appointment_date DATE,
    doctor_name VARCHAR(100),
    notes TEXT,
    FOREIGN KEY (relative_id) REFERENCES elderly_relatives(id)
);

CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT,
    name VARCHAR(100),
    dosage VARCHAR(50),
    frequency VARCHAR(50),
    start_date DATE,
    end_date DATE,
    instructions TEXT,
    FOREIGN KEY (relative_id) REFERENCES elderly_relatives(id)
);

CREATE TABLE financial_advisors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    specialty VARCHAR(100),
    contact_info VARCHAR(100),
    rating DECIMAL(2,1),
    total_reviews INT
);

CREATE TABLE community_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    event_date DATE,
    location VARCHAR(100)
);

CREATE TABLE storytelling (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT,
    story TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relative_id) REFERENCES elderly_relatives(id)
);