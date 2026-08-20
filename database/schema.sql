CREATE DATABASE IF NOT EXISTS complaint_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE complaint_system;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_roles_name UNIQUE (name)
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'blocked') NOT NULL DEFAULT 'pending',
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_users_status (status),
    INDEX idx_users_role_id (role_id)
) ENGINE=InnoDB;

CREATE TABLE complaint_reasons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    priority ENUM('LOW', 'MEDIUM', 'HIGH') NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_complaint_reasons_name UNIQUE (name),
    INDEX idx_complaint_reasons_active (active),
    INDEX idx_complaint_reasons_priority (priority)
) ENGINE=InnoDB;

CREATE TABLE complaints (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    reason_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('LOW', 'MEDIUM', 'HIGH') NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved', 'closed', 'rejected') NOT NULL DEFAULT 'pending',
    assigned_to BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_complaints_user FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_complaints_reason FOREIGN KEY (reason_id) REFERENCES complaint_reasons (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_complaints_assignee FOREIGN KEY (assigned_to) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_complaints_user_id (user_id),
    INDEX idx_complaints_reason_id (reason_id),
    INDEX idx_complaints_status (status),
    INDEX idx_complaints_priority (priority),
    INDEX idx_complaints_assigned_to (assigned_to),
    INDEX idx_complaints_created_at (created_at)
) ENGINE=InnoDB;

CREATE TABLE complaint_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(150) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_complaint_attachments_stored_name UNIQUE (stored_name),
    CONSTRAINT fk_attachments_complaint FOREIGN KEY (complaint_id) REFERENCES complaints (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_attachments_complaint_id (complaint_id)
) ENGINE=InnoDB;

CREATE TABLE complaint_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_status ENUM('pending', 'in_progress', 'resolved', 'closed', 'rejected') NULL,
    new_status ENUM('pending', 'in_progress', 'resolved', 'closed', 'rejected') NULL,
    assigned_from BIGINT UNSIGNED NULL,
    assigned_to BIGINT UNSIGNED NULL,
    performed_by BIGINT UNSIGNED NOT NULL,
    description VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_complaint FOREIGN KEY (complaint_id) REFERENCES complaints (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_history_assigned_from FOREIGN KEY (assigned_from) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_history_assigned_to FOREIGN KEY (assigned_to) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_history_performed_by FOREIGN KEY (performed_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_history_complaint_id (complaint_id),
    INDEX idx_history_performed_by (performed_by),
    INDEX idx_history_created_at (created_at),
    INDEX idx_history_action (action)
) ENGINE=InnoDB;

INSERT INTO roles (name, description)
VALUES
    ('user', 'Standard complaint system user'),
    ('admin', 'Complaint system administrator')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO complaint_reasons (name, description, priority)
VALUES
    ('Policy Violation', 'A complaint about a suspected policy violation.', 'HIGH'),
    ('General Inquiry', 'A general policy-related question or inquiry.', 'LOW'),
    ('Documentation Issue', 'A complaint about missing or incorrect documentation.', 'MEDIUM')
ON DUPLICATE KEY UPDATE description = VALUES(description), priority = VALUES(priority);
