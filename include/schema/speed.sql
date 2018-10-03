CREATE TABLE {prefix}elevate_speed (
  id int(11) NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  desktop_speed float NOT NULL,
  desktop_size int(11) NOT NULL,
  desktop_resources int(11) NOT NULL,
  mobile_speed float NOT NULL,
  mobile_size int(11) NOT NULL,
  mobile_resources int(11) NOT NULL,
  test_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
) {charset_collate};
