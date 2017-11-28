ALTER TABLE url_shortener__records CHANGE short keyword varchar(64) NOT NULL, CHANGE long url varchar(2048) NOT NULL;
