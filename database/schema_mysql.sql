CREATE TABLE studies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    authors VARCHAR(1000) NULL,
    publication_year SMALLINT UNSIGNED NULL,
    doi VARCHAR(200) NULL,
    url VARCHAR(700) NULL,
    source_type ENUM('paper','patent','review','thesis','report') NOT NULL DEFAULT 'paper',
    species ENUM('dairy_cows','beef_cattle','calves','mixed') NOT NULL DEFAULT 'dairy_cows',
    context ENUM('milking','feeding','transport','growth','reproduction','housing','other') NOT NULL DEFAULT 'other',
    summary TEXT NULL,
    evidence_level ENUM('high','medium','low','preliminary') NOT NULL DEFAULT 'preliminary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_context (context),
    INDEX idx_species (species),
    INDEX idx_evidence (evidence_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE frequencies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    study_id INT UNSIGNED NOT NULL,
    frequency_min_hz DECIMAL(12,3) NOT NULL,
    frequency_max_hz DECIMAL(12,3) NOT NULL,
    frequency_type ENUM('mechanical_vibration','audible_sound','electromagnetic','mixed') NOT NULL,
    waveform VARCHAR(100) NULL,
    exposure_duration VARCHAR(100) NULL,
    body_region VARCHAR(100) NULL,
    notes VARCHAR(500) NULL,
    CONSTRAINT fk_freq_study FOREIGN KEY (study_id) REFERENCES studies(id) ON DELETE CASCADE,
    INDEX idx_range (frequency_min_hz, frequency_max_hz),
    INDEX idx_type (frequency_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE outcomes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    study_id INT UNSIGNED NOT NULL,
    outcome_type ENUM('milk_yield','udder_health','weight_gain','stress_indicator','feed_intake','reproduction','other') NOT NULL,
    effect_direction ENUM('positive','neutral','negative','mixed') NOT NULL,
    effect_size DECIMAL(10,3) NULL,
    effect_unit VARCHAR(50) NULL,
    p_value DECIMAL(6,4) NULL,
    notes VARCHAR(500) NULL,
    CONSTRAINT fk_out_study FOREIGN KEY (study_id) REFERENCES studies(id) ON DELETE CASCADE,
    INDEX idx_outcome (outcome_type),
    INDEX idx_direction (effect_direction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE risk_flags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    study_id INT UNSIGNED NOT NULL,
    frequency_range_hz VARCHAR(100) NOT NULL,
    risk_description VARCHAR(500) NOT NULL,
    evidence_level ENUM('high','medium','low','preliminary') NOT NULL DEFAULT 'preliminary',
    CONSTRAINT fk_risk_study FOREIGN KEY (study_id) REFERENCES studies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
