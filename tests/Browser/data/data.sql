-- Contacts
INSERT INTO contacts (user_id, changed, del, name, email, firstname, surname, vcard, words) VALUES (1, '2019-12-31 12:23:33.523071-05', 0, 'John Doe', 'johndoe@example.org', 'John', 'Doe', 'BEGIN:VCARD
VERSION:3.0
N:Doe;John;;;
FN:John Doe
EMAIL;TYPE=INTERNET;TYPE=HOME:johndoe@example.org
END:VCARD', ' john do johndo@example.org');
INSERT INTO contacts (user_id, changed, del, name, email, firstname, surname, vcard, words) VALUES (1, '2019-12-31 12:24:10.213475-05', 0, 'Jane Stalone', 'j.stalone@microsoft.com', 'Jane', 'Stalone', 'BEGIN:VCARD
VERSION:3.0
N:Stalone;Jane;;;
FN:Jane Stalone
EMAIL;TYPE=INTERNET;TYPE=HOME:j.stalone@microsoft.com
END:VCARD', ' jane stalone j.stalone@microsoft.com');
