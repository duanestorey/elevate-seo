CREATE TABLE {prefix}elevate_search (
  id int(11) NOT NULL AUTO_INCREMENT,
  impressions int(11) NOT NULL,
  ctr float NOT NULL,
  avg_pos float NOT NULL,
  errors_not_found int(11) NOT NULL,
  errors_not_auth int(11) NOT NULL,
  errors_server int(11) NOT NULL,
  test_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
) {charset_collate};
