ALTER TABLE url_shortener__records
  ADD forced_content_type VARCHAR(32) NULL DEFAULT NULL
  /*! AFTER is_redirect */;
