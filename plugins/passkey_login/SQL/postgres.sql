-- Roundcube Passkey login plugin - PostgreSQL schema
--
-- One row per enrolled passkey credential, keyed by (user_id, cred_id):
-- a user (from the core `users` table) may enroll several passkeys.
-- `secret` is the user's IMAP password encrypted *in the browser* with a key
-- derived from the passkey via the WebAuthn PRF extension. The server never
-- sees the encryption key or the plaintext password.
--
-- If you use a `db_prefix`, add it to the table name below.

CREATE TABLE "passkey_login" (
    user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    cred_id varchar(512) NOT NULL,
    iv varchar(64) NOT NULL,
    secret text NOT NULL,
    public_key text NOT NULL,
    alg integer NOT NULL DEFAULT 0,
    sign_count integer NOT NULL DEFAULT 0,
    description varchar(255) NOT NULL DEFAULT '',
    created timestamp with time zone DEFAULT now() NOT NULL,
    PRIMARY KEY (user_id, cred_id),
    CONSTRAINT passkey_login_cred_id UNIQUE (cred_id)
);
