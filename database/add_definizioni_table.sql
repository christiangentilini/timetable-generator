-- Add definizioni table if it doesn't exist already
CREATE TABLE IF NOT EXISTS `definizioni` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `definition` varchar(255) NOT NULL,
    `definition_parent` varchar(50) NOT NULL,
    `image_path` varchar(255) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;