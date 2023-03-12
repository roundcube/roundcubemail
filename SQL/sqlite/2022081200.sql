DROP INDEX ix_responses_user_id;
CREATE INDEX ix_responses_user_id ON responses(user_id, del);
