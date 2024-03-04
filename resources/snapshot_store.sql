DROP TABLE IF EXISTS your_table_name;

CREATE TABLE IF NOT EXISTS event_store_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aggregate_type VARCHAR(255) NOT NULL,
    aggregate_id CHAR(36) NOT NULL,
    aggregate_version INT NOT NULL,
    aggregate_root TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
