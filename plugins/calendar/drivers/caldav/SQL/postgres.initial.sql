/**
 * CalDAV Client
 *
 * @version @package_version@
 * @author Hugo Slabbert <hugo@slabnet.com>
 *
 * Copyright (C) 2014, Hugo Slabbert <hugo@slabnet.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

CREATE TYPE caldav_type AS ENUM ('vcal','vevent','vtodo','');

CREATE TABLE IF NOT EXISTS caldav_props (
  obj_id int NOT NULL,
  obj_type caldav_type NOT NULL,
  url varchar(255) NOT NULL,
  tag varchar(255) DEFAULT NULL,
  username varchar(255) DEFAULT NULL,
  pass varchar(1024) DEFAULT NULL,
  last_change timestamp without time zone DEFAULT now() NOT NULL,
  PRIMARY KEY (obj_id, obj_type)
);

CREATE OR REPLACE FUNCTION upd_timestamp() RETURNS TRIGGER 
LANGUAGE plpgsql
AS
$$
BEGIN
    NEW.last_change = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;

CREATE TRIGGER update_timestamp
  BEFORE INSERT OR UPDATE
  ON caldav_props
  FOR EACH ROW
  EXECUTE PROCEDURE upd_timestamp();

