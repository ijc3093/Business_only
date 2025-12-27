DROP DATABASE IF EXISTS gospel;
CREATE DATABASE gospel;
USE gospel;

-- =========================
-- ROLES
-- =========================
CREATE TABLE role (
    idrole INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO role (name) VALUES
('Admin'),
('Manager'),
('Gospel'),
('Staff');

-- =========================
-- ADMIN USERS
-- =========================
CREATE TABLE admin (
    idadmin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender VARCHAR(50) NOT NULL,
    mobile VARCHAR(50) NOT NULL,
    designation VARCHAR(50) NOT NULL,
    role INT NOT NULL,
    image VARCHAR(100) NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_admin_email (email),
    UNIQUE KEY uq_admin_username (username),
    KEY idx_admin_role (role),

    CONSTRAINT fk_admin_role
        FOREIGN KEY (role) REFERENCES role(idrole)
);

-- =========================
-- PUBLIC USERS
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender VARCHAR(50) NOT NULL,
    mobile VARCHAR(50) NOT NULL,
    designation VARCHAR(50) NOT NULL,
    image VARCHAR(100) NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_users_email (email)
);

-- =========================
-- FEEDBACK
-- =========================
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender VARCHAR(100) NOT NULL,
    receiver VARCHAR(100) NOT NULL,
    title VARCHAR(150) NOT NULL,
    feedbackdata TEXT NOT NULL,
    attachment VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    KEY idx_feedback_receiver (receiver)
);

-- =========================
-- NOTIFICATIONS
-- =========================
CREATE TABLE notification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notiuser VARCHAR(100) NOT NULL,
    notireceiver VARCHAR(100) NOT NULL,
    notitype VARCHAR(100) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- ✅ NEW
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at TIMESTAMP NULL DEFAULT NULL,

    KEY idx_notification_receiver (notireceiver),
    KEY idx_notification_receiver_read (notireceiver, is_read),
    KEY idx_notification_created (created_at)
);

-- =========================
-- DELETED USERS
-- =========================
CREATE TABLE deleteduser (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- OPTIONAL CHECKS
-- =========================
SHOW CREATE TABLE admin;
SHOW CREATE TABLE users;

DESCRIBE admin;
DESCRIBE users;
DESCRIBE notification;

-- (These ALTER statements are optional, because password already VARCHAR(255))
ALTER TABLE admin MODIFY password VARCHAR(255) NOT NULL;
ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL;
