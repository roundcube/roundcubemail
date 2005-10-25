--
-- PostgreSQL database dump
--

SET client_encoding = 'UNICODE';
SET check_function_bodies = false;
SET search_path = public, pg_catalog;

ALTER TABLE ONLY public.identities DROP CONSTRAINT "$1";
ALTER TABLE ONLY public.contacts DROP CONSTRAINT "$1";
ALTER TABLE ONLY public."cache" DROP CONSTRAINT "$2";
ALTER TABLE ONLY public."cache" DROP CONSTRAINT "$1";
ALTER TABLE ONLY public.users DROP CONSTRAINT users_pkey;
ALTER TABLE ONLY public."session" DROP CONSTRAINT session_pkey;
ALTER TABLE ONLY public.identities DROP CONSTRAINT identities_pkey;
ALTER TABLE ONLY public.contacts DROP CONSTRAINT contacts_pkey;
ALTER TABLE ONLY public."cache" DROP CONSTRAINT cache_pkey;
DROP TABLE public.users;
DROP TABLE public."session";
DROP TABLE public.identities;
DROP TABLE public.contacts;
DROP TABLE public."cache";
DROP SEQUENCE public.user_ids;
DROP SEQUENCE public.identity_ids;
DROP SEQUENCE public.contact_ids;
DROP SEQUENCE public.cache_ids;
--
-- TOC entry 4 (OID 15282470)
-- Name: cache_ids; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cache_ids
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 5 (OID 15282472)
-- Name: contact_ids; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE contact_ids
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 6 (OID 15282474)
-- Name: identity_ids; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE identity_ids
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 7 (OID 15282476)
-- Name: user_ids; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE user_ids
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 8 (OID 15282478)
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE "cache" (
    cache_id integer DEFAULT nextval('cache_ids'::text) NOT NULL,
    user_id integer DEFAULT 0 NOT NULL,
    session_id character varying(32),
    cache_key character varying(128) DEFAULT ''::character varying NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL,
    data text NOT NULL
);


--
-- TOC entry 10 (OID 15282486)
-- Name: contacts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE contacts (
    contact_id integer DEFAULT nextval('contact_ids'::text) NOT NULL,
    user_id integer DEFAULT 0 NOT NULL,
    del boolean DEFAULT false NOT NULL,
    name character varying(128) DEFAULT ''::character varying NOT NULL,
    email character varying(128) DEFAULT ''::character varying NOT NULL,
    firstname character varying(128) DEFAULT ''::character varying NOT NULL,
    surname character varying(128) DEFAULT ''::character varying NOT NULL,
    vcard text NOT NULL
);


--
-- TOC entry 11 (OID 15282494)
-- Name: identities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE identities (
    identity_id integer DEFAULT nextval('identity_ids'::text) NOT NULL,
    user_id integer DEFAULT 0 NOT NULL,
    del boolean DEFAULT false NOT NULL,
    "default" boolean DEFAULT false NOT NULL,
    name character varying(128) NOT NULL,
    organization character varying(128),
    email character varying(128) NOT NULL,
    "reply-to" character varying(128),
    bcc character varying(128),
    signature text
);


--
-- TOC entry 12 (OID 15282503)
-- Name: session; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE "session" (
    sess_id character varying(32) DEFAULT ''::character varying NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL,
    changed timestamp with time zone DEFAULT now() NOT NULL,
    ip character varying(16) NOT NULL,
    vars text NOT NULL
);


--
-- TOC entry 13 (OID 15282510)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE users (
    user_id integer DEFAULT nextval('user_ids'::text) NOT NULL,
    username character varying(128) DEFAULT ''::character varying NOT NULL,
    mail_host character varying(128) DEFAULT ''::character varying NOT NULL,
    alias character varying(128) DEFAULT ''::character varying NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL,
    last_login timestamp with time zone DEFAULT now() NOT NULL,
    "language" character varying(5) DEFAULT 'en'::character varying NOT NULL,
    preferences text DEFAULT ''::text NOT NULL
);


--
-- TOC entry 14 (OID 15282518)
-- Name: cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "cache"
    ADD CONSTRAINT cache_pkey PRIMARY KEY (cache_id);


--
-- TOC entry 15 (OID 15282520)
-- Name: contacts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contacts
    ADD CONSTRAINT contacts_pkey PRIMARY KEY (contact_id);


--
-- TOC entry 16 (OID 15282522)
-- Name: identities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY identities
    ADD CONSTRAINT identities_pkey PRIMARY KEY (identity_id);


--
-- TOC entry 17 (OID 15282524)
-- Name: session_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "session"
    ADD CONSTRAINT session_pkey PRIMARY KEY (sess_id);


--
-- TOC entry 18 (OID 15282526)
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- TOC entry 19 (OID 15282528)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "cache"
    ADD CONSTRAINT "$1" FOREIGN KEY (user_id) REFERENCES users(user_id);


--
-- TOC entry 20 (OID 15282532)
-- Name: $2; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "cache"
    ADD CONSTRAINT "$2" FOREIGN KEY (session_id) REFERENCES "session"(sess_id);


--
-- TOC entry 21 (OID 15282536)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contacts
    ADD CONSTRAINT "$1" FOREIGN KEY (user_id) REFERENCES users(user_id);


--
-- TOC entry 22 (OID 15282540)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY identities
    ADD CONSTRAINT "$1" FOREIGN KEY (user_id) REFERENCES users(user_id);


SET SESSION AUTHORIZATION 'postgres';

--
-- TOC entry 3 (OID 15282469)
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


SET SESSION AUTHORIZATION 'postgres';

--
-- TOC entry 9 (OID 15282478)
-- Name: TABLE "cache"; Type: COMMENT; Schema: public; Owner: postgres
--