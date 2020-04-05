BEGIN;

CREATE TABLE /*_*/bs_editnotifyconnector(
-- Primary key
enc_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
-- namespace.id of the namespace for notifications
enc_ns_id INT UNSIGNED NOT NULL,
-- user.username of the user who selected to be notified on namespace activity
enc_username VARCHAR(256),
-- action type like "edit" or "create"
enc_action VARCHAR(256)
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/enc_ns_id ON /*_*/bs_editnotifyconnector (enc_ns_id);
CREATE INDEX /*i*/enc_action ON /*_*/bs_editnotifyconnector (enc_action);
COMMIT;
