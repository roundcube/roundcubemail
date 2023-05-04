DELETE FROM contacts;
DELETE FROM contactgroups;
DELETE FROM contactgroupmembers;
DELETE FROM collected_addresses;
INSERT INTO collected_addresses (user_id, name, email, type) VALUES (1, 'test', 'test@collected.eu', 1);
INSERT INTO contactgroups (user_id, name) VALUES (1, 'test-group');
INSERT INTO contacts (user_id, changed, del, name, email, firstname, surname, vcard, words)
    VALUES (1, '2019-12-31 12:23:33.523071-05', 0, 'John Doe', 'johndoe@example.org', 'John', 'Doe', 'BEGIN:VCARD
VERSION:3.0
N:Doe;John;;;
FN:John Doe
EMAIL;TYPE=INTERNET;TYPE=HOME:johndoe@example.org
END:VCARD', ' john do johndo@example.org');
INSERT INTO contacts (user_id, changed, del, name, email, firstname, surname, vcard, words)
    VALUES (1, '2019-12-31 12:24:10.213475-05', 0, 'Jane Stalone', 'j.stalone@microsoft.com', 'Jane', 'Stalone', 'BEGIN:VCARD
VERSION:3.0
N:Stalone;Jane;;;
FN:Jane Stalone
EMAIL;TYPE=INTERNET;TYPE=HOME:j.stalone@microsoft.com
END:VCARD', ' jane stalone j.stalone@microsoft.com');
INSERT INTO contacts (user_id, changed, del, name, email, firstname, surname, vcard, words)
    VALUES (1, '2019-12-31 12:24:10.213475-05', 0, 'Jack Rian', 'j.rian@gmail.com', 'Jack', 'Rian', 'BEGIN:VCARD
VERSION:3.0
N:Rian;Jack;;;
FN:Jack Rian
EMAIL;TYPE=INTERNET;TYPE=HOME:j.rian@gmal.com
END:VCARD', ' jack rian j.rian@gmail.com');
INSERT INTO contacts (user_id, changed, del, name, email, firstname, surname, vcard, words)
    VALUES (1, '2019-12-31 12:24:10.213475-05', 0, 'George Bush', 'g.bush@gov.com', 'George', 'Bush', 'BEGIN:VCARD
VERSION:3.0
N:Bush;George;;;
FN:George Bush
EMAIL;TYPE=INTERNET;TYPE=HOME:g.bush@gov.com
END:VCARD', ' george bush g.bush@gov.com');
INSERT INTO contacts (user_id, changed, del, name, email, firstname, surname, vcard, words)
    VALUES (1, '2019-12-31 12:24:10.213475-05', 0, 'Anna Karenina', 'a.karenina@leo.ru', 'Anna', 'Karenina', 'BEGIN:VCARD
VERSION:3.0
N:Karenina;Anna;;;
FN:Anna Karenina
EMAIL;TYPE=INTERNET;TYPE=HOME:a.karenina@leo.ru
END:VCARD', ' anna karenina a.karenina@leo.ru');
INSERT INTO contacts (user_id, changed, del, name, email, firstname, surname, vcard, words)
    VALUES (1, '2019-12-31 12:24:10.213475-05', 0, 'Jon Snow', 'j.snow@game.com', 'Jon', 'Snow', 'BEGIN:VCARD
VERSION:3.0
N:Snow;Jon;;;
FN:Jon ż Snow
EMAIL;TYPE=INTERNET;TYPE=HOME:j.snow@game.com
END:VCARD', ' jon snow j.snow@game.com');
