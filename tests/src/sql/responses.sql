DELETE FROM responses;
INSERT INTO responses (user_id, name, data, is_html) VALUES (1, 'response 1', 'test response 1', '0');
INSERT INTO responses (user_id, name, data, is_html) VALUES (1, 'response 2', '<p><b>test response 2</b></p>', '1');
