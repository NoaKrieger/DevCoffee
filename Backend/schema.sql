CREATE DATABASE IF NOT EXISTS devcoffee
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE devcoffee;

DROP TABLE IF EXISTS consumptions;
DROP TABLE IF EXISTS drinks;

CREATE TABLE drinks (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    caffeine_mg INT          NOT NULL,
    volume_ml   INT          NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE consumptions (
    id             INT          NOT NULL AUTO_INCREMENT,
    drink_id       INT          NOT NULL,
    developer_name VARCHAR(100) NOT NULL,
    consumed_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),

    CONSTRAINT fk_consumptions_drink
        FOREIGN KEY (drink_id) REFERENCES drinks (id)
        ON DELETE CASCADE,

    INDEX idx_consumptions_developer_date (developer_name, consumed_at)
) ENGINE=InnoDB;

INSERT INTO drinks (name, caffeine_mg, volume_ml) VALUES
    ('Espresso',       80,  30),
    ('Café Allongé',  110, 120),
    ('Monster Energy', 160, 500);
