--
-- Sequence "user_ids"
-- Name: user_ids; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE user_ids
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Table "users"
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE users (
    user_id integer DEFAULT nextval('user_ids'::text) PRIMARY KEY,
    username character varying(128) DEFAULT ''::character varying NOT NULL,
    mail_host character varying(128) DEFAULT ''::character varying NOT NULL,
    alias character varying(128) DEFAULT ''::character varying NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL,
    last_login timestamp with time zone DEFAULT now() NOT NULL,
    "language" character varying(5) DEFAULT 'en'::character varying NOT NULL,
    preferences text DEFAULT ''::text NOT NULL
);

CREATE INDEX users_username_id_idx ON users (username);
CREATE INDEX users_alias_id_idx ON users (alias);

  
--
-- Table "session"
-- Name: session; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE "session" (
    sess_id character varying(40) DEFAULT ''::character varying PRIMARY KEY,
    created timestamp with time zone DEFAULT now() NOT NULL,
    changed timestamp with time zone DEFAULT now() NOT NULL,
    ip character varying(41) NOT NULL,
    vars text NOT NULL
);



--
-- Sequence "identity_ids"
-- Name: identity_ids; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE identity_ids
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Table "identities"
-- Name: identities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE identities (
    identity_id integer DEFAULT nextval('identity_ids'::text) PRIMARY KEY,
    user_id integer NOT NULL REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    del integer DEFAULT 0 NOT NULL,
    standard integer DEFAULT 0 NOT NULL,
    name character varying(128) NOT NULL,
    organization character varying(128),
    email character varying(128) NOT NULL,
    "reply-to" character varying(128),
    bcc character varying(128),
    signature text,
    html_signature integer DEFAULT 0 NOT NULL
);

CREATE INDEX identities_user_id_idx ON identities (user_id);


--
-- Sequence "contact_ids"
-- Name: contact_ids; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE contact_ids
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Table "contacts"
-- Name: contacts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE contacts (
    contact_id integer DEFAULT nextval('contact_ids'::text) PRIMARY KEY,
    user_id integer NOT NULL REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    changed timestamp with time zone DEFAULT now() NOT NULL,
    del integer DEFAULT 0 NOT NULL,
    name character varying(128) DEFAULT ''::character varying NOT NULL,
    email character varying(128) DEFAULT ''::character varying NOT NULL,
    firstname character varying(128) DEFAULT ''::character varying NOT NULL,
    surname character varying(128) DEFAULT ''::character varying NOT NULL,
    vcard text
);

CREATE INDEX contacts_user_id_idx ON contacts (user_id);

--
-- Sequence "cache_ids"
-- Name: cache_ids; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cache_ids
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Table "cache"
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE "cache" (
    cache_id integer DEFAULT nextval('cache_ids'::text) PRIMARY KEY,
    user_id integer NOT NULL REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    session_id character varying(40) REFERENCES "session" (sess_id) ON DELETE CASCADE ON UPDATE CASCADE,
    cache_key character varying(128) DEFAULT ''::character varying NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL,
    data text NOT NULL
);

CREATE INDEX cache_user_id_idx ON "cache" (user_id, cache_key);
CREATE INDEX cache_session_id_idx ON "cache" (session_id);

--
-- Sequence "message_ids"
-- Name: message_ids; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE message_ids
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Table "messages"
-- Name: messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE messages (
    message_id integer DEFAULT nextval('message_ids'::text) PRIMARY KEY,
    user_id integer NOT NULL REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    del integer DEFAULT 0 NOT NULL,
    cache_key character varying(128) DEFAULT ''::character varying NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL,
    idx integer DEFAULT 0 NOT NULL,
    uid integer DEFAULT 0 NOT NULL,
    subject character varying(128) DEFAULT ''::character varying NOT NULL,
    "from" character varying(128) DEFAULT ''::character varying NOT NULL,
    "to" character varying(128) DEFAULT ''::character varying NOT NULL,
    cc character varying(128) DEFAULT ''::character varying NOT NULL,
    date timestamp with time zone NOT NULL,
    size integer DEFAULT 0 NOT NULL,
    headers text NOT NULL,
    structure text
);

ALTER TABLE messages ADD UNIQUE (user_id, cache_key, uid);
CREATE INDEX messages_created_idx ON messages (created);
