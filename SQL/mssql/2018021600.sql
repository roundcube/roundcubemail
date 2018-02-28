CREATE TABLE [dbo].[filestore] (
	[file_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[filename] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[mtime] [int] NOT NULL ,
	[data] [text] COLLATE Latin1_General_CI_AI NULL ,
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[filestore] WITH NOCHECK ADD 
	CONSTRAINT [PK_filestore_file_id] PRIMARY KEY  CLUSTERED 
	(
		[file_id]
	) ON [PRIMARY] 
GO

CREATE INDEX [IX_filestore_user_id] ON [dbo].[filestore]([user_id]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[filestore] ADD CONSTRAINT [FK_filestore_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

