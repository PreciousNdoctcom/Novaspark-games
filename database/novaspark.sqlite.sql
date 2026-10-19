-- SQLite version of the schema, used only by the hosted demo (DB_DRIVER=sqlite).
-- includes/db.php runs this and database/seed.sql automatically on first request.

CREATE TABLE games (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    title        TEXT    NOT NULL,
    slug         TEXT    NULL,
    genre        TEXT    NOT NULL,
    price        NUMERIC NOT NULL,
    description  TEXT    NOT NULL,
    release_year INTEGER NOT NULL,
    image_url    TEXT    DEFAULT 'assets/img/games/fallback.svg',
    status       TEXT    NOT NULL DEFAULT 'available' CHECK (status IN ('available','coming_soon','beta','hidden')),
    release_date TEXT    NULL,
    platforms    TEXT    NULL
);

CREATE TABLE users (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    username   TEXT NOT NULL UNIQUE,
    password   TEXT NOT NULL,
    email      TEXT NOT NULL,
    role       TEXT DEFAULT 'customer' CHECK (role IN ('admin','customer')),
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id       INTEGER NOT NULL REFERENCES users(id),
    game_id       INTEGER NOT NULL REFERENCES games(id),
    quantity      INTEGER NOT NULL,
    unit_price    NUMERIC NULL,
    total_price   NUMERIC NOT NULL,
    contact_email TEXT    NULL,
    order_date    TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE newsletter (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    email      TEXT NOT NULL UNIQUE,
    name       TEXT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
