-- Crea la tabella per la condivisione dei cronologici
CREATE TABLE IF NOT EXISTS timetable_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timetable_id INT NOT NULL,
    user_id INT NOT NULL,
    permission_level ENUM('view', 'edit') NOT NULL DEFAULT 'view',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (timetable_id) REFERENCES timetables(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_share (timetable_id, user_id)
);