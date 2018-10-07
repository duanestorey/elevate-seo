CREATE TABLE {prefix}elevate_visits (
  id int(11) NOT NULL AUTO_INCREMENT,
  visitors int(11) NOT NULL,
  views int(11) NOT NULL,
  date_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `date_time_index` (`date_time`),
  UNIQUE KEY `unique_date_key` (`date_time`),
  PRIMARY KEY  (id)
) {charset_collate};
