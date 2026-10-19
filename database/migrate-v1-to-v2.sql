-- Upgrade an existing version 1 novaspark_db in place (keeps users and orders).
USE novaspark_db;

ALTER TABLE games
  ADD COLUMN slug VARCHAR(120) NULL AFTER title,
  ADD COLUMN status ENUM('available','coming_soon','beta','hidden') NOT NULL DEFAULT 'available',
  ADD COLUMN release_date DATE NULL,
  ADD COLUMN platforms VARCHAR(100) NULL;

ALTER TABLE orders
  ADD COLUMN unit_price DECIMAL(10,2) NULL AFTER quantity,
  ADD COLUMN contact_email VARCHAR(100) NULL;

CREATE TABLE IF NOT EXISTS newsletter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

UPDATE games SET slug='void-protocol',      genre='RPG',            status='available',   release_date='2025-03-14', platforms='PC / PS5 / Xbox',    image_url='assets/img/games/void-protocol.svg'      WHERE title='Void Protocol';
UPDATE games SET slug='nebula-drift',       genre='Racing',         status='coming_soon', release_date='2027-02-26', platforms='PC / Mobile',        image_url='assets/img/games/nebula-drift.svg', release_year=2027 WHERE title='Nebula Drift';
UPDATE games SET slug='iron-circuit',       genre='Strategy',       status='beta',        release_date='2026-09-01', platforms='PC / Xbox / Mobile', image_url='assets/img/games/iron-circuit.svg'       WHERE title='Iron Circuit';
UPDATE games SET slug='stellar-abyss',      genre='Survival',       status='available',   release_date='2024-08-22', platforms='PC / PS5',           image_url='assets/img/games/stellar-abyss.svg'      WHERE title='Stellar Abyss';
UPDATE games SET slug='shadow-nova',        genre='Action Stealth', status='available',   release_date='2025-10-09', platforms='PC / PS5',           image_url='assets/img/games/shadow-nova.svg'        WHERE title='Shadow Nova';
UPDATE games SET slug='echoes-of-arun',     genre='Fantasy RPG',    status='coming_soon', release_date='2027-05-14', platforms='PC / PS5 / Xbox',    image_url='assets/img/games/echoes-of-arun.svg', release_year=2027 WHERE title='Echoes of Arun';
UPDATE games SET slug='quantum-breach',     genre='Puzzle Action',  status='available',   release_date='2024-02-02', platforms='PC / Switch',        image_url='assets/img/games/quantum-breach.svg'     WHERE title='Quantum Breach';
UPDATE games SET slug='glitch-city-hustle', genre='Cyberpunk Sim',  status='available',   release_date='2025-06-19', platforms='PC / Mobile',        image_url='assets/img/games/glitch-city-hustle.svg' WHERE title='Glitch City Hustle';
UPDATE games SET slug='binary-horizon',     genre='Space Explorer', status='available',   release_date='2026-04-30', platforms='PC / PS5 / Xbox',    image_url='assets/img/games/binary-horizon.svg'     WHERE title='Binary Horizon';

UPDATE orders o JOIN games g ON g.id = o.game_id SET o.unit_price = g.price WHERE o.unit_price IS NULL;
