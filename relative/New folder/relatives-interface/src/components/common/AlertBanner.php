CREATE TABLE relatives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    age INT,
    medical_history TEXT,
    medications TEXT,
    emergency_contact VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE health_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT NOT NULL,
    update_date DATE,
    health_status TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE communications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT NOT NULL,
    message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE financial_assistance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT NOT NULL,
    program_name VARCHAR(100),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE community_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100),
    description TEXT,
    event_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE storytelling (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT NOT NULL,
    story TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);