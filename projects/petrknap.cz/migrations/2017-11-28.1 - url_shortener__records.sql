CREATE TABLE url_shortener__records (
  id int(11) NOT NULL,
  keyword varchar(64) NOT NULL,
  url varchar(2048) NOT NULL,
  is_redirect tinyint(1) NOT NULL,
  UNIQUE INDEX unique_keyword (keyword),
  PRIMARY KEY(id)
);
