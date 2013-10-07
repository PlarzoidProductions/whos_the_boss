DROP DATABASE randomizer;

DROP user 'random_user'@'localhost';

flush privileges;

source create_db.sql;
