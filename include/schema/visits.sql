CREATE TABLE {prefix}elevate_visits (
  id int(11) NOT NULL AUTO_INCREMENT,
  visitors int(11) NOT NULL,
  views int(11) NOT NULL,
  date_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
) {charset_collate};
