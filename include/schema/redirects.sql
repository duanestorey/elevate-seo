CREATE TABLE {prefix}elevate_redirects (
 id int(11) NOT NULL AUTO_INCREMENT,
 source_url varchar(255) NOT NULL,
 dest_url varchar(255) NOT NULL,
 date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY  (id)
) {charset_collate};