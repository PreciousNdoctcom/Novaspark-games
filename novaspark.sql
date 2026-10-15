
--  NOVASPARK GAMES — Full Database Setup Script

CREATE DATABASE IF NOT EXISTS novaspark_db;
USE novaspark_db;

--  TABLE: games

CREATE TABLE IF NOT EXISTS games (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(100)  NOT NULL,
    genre        VARCHAR(50)   NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    description  TEXT          NOT NULL,
    release_year INT           NOT NULL,
    image_url    VARCHAR(255)  DEFAULT 'images/default-game.jpg'
);

--  TABLE: users

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    email      VARCHAR(100) NOT NULL,
    role       ENUM('admin','customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--  TABLE: orders

CREATE TABLE IF NOT EXISTS orders (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    game_id     INT           NOT NULL,
    quantity    INT           NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (game_id) REFERENCES games(id)
);


INSERT INTO games (title, genre, price, description, release_year) VALUES
('Void Protocol',     'RPG',            49.99, 'A deep narrative cyber-RPG where choices rewrite planetary history. BAFTA Nominated.',                                               2025),
('Nebula Drift',      'Racing',         29.99, 'High-velocity space flight racing across volatile asteroid belts and stellar anomalies.',                                            2026),
('Iron Circuit',      'Strategy',       39.99, 'Turn-based tactical mech warfare. Customize chassis hulls and manage power grids.',                                                  2026),
('Stellar Abyss',     'Survival',       19.99, 'Deep-sea exploration on an alien water planet. Scavenge resources and maintain oxygen.',                                             2024),
('Shadow Nova',       'Action Stealth', 59.99, 'High-stakes corporate espionage and tactical infiltration in a neon-drenched dystopian cityscape.',                                 2025),
('Echoes of Arun',    'Fantasy RPG',    44.99, 'An ancient world awakened by techno-magic anomalies. Explore broken realms and forge fractured alliances.',                         2026),
('Quantum Breach',    'Puzzle / Action',14.99, 'Manipulate localized temporal timelines to escape a collapsing, automated research facility.',                                       2024),
('Glitch City Hustle','Cyberpunk Sim',  24.99, 'Navigate the black market trade and corporate security nets of a sprawling underground megacity.',                                   2025),
('Binary Horizon',    'Space Explorer', 34.99, 'Chart uncharted star clusters, upgrade your hyperdrive warp matrix, and mine deep space resources.',                                 2026);


INSERT INTO users (username, password, role, email) VALUES
('admin',     '$2y$10$MIrRm6rTdB0Wd38m6I/1lOnFU7gNjDJgn94TN9kyXcFNZ.I1q5g6W', 'admin',    'admin@novasparkgames.example.com'),
('customer1', '$2y$10$Jmy32qvMUNmdumaywgjsS.iIIYoRN/y/wVObmQCMwM.xxR.iMeAEe', 'customer', 'customer1@example.com'),
('customer2', '$2y$10$egsdBsrNgr9dvM7pm0/O8u7h.yfBC.tMMN2D//gEDU/ttt7yHZl5O', 'customer', 'customer2@example.com');


UPDATE games SET image_url = 'https://picsum.photos/seed/voidprotocol/600/400'     WHERE id = 1;  -- Void Protocol
UPDATE games SET image_url = 'https://picsum.photos/seed/nebuladrift/600/400'      WHERE id = 2;  -- Nebula Drift
UPDATE games SET image_url = 'https://picsum.photos/seed/ironcircuit/600/400'      WHERE id = 3;  -- Iron Circuit
UPDATE games SET image_url = 'https://picsum.photos/seed/stellarabyss/600/400'     WHERE id = 4;  -- Stellar Abyss
UPDATE games SET image_url = 'https://picsum.photos/seed/shadownova/600/400'       WHERE id = 5;  -- Shadow Nova
UPDATE games SET image_url = 'https://picsum.photos/seed/echoesofarun/600/400'     WHERE id = 6;  -- Echoes of Arun
UPDATE games SET image_url = 'https://picsum.photos/seed/quantumbreach/600/400'    WHERE id = 7;  -- Quantum Breach
UPDATE games SET image_url = 'https://picsum.photos/seed/glitchcity/600/400'       WHERE id = 8;  -- Glitch City Hustle
UPDATE games SET image_url = 'https://picsum.photos/seed/binaryhorizon/600/400'    WHERE id = 9;  -- Binary Horizon