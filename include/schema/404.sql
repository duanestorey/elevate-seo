CREATE TABLE {prefix}elevate_404 (
 id int(11) NOT NULL AUTO_INCREMENT,
 url varchar(255) NOT NULL,
 last_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 count int(11) NOT NULL,
 PRIMARY KEY  (id)
) {charset_collate};