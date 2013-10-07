CREATE DATABASE randomizer;

USE randomizer;

CREATE TABLE casters (
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(80) NOT NULL,
system ENUM('W', 'H') DEFAULT 'W',
faction ENUM('CYG', 'KHA', 'POM', 'CRX', 'RET', 'CON', 'MRC', 'TRL', 'LOE', 'SKO', 'COO', 'MIN', 'PPS'),
used ENUM ('Y', 'N') NOT NULL DEFAULT 'N',
picture VARCHAR(10),
description VARCHAR(255)
);

CREATE user 'random_user'@'localhost' identified by 'eeneymeeneymineymo';

GRANT ALL on casters TO 'random_user'@'localhost';
