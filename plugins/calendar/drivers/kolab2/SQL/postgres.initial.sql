/**
 * Roundcube Calendar Kolab backend
 *
 * @author Sergey Sidlyarenko
 * @licence GNU AGPL
 **/

CREATE TABLE IF NOT EXISTS kolab_alarms (
  alarm_id character varying(255) NOT NULL,
  user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  notifyat timestamp without time zone DEFAULT NULL,
  dismissed smallint NOT NULL DEFAULT 0,
  PRIMARY KEY(alarm_id)
);

CREATE INDEX kolab_alarms_user_id_idx ON kolab_alarms (user_id);

CREATE TABLE IF NOT EXISTS itipinvitations (
  token character varying(64) NOT NULL,
  event_uid character varying(255) NOT NULL,
  user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  event text NOT NULL,
  expires timestamp without time zone DEFAULT NULL,
  cancelled smallint NOT NULL DEFAULT 0,
  PRIMARY KEY(token)
);

CREATE INDEX itipinvitations_user_id_event_uid_idx ON itipinvitations (user_id, event_uid);

INSERT INTO system (name, value) VALUES ('calendar-kolab-version', '2014041700');
