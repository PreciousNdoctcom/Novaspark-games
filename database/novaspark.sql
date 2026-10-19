--  NOVASPARK GAMES — full MySQL / MariaDB set-up script (version 2)
--  Import in phpMyAdmin (Import tab) or: mysql -u root < database/novaspark.sql
--  Upgrading an existing version 1 database instead? Run database/migrate-v1-to-v2.sql.

CREATE DATABASE IF NOT EXISTS novaspark_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE novaspark_db;

DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS newsletter;
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS users;

CREATE TABLE games (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(100)  NOT NULL,
    slug         VARCHAR(120)  NULL,
    genre        VARCHAR(50)   NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    description  TEXT          NOT NULL,
    release_year INT           NOT NULL,
    image_url    VARCHAR(255)  DEFAULT 'assets/img/games/fallback.svg',
    status       ENUM('available','coming_soon','beta','hidden') NOT NULL DEFAULT 'available',
    release_date DATE          NULL,
    platforms    VARCHAR(100)  NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    email      VARCHAR(100) NOT NULL,
    role       ENUM('admin','customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT           NOT NULL,
    game_id       INT           NOT NULL,
    quantity      INT           NOT NULL,
    unit_price    DECIMAL(10,2) NULL,
    total_price   DECIMAL(10,2) NOT NULL,
    contact_email VARCHAR(100)  NULL,
    order_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (game_id) REFERENCES games(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE newsletter (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(100) NOT NULL UNIQUE,
    name       VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data shared by the MySQL and SQLite set-up scripts.
-- Statuses, genres and platforms live here only, so Home and Games always agree.

INSERT INTO games (id, title, slug, genre, price, description, release_year, image_url, status, release_date, platforms) VALUES
(1, 'Void Protocol',      'void-protocol',      'RPG',             49.99, 'A deep narrative cyber-RPG where choices rewrite planetary history. Its branching dialogue engine carries every decision across all three acts. BAFTA nominated.', 2025, 'assets/img/games/void-protocol.svg',      'available',   '2025-03-14', 'PC / PS5 / Xbox'),
(2, 'Nebula Drift',       'nebula-drift',       'Racing',          29.99, 'High-velocity space flight racing across volatile asteroid belts and stellar anomalies, with over 40 handcrafted circuits.',                              2027, 'assets/img/games/nebula-drift.svg',       'coming_soon', '2027-02-26', 'PC / Mobile'),
(3, 'Iron Circuit',       'iron-circuit',       'Strategy',        39.99, 'Turn-based tactical mech warfare. Customise chassis hulls, manage power grids and outthink your rivals on the online leaderboard.',                     2026, 'assets/img/games/iron-circuit.svg',       'beta',        '2026-09-01', 'PC / Xbox / Mobile'),
(4, 'Stellar Abyss',      'stellar-abyss',      'Survival',        19.99, 'Deep-sea exploration on an alien water planet. Scavenge resources, maintain your oxygen and find out what lives below the light.',                       2024, 'assets/img/games/stellar-abyss.svg',      'available',   '2024-08-22', 'PC / PS5'),
(5, 'Shadow Nova',        'shadow-nova',        'Action Stealth',  59.99, 'High-stakes corporate espionage and tactical infiltration in a neon-drenched dystopian cityscape.',                                                      2025, 'assets/img/games/shadow-nova.svg',        'available',   '2025-10-09', 'PC / PS5'),
(6, 'Echoes of Arun',     'echoes-of-arun',     'Fantasy RPG',     44.99, 'An ancient world awakened by techno-magic anomalies. Explore broken realms and forge fractured alliances.',                                              2027, 'assets/img/games/echoes-of-arun.svg',     'coming_soon', '2027-05-14', 'PC / PS5 / Xbox'),
(7, 'Quantum Breach',     'quantum-breach',     'Puzzle Action',   14.99, 'Manipulate localised temporal timelines to escape a collapsing, automated research facility.',                                                         2024, 'assets/img/games/quantum-breach.svg',     'available',   '2024-02-02', 'PC / Switch'),
(8, 'Glitch City Hustle', 'glitch-city-hustle', 'Cyberpunk Sim',   24.99, 'Navigate the black market trade and corporate security nets of a sprawling underground megacity.',                                                     2025, 'assets/img/games/glitch-city-hustle.svg', 'available',   '2025-06-19', 'PC / Mobile'),
(9, 'Binary Horizon',     'binary-horizon',     'Space Explorer',  34.99, 'Chart uncharted star clusters, upgrade your hyperdrive warp matrix and mine deep space resources.',                                                     2026, 'assets/img/games/binary-horizon.svg',     'available',   '2026-04-30', 'PC / PS5 / Xbox');

-- Demo accounts. Passwords: admin / admin, customer1 / customer1, customer2 / customer2.
INSERT INTO users (id, username, password, email, role) VALUES
(1, 'admin',     '$2y$10$MIrRm6rTdB0Wd38m6I/1lOnFU7gNjDJgn94TN9kyXcFNZ.I1q5g6W', 'admin@novasparkgames.example.com', 'admin'),
(2, 'customer1', '$2y$10$Jmy32qvMUNmdumaywgjsS.iIIYoRN/y/wVObmQCMwM.xxR.iMeAEe', 'customer1@example.com',            'customer'),
(3, 'customer2', '$2y$10$egsdBsrNgr9dvM7pm0/O8u7h.yfBC.tMMN2D//gEDU/ttt7yHZl5O', 'customer2@example.com',            'customer');

-- Sample simulated orders so My orders and Admin have something to show.
INSERT INTO orders (user_id, game_id, quantity, unit_price, total_price, contact_email, order_date) VALUES
(2, 5, 1, 59.99,  59.99, 'customer1@example.com', '2026-09-12 18:40:00'),
(2, 1, 2, 49.99,  99.98, 'customer1@example.com', '2026-10-02 09:15:00'),
(3, 5, 1, 59.99,  59.99, 'customer2@example.com', '2026-10-11 20:05:00');
