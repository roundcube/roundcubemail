ALTER TABLE [dbo].[filestore] ADD [context] varchar(32) COLLATE Latin1_General_CI_AI NOT NULL
GO

UPDATE [dbo].[filestore] SET [context] = 'enigma'
GO

CREATE UNIQUE INDEX [IX_filestore_user_id_context_filename] ON [dbo].[filestore]([user_id],[context],[filename]) ON [PRIMARY]
GO

