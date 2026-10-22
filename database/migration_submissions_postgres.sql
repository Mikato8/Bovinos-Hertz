CREATE TABLE submissions (
    id SERIAL PRIMARY KEY,
    submitter_name VARCHAR(120) NOT NULL,
    submitter_email VARCHAR(200) NOT NULL,
    submitter_affiliation VARCHAR(200),
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
    frequencies_json TEXT,
    outcomes_json TEXT,
    risks_json TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending','approved','rejected')),
    reviewer_notes VARCHAR(500),
    reviewed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_sub_status ON submissions(status);
CREATE INDEX idx_sub_created ON submissions(created_at);
