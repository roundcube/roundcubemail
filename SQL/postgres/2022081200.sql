DROP INDEX responses_user_id_idx;
CREATE INDEX responses_user_id_idx ON responses (user_id, del);
