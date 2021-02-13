DELETE FROM identities;
INSERT INTO identities (user_id, name, standard, email) VALUES (1, 'test', '1', 'test@example.com');
INSERT INTO identities (user_id, name, standard, email, signature) VALUES (1, 'test', '0', 'test@example.org', 'sig');
