
CREATE SEQUENCE collected_addresses_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE collected_addresses (
    address_id integer DEFAULT nextval('collected_addresses_seq'::text) PRIMARY KEY,
    user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    changed timestamp with time zone DEFAULT now() NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
    email varchar(255) NOT NULL,
    "type" integer NOT NULL
);

CREATE UNIQUE INDEX collected_addresses_user_id_idx ON collected_addresses (user_id, "type", email);
