CREATE TABLE studies (
    id SERIAL PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    authors VARCHAR(1000),
    publication_year SMALLINT,
    doi VARCHAR(200),
    url VARCHAR(700),
    source_type VARCHAR(20) NOT NULL DEFAULT 'paper'
        CHECK (source_type IN ('paper','patent','review','thesis','report')),
    species VARCHAR(20) NOT NULL DEFAULT 'dairy_cows'
        CHECK (species IN ('dairy_cows','beef_cattle','calves','mixed')),
    context VARCHAR(20) NOT NULL DEFAULT 'other'
        CHECK (context IN ('milking','feeding','transport','growth','reproduction','housing','other')),
    summary TEXT,
    evidence_level VARCHAR(20) NOT NULL DEFAULT 'preliminary'
        CHECK (evidence_level IN ('high','medium','low','preliminary')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_studies_context ON studies(context);
CREATE INDEX idx_studies_species ON studies(species);
CREATE INDEX idx_studies_evidence ON studies(evidence_level);

CREATE TABLE frequencies (
    id SERIAL PRIMARY KEY,
    study_id INTEGER NOT NULL REFERENCES studies(id) ON DELETE CASCADE,
    frequency_min_hz NUMERIC(12,3) NOT NULL,
    frequency_max_hz NUMERIC(12,3) NOT NULL,
    frequency_type VARCHAR(30) NOT NULL
        CHECK (frequency_type IN ('mechanical_vibration','audible_sound','electromagnetic','mixed')),
    waveform VARCHAR(100),
    exposure_duration VARCHAR(100),
    body_region VARCHAR(100),
    notes VARCHAR(500)
);
CREATE INDEX idx_freq_range ON frequencies(frequency_min_hz, frequency_max_hz);
CREATE INDEX idx_freq_type ON frequencies(frequency_type);

CREATE TABLE outcomes (
    id SERIAL PRIMARY KEY,
    study_id INTEGER NOT NULL REFERENCES studies(id) ON DELETE CASCADE,
    outcome_type VARCHAR(30) NOT NULL
        CHECK (outcome_type IN ('milk_yield','udder_health','weight_gain','stress_indicator','feed_intake','reproduction','other')),
    effect_direction VARCHAR(20) NOT NULL
        CHECK (effect_direction IN ('positive','neutral','negative','mixed')),
    effect_size NUMERIC(10,3),
    effect_unit VARCHAR(50),
    p_value NUMERIC(6,4),
    notes VARCHAR(500)
);
CREATE INDEX idx_out_type ON outcomes(outcome_type);
CREATE INDEX idx_out_direction ON outcomes(effect_direction);

CREATE TABLE risk_flags (
    id SERIAL PRIMARY KEY,
    study_id INTEGER NOT NULL REFERENCES studies(id) ON DELETE CASCADE,
    frequency_range_hz VARCHAR(100) NOT NULL,
    risk_description VARCHAR(500) NOT NULL,
    evidence_level VARCHAR(20) NOT NULL DEFAULT 'preliminary'
        CHECK (evidence_level IN ('high','medium','low','preliminary'))
);
