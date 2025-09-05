-- QLink Database Schema
-- Smart Queuing System for Schools

-- Create database
CREATE DATABASE IF NOT EXISTS `qlink` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `qlink`;

-- Users table
CREATE TABLE `users` (
    `user_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'staff', 'admin') NOT NULL DEFAULT 'student',
    `phone` VARCHAR(20) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Departments table
CREATE TABLE `departments` (
    `dept_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_active` (`is_active`),
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Queues table
CREATE TABLE `queues` (
    `queue_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `dept_id` INT UNSIGNED NOT NULL,
    `ticket_no` INT NOT NULL,
    `status` ENUM('waiting', 'serving', 'done', 'cancelled', 'missed') NOT NULL DEFAULT 'waiting',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `started_at` TIMESTAMP NULL,
    `finished_at` TIMESTAMP NULL,
    `notes` TEXT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`dept_id`) REFERENCES `departments`(`dept_id`) ON DELETE CASCADE,
    INDEX `idx_user_dept` (`user_id`, `dept_id`),
    INDEX `idx_dept_status` (`dept_id`, `status`),
    INDEX `idx_ticket_dept_date` (`ticket_no`, `dept_id`, `created_at`),
    INDEX `idx_status_date` (`status`, `created_at`),
    UNIQUE KEY `unique_ticket_dept_date` (`ticket_no`, `dept_id`, DATE(`created_at`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Department now serving table
CREATE TABLE `dept_now_serving` (
    `dept_id` INT UNSIGNED PRIMARY KEY,
    `now_serving` INT NOT NULL DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`dept_id`) REFERENCES `departments`(`dept_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE `notifications` (
    `notif_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `queue_id` INT UNSIGNED NOT NULL,
    `channel` ENUM('in_app', 'sms') NOT NULL,
    `message` TEXT NOT NULL,
    `trigger` ENUM('next10', 'called', 'missed', 'cancelled') NOT NULL,
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `sent_status` ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    `read_at` TIMESTAMP NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`queue_id`) REFERENCES `queues`(`queue_id`) ON DELETE CASCADE,
    INDEX `idx_user_status` (`user_id`, `sent_status`),
    INDEX `idx_queue_trigger` (`queue_id`, `trigger`),
    INDEX `idx_sent_status` (`sent_status`),
    INDEX `idx_sent_at` (`sent_at`),
    UNIQUE KEY `unique_notification` (`queue_id`, `trigger`, `channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Queue history table
CREATE TABLE `queue_history` (
    `history_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `queue_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `dept_id` INT UNSIGNED NOT NULL,
    `ticket_no` INT NOT NULL,
    `outcome` ENUM('served', 'missed', 'cancelled') NOT NULL,
    `duration_minutes` INT NULL,
    `created_at` TIMESTAMP NOT NULL,
    `started_at` TIMESTAMP NULL,
    `finished_at` TIMESTAMP NULL,
    `archived_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_dept` (`user_id`, `dept_id`),
    INDEX `idx_dept_date` (`dept_id`, `created_at`),
    INDEX `idx_outcome` (`outcome`),
    INDEX `idx_archived_at` (`archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System settings table
CREATE TABLE `system_settings` (
    `setting_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `description` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log table
CREATE TABLE `activity_log` (
    `log_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_action` (`user_id`, `action`),
    INDEX `idx_action_date` (`action`, `created_at`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data

-- Insert departments
INSERT INTO `departments` (`name`, `description`, `is_active`) VALUES
('Registrar Office', 'Student registration and enrollment services', 1),
('Student Affairs', 'Student welfare and activities', 1),
('Academic Services', 'Academic records and transcript requests', 1),
('Finance Office', 'Tuition and payment inquiries', 1),
('Library Services', 'Book borrowing and research assistance', 1);

-- Insert admin user (password: admin123)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `is_active`) VALUES
('Admin User', 'admin@qlink.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+639123456789', 1);

-- Insert staff users (password: staff123)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `is_active`) VALUES
('John Smith', 'john.smith@qlink.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', '+639123456790', 1),
('Maria Garcia', 'maria.garcia@qlink.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', '+639123456791', 1),
('Robert Johnson', 'robert.johnson@qlink.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', '+639123456792', 1);

-- Insert student users (password: student123)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `is_active`) VALUES
('Alice Brown', 'alice.brown@student.qlink.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '+639123456793', 1),
('Bob Wilson', 'bob.wilson@student.qlink.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '+639123456794', 1),
('Carol Davis', 'carol.davis@student.qlink.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '+639123456795', 1),
('David Miller', 'david.miller@student.qlink.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '+639123456796', 1),
('Eva Rodriguez', 'eva.rodriguez@student.qlink.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '+639123456797', 1);

-- Initialize department now serving
INSERT INTO `dept_now_serving` (`dept_id`, `now_serving`) VALUES
(1, 0), (2, 0), (3, 0), (4, 0), (5, 0);

-- Insert sample queues (for today)
INSERT INTO `queues` (`user_id`, `dept_id`, `ticket_no`, `status`, `created_at`) VALUES
(5, 1, 1, 'done', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(6, 1, 2, 'done', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(7, 1, 3, 'serving', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(8, 2, 1, 'waiting', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(9, 2, 2, 'waiting', DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
(5, 3, 1, 'waiting', DATE_SUB(NOW(), INTERVAL 5 MINUTE));

-- Insert sample notifications
INSERT INTO `notifications` (`user_id`, `queue_id`, `channel`, `message`, `trigger`, `sent_status`) VALUES
(7, 3, 'in_app', 'Your ticket #3 is now being served at Registrar Office', 'called', 'sent'),
(8, 4, 'in_app', 'You are next in line at Student Affairs (Position: 1)', 'next10', 'sent'),
(9, 5, 'in_app', 'You are next in line at Student Affairs (Position: 2)', 'next10', 'sent'),
(5, 6, 'in_app', 'You are next in line at Academic Services (Position: 1)', 'next10', 'sent');

-- Insert system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sms_enabled', '1', 'Enable SMS notifications'),
('notification_threshold', '10', 'Number of positions before notifying user'),
('queue_timeout_minutes', '30', 'Minutes before marking queue as missed'),
('max_queue_size', '100', 'Maximum number of people in queue per department'),
('auto_cleanup_days', '30', 'Days to keep old queue data');

-- Insert sample activity log
INSERT INTO `activity_log` (`user_id`, `action`, `description`, `ip_address`) VALUES
(1, 'login', 'Admin user logged in', '127.0.0.1'),
(5, 'join_queue', 'Joined queue at Registrar Office', '127.0.0.1'),
(6, 'join_queue', 'Joined queue at Registrar Office', '127.0.0.1'),
(7, 'join_queue', 'Joined queue at Registrar Office', '127.0.0.1'),
(3, 'call_next', 'Called next customer at Registrar Office', '127.0.0.1'),
(3, 'mark_done', 'Marked customer as done at Registrar Office', '127.0.0.1');

-- Create views for easier querying

-- View for active queues with user and department info
CREATE VIEW `active_queues_view` AS
SELECT 
    q.queue_id,
    q.ticket_no,
    q.status,
    q.created_at,
    q.started_at,
    q.finished_at,
    u.name as customer_name,
    u.phone as customer_phone,
    d.name as department_name,
    d.dept_id,
    CASE 
        WHEN q.status = 'waiting' THEN 
            (SELECT COALESCE(MAX(ticket_no), 0) FROM queues 
             WHERE dept_id = q.dept_id AND status = 'done' AND DATE(created_at) = CURDATE()) - q.ticket_no + 1
        ELSE 0
    END as position_in_line
FROM queues q
JOIN users u ON q.user_id = u.user_id
JOIN departments d ON q.dept_id = d.dept_id
WHERE q.status IN ('waiting', 'serving')
AND DATE(q.created_at) = CURDATE();

-- View for department statistics
CREATE VIEW `department_stats_view` AS
SELECT 
    d.dept_id,
    d.name as department_name,
    d.is_active,
    COALESCE(dns.now_serving, 0) as now_serving,
    COUNT(CASE WHEN q.status = 'waiting' THEN 1 END) as waiting_count,
    COUNT(CASE WHEN q.status = 'serving' THEN 1 END) as serving_count,
    COUNT(CASE WHEN q.status = 'done' THEN 1 END) as done_count,
    COUNT(CASE WHEN q.status = 'cancelled' THEN 1 END) as cancelled_count,
    COUNT(CASE WHEN q.status = 'missed' THEN 1 END) as missed_count,
    COUNT(*) as total_today
FROM departments d
LEFT JOIN dept_now_serving dns ON d.dept_id = dns.dept_id
LEFT JOIN queues q ON d.dept_id = q.dept_id AND DATE(q.created_at) = CURDATE()
GROUP BY d.dept_id, d.name, d.is_active, dns.now_serving;

-- View for user queue history
CREATE VIEW `user_queue_history_view` AS
SELECT 
    q.queue_id,
    q.ticket_no,
    q.status,
    q.created_at,
    q.started_at,
    q.finished_at,
    d.name as department_name,
    CASE 
        WHEN q.status = 'done' AND q.started_at IS NOT NULL AND q.finished_at IS NOT NULL 
        THEN TIMESTAMPDIFF(MINUTE, q.started_at, q.finished_at)
        ELSE NULL
    END as duration_minutes
FROM queues q
JOIN departments d ON q.dept_id = d.dept_id
WHERE q.status IN ('done', 'cancelled', 'missed');

-- Create stored procedures

-- Procedure to join queue
DELIMITER //
CREATE PROCEDURE `JoinQueue`(
    IN p_user_id INT,
    IN p_dept_id INT
)
BEGIN
    DECLARE v_next_ticket INT;
    DECLARE v_queue_id INT;
    DECLARE v_user_already_in_queue INT;
    
    -- Check if user is already in a queue today
    SELECT COUNT(*) INTO v_user_already_in_queue
    FROM queues 
    WHERE user_id = p_user_id 
    AND dept_id = p_dept_id 
    AND status IN ('waiting', 'serving')
    AND DATE(created_at) = CURDATE();
    
    IF v_user_already_in_queue > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User is already in queue for this department';
    END IF;
    
    -- Get next ticket number for today
    SELECT COALESCE(MAX(ticket_no), 0) + 1 INTO v_next_ticket
    FROM queues 
    WHERE dept_id = p_dept_id 
    AND DATE(created_at) = CURDATE();
    
    -- Insert queue
    INSERT INTO queues (user_id, dept_id, ticket_no, status, created_at)
    VALUES (p_user_id, p_dept_id, v_next_ticket, 'waiting', NOW());
    
    SET v_queue_id = LAST_INSERT_ID();
    
    -- Return queue info
    SELECT 
        v_queue_id as queue_id,
        v_next_ticket as ticket_no,
        'waiting' as status,
        NOW() as created_at;
END //
DELIMITER ;

-- Procedure to call next customer
DELIMITER //
CREATE PROCEDURE `CallNextCustomer`(
    IN p_dept_id INT
)
BEGIN
    DECLARE v_next_queue_id INT;
    DECLARE v_next_ticket INT;
    DECLARE v_next_user_id INT;
    
    -- Get next waiting customer
    SELECT queue_id, ticket_no, user_id
    INTO v_next_queue_id, v_next_ticket, v_next_user_id
    FROM queues 
    WHERE dept_id = p_dept_id 
    AND status = 'waiting'
    AND DATE(created_at) = CURDATE()
    ORDER BY ticket_no ASC
    LIMIT 1;
    
    IF v_next_queue_id IS NOT NULL THEN
        -- Update queue status
        UPDATE queues 
        SET status = 'serving', started_at = NOW()
        WHERE queue_id = v_next_queue_id;
        
        -- Update department now serving
        UPDATE dept_now_serving 
        SET now_serving = v_next_ticket, updated_at = NOW()
        WHERE dept_id = p_dept_id;
        
        -- Return next customer info
        SELECT 
            v_next_queue_id as queue_id,
            v_next_ticket as ticket_no,
            v_next_user_id as user_id;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No customers waiting in queue';
    END IF;
END //
DELIMITER ;

-- Procedure to mark customer as done
DELIMITER //
CREATE PROCEDURE `MarkCustomerDone`(
    IN p_queue_id INT
)
BEGIN
    DECLARE v_dept_id INT;
    DECLARE v_ticket_no INT;
    DECLARE v_user_id INT;
    DECLARE v_started_at TIMESTAMP;
    
    -- Get queue info
    SELECT dept_id, ticket_no, user_id, started_at
    INTO v_dept_id, v_ticket_no, v_user_id, v_started_at
    FROM queues 
    WHERE queue_id = p_queue_id 
    AND status = 'serving';
    
    IF v_dept_id IS NOT NULL THEN
        -- Update queue status
        UPDATE queues 
        SET status = 'done', finished_at = NOW()
        WHERE queue_id = p_queue_id;
        
        -- Insert into history
        INSERT INTO queue_history (queue_id, user_id, dept_id, ticket_no, outcome, duration_minutes, created_at, started_at, finished_at)
        VALUES (p_queue_id, v_user_id, v_dept_id, v_ticket_no, 'served', 
                TIMESTAMPDIFF(MINUTE, v_started_at, NOW()), 
                (SELECT created_at FROM queues WHERE queue_id = p_queue_id),
                v_started_at, NOW());
        
        -- Return success
        SELECT 'Customer marked as done' as message;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Queue not found or not currently serving';
    END IF;
END //
DELIMITER ;

-- Create triggers

-- Trigger to log queue status changes
DELIMITER //
CREATE TRIGGER `queue_status_change_log` 
AFTER UPDATE ON `queues`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO activity_log (user_id, action, description)
        VALUES (
            NEW.user_id,
            CONCAT('queue_', NEW.status),
            CONCAT('Queue #', NEW.queue_id, ' status changed from ', OLD.status, ' to ', NEW.status)
        );
    END IF;
END //
DELIMITER ;

-- Trigger to update queue history when queue is completed
DELIMITER //
CREATE TRIGGER `queue_completed_history` 
AFTER UPDATE ON `queues`
FOR EACH ROW
BEGIN
    IF NEW.status IN ('done', 'cancelled', 'missed') AND OLD.status NOT IN ('done', 'cancelled', 'missed') THEN
        INSERT INTO queue_history (queue_id, user_id, dept_id, ticket_no, outcome, duration_minutes, created_at, started_at, finished_at)
        VALUES (
            NEW.queue_id,
            NEW.user_id,
            NEW.dept_id,
            NEW.ticket_no,
            CASE 
                WHEN NEW.status = 'done' THEN 'served'
                WHEN NEW.status = 'cancelled' THEN 'cancelled'
                WHEN NEW.status = 'missed' THEN 'missed'
            END,
            CASE 
                WHEN NEW.started_at IS NOT NULL AND NEW.finished_at IS NOT NULL 
                THEN TIMESTAMPDIFF(MINUTE, NEW.started_at, NEW.finished_at)
                ELSE NULL
            END,
            NEW.created_at,
            NEW.started_at,
            NEW.finished_at
        );
    END IF;
END //
DELIMITER ;

-- Create indexes for better performance
CREATE INDEX `idx_queues_dept_date_status` ON `queues` (`dept_id`, `created_at`, `status`);
CREATE INDEX `idx_queues_user_date` ON `queues` (`user_id`, `created_at`);
CREATE INDEX `idx_notifications_user_read` ON `notifications` (`user_id`, `read_at`);
CREATE INDEX `idx_activity_log_date_action` ON `activity_log` (`created_at`, `action`);

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON qlink.* TO 'qlink_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Show table structure
SHOW TABLES;

-- Show sample data
SELECT 'Users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'Departments', COUNT(*) FROM departments
UNION ALL
SELECT 'Queues', COUNT(*) FROM queues
UNION ALL
SELECT 'Notifications', COUNT(*) FROM notifications
UNION ALL
SELECT 'Activity Log', COUNT(*) FROM activity_log;
