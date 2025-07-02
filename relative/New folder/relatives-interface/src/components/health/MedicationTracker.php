CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100),
    password VARCHAR(255),
    user_role ENUM('family_member', 'elderly'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE elderly_relatives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    date_of_birth DATE,
    health_status TEXT,
    medications TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE health_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT,
    update_date DATE,
    health_metric VARCHAR(100),
    value VARCHAR(100),
    FOREIGN KEY (relative_id) REFERENCES elderly_relatives(id)
);

CREATE TABLE communications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT,
    message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relative_id) REFERENCES elderly_relatives(id)
);

CREATE TABLE financial_assistance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    advisor_id INT,
    request_date DATE,
    status ENUM('pending', 'approved', 'denied'),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (advisor_id) REFERENCES financial_advisors(id)
);

CREATE TABLE life_stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relative_id INT,
    story TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relative_id) REFERENCES elderly_relatives(id)
);

CREATE TABLE community_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    event_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE volunteer_opportunities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    description TEXT,
    location VARCHAR(100),
    date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);