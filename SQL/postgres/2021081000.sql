CREATE SEQUENCE responses_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE responses (
    response_id integer DEFAULT nextval('responses_seq'::text) PRIMARY KEY,
    user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    changed timestamp with time zone DEFAULT now() NOT NULL,
    del smallint DEFAULT 0 NOT NULL,
    name varchar(255) NOT NULL,
    data text NOT NULL,
    is_html smallint DEFAULT 0 NOT NULL
);

CREATE UNIQUE INDEX responses_user_id_idx ON responses (user_id, del);

ALTER TABLE identities ALTER html_signature TYPE smallint;
