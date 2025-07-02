CREATE TABLE relatives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    date_of_birth DATE,
    health_status TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE health_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT NOT NULL,
    update_date DATE,
    blood_pressure VARCHAR(20),
    glucose_level VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relative_id) REFERENCES relatives(id)
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT NOT NULL,
    appointment_date DATE,
    doctor_name VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relative_id) REFERENCES relatives(id)
);

CREATE TABLE communications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT NOT NULL,
    message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relative_id) REFERENCES relatives(id)
);

CREATE TABLE financial_assistance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT NOT NULL,
    budget DECIMAL(10, 2),
    assistance_needed BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relative_id) REFERENCES relatives(id)
);

CREATE TABLE community_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100),
    event_date DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);