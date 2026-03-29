CREATE DATABASE IF NOT EXISTS my_cinema;
USE my_cinema;

-- Table des films
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT NOT NULL,
    release_date DATE,
    genre VARCHAR(100),
    image_url VARCHAR(255) DEFAULT NULL
);

-- Table des salles
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    type ENUM('Standard', '3D', 'IMAX') DEFAULT 'Standard',
    deleted_at DATETIME DEFAULT NULL
);

-- Table des séances
CREATE TABLE shows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT,
    room_id INT,
    start_time DATETIME NOT NULL,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);