ALTER TABLE [dbo].[cache] ALTER COLUMN
	[cache_key] [varchar] (128) COLLATE Latin1_General_CS_AS NOT NULL
GO
ALTER TABLE [dbo].[cache_shared] ALTER COLUMN
	[cache_key] [varchar] (255) COLLATE Latin1_General_CS_AS NOT NULL
GO
ALTER TABLE [dbo].[cache_index] ALTER COLUMN
	[mailbox] [varchar] (128) COLLATE Latin1_General_CS_AS NOT NULL
GO
ALTER TABLE [dbo].[cache_messages] ALTER COLUMN
	[mailbox] [varchar] (128) COLLATE Latin1_General_CS_AS NOT NULL
GO
ALTER TABLE [dbo].[cache_thread] ALTER COLUMN
	[mailbox] [varchar] (128) COLLATE Latin1_General_CS_AS NOT NULL
GO
ALTER TABLE [dbo].[users] ALTER COLUMN
	[username] [varchar] (128) COLLATE Latin1_General_CS_AS NOT NULL
GO
