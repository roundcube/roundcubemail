-- Roundcube Passkey login plugin - MySQL/MariaDB schema
--
-- One row per enrolled passkey credential, keyed by (user_id, cred_id):
-- a user (from the core `users` table) may enroll several passkeys.
-- `secret` is the user's IMAP password encrypted *in the browser* with a key
-- derived from the passkey via the WebAuthn PRF extension. The server never
-- sees the encryption key or the plaintext password.
--
-- If you use a `db_prefix`, add it to the table name below.

CREATE TABLE `passkey_login` (
 `user_id` int(10) UNSIGNED NOT NULL,
 `cred_id` varchar(512) NOT NULL,
 `iv` varchar(64) NOT NULL,
 `secret` text NOT NULL,
 `public_key` text NOT NULL,
 `alg` int(11) NOT NULL DEFAULT 0,
 `sign_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
 `description` varchar(255) NOT NULL DEFAULT '',
 `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`user_id`, `cred_id`),
 UNIQUE `cred_id_uniqueness` (`cred_id`),
 CONSTRAINT `user_id_fk_passkey_login` FOREIGN KEY (`user_id`)
   REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ROW_FORMAT=DYNAMIC ENGINE=INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
