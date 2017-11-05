CREATE TABLE url_shortener__records (
  id int(11) NOT NULL,
  short varchar(64) NOT NULL,
  long varchar(2048) NOT NULL,
  is_redirect tinyint(1) NOT NULL
);
