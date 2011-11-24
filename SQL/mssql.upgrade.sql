-- Roundcube Webmail update script for MSSQL databases

-- Updates from version 0.3.1

ALTER TABLE [dbo].[messages] ADD CONSTRAINT [FK_messages_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[cache] ADD CONSTRAINT [FK_cache_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[contacts] ADD CONSTRAINT [FK_contacts_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[identities] ADD CONSTRAINT [FK_identities_user_id] 
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[identities] ADD [changed] [datetime] NULL 
GO

CREATE TABLE [dbo].[contactgroups] (
	[contactgroup_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[changed] [datetime] NOT NULL ,
	[del] [char] (1) COLLATE Latin1_General_CI_AI NOT NULL ,
	[name] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL
) ON [PRIMARY] 
GO

CREATE TABLE [dbo].[contactgroupmembers] (
	[contactgroup_id] [int] NOT NULL ,
	[contact_id] [int] NOT NULL ,
	[created] [datetime] NOT NULL
) ON [PRIMARY] 
GO

ALTER TABLE [dbo].[contactgroups] WITH NOCHECK ADD 
	CONSTRAINT [PK_contactgroups_contactgroup_id] PRIMARY KEY CLUSTERED 
	(
		[contactgroup_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[contactgroupmembers] WITH NOCHECK ADD 
	CONSTRAINT [PK_contactgroupmembers_id] PRIMARY KEY CLUSTERED 
	(
		[contactgroup_id], [contact_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[contactgroups] ADD 
	CONSTRAINT [DF_contactgroups_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_contactgroups_changed] DEFAULT (getdate()) FOR [changed],
	CONSTRAINT [DF_contactgroups_del] DEFAULT ('0') FOR [del],
	CONSTRAINT [DF_contactgroups_name] DEFAULT ('') FOR [name],
	CONSTRAINT [CK_contactgroups_del] CHECK ([del] = '1' or [del] = '0')
GO

CREATE  INDEX [IX_contactgroups_user_id] ON [dbo].[contacts]([user_id]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[contactgroupmembers] ADD 
	CONSTRAINT [DF_contactgroupmembers_contactgroup_id] DEFAULT (0) FOR [contactgroup_id],
	CONSTRAINT [DF_contactgroupmembers_contact_id] DEFAULT (0) FOR [contact_id],
	CONSTRAINT [DF_contactgroupmembers_created] DEFAULT (getdate()) FOR [created]
GO

ALTER TABLE [dbo].[contactgroupmembers] ADD CONSTRAINT [FK_contactgroupmembers_contactgroup_id]
    FOREIGN KEY ([contactgroup_id]) REFERENCES [dbo].[contactgroups] ([contactgroup_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

CREATE TRIGGER [contact_delete_member] ON [dbo].[contacts]
    AFTER DELETE AS
    DELETE FROM [dbo].[contactgroupmembers]
    WHERE [contact_id] IN (SELECT [contact_id] FROM deleted)
GO

ALTER TABLE [dbo].[contactgroups] ADD CONSTRAINT [FK_contactgroups_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

-- Updates from version 0.4.2

DROP INDEX [IX_users_username]
GO
CREATE UNIQUE INDEX [IX_users_username] ON [dbo].[users]([username],[mail_host]) ON [PRIMARY]
GO
ALTER TABLE [dbo].[contacts] ALTER COLUMN [email] [varchar] (255) COLLATE Latin1_General_CI_AI NOT NULL
GO

-- Updates from version 0.5.1
-- Updates from version 0.5.2
-- Updates from version 0.5.3
-- Updates from version 0.5.4

ALTER TABLE [dbo].[contacts] ADD [words] [text] COLLATE Latin1_General_CI_AI NULL 
GO
CREATE INDEX [IX_contactgroupmembers_contact_id] ON [dbo].[contactgroupmembers]([contact_id]) ON [PRIMARY]
GO
DELETE FROM [dbo].[messages]
GO
DELETE FROM [dbo].[cache]
GO

-- Updates from version 0.6

CREATE TABLE [dbo].[dictionary] (
    [user_id] [int] ,
    [language] [varchar] (5) COLLATE Latin1_General_CI_AI NOT NULL ,
    [data] [text] COLLATE Latin1_General_CI_AI NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE  UNIQUE INDEX [IX_dictionary_user_language] ON [dbo].[dictionary]([user_id],[language]) ON [PRIMARY]
GO

CREATE TABLE [dbo].[searches] (
	[search_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[type] [tinyint] NOT NULL ,
	[name] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[data] [text] COLLATE Latin1_General_CI_AI NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[searches] WITH NOCHECK ADD 
	CONSTRAINT [PK_searches_search_id] PRIMARY KEY CLUSTERED 
	(
		[search_id]
	) ON [PRIMARY] 
GO

ALTER TABLE [dbo].[searches] ADD 
	CONSTRAINT [DF_searches_user] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_searches_type] DEFAULT (0) FOR [type],
GO

CREATE UNIQUE INDEX [IX_searches_user_type_name] ON [dbo].[searches]([user_id],[type],[name]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[searches] ADD CONSTRAINT [FK_searches_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

DROP TABLE [dbo].[messages]
GO
CREATE TABLE [dbo].[cache_index] (
	[user_id] [int] NOT NULL ,
	[mailbox] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[changed] [datetime] NOT NULL ,
	[valid] [char] (1) COLLATE Latin1_General_CI_AI NOT NULL ,
	[data] [text] COLLATE Latin1_General_CI_AI NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[cache_thread] (
	[user_id] [int] NOT NULL ,
	[mailbox] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[changed] [datetime] NOT NULL ,
	[data] [text] COLLATE Latin1_General_CI_AI NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[cache_messages] (
	[user_id] [int] NOT NULL ,
	[mailbox] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[uid] [int] NOT NULL ,
	[changed] [datetime] NOT NULL ,
	[data] [text] COLLATE Latin1_General_CI_AI NOT NULL 
	[flags] [int] NOT NULL ,
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[cache_index] WITH NOCHECK ADD 
	 PRIMARY KEY CLUSTERED 
	(
		[user_id],[mailbox]
	) ON [PRIMARY] 
GO

ALTER TABLE [dbo].[cache_thread] WITH NOCHECK ADD 
	 PRIMARY KEY CLUSTERED 
	(
		[user_id],[mailbox]
	) ON [PRIMARY] 
GO

ALTER TABLE [dbo].[cache_messages] WITH NOCHECK ADD 
	 PRIMARY KEY CLUSTERED 
	(
		[user_id],[mailbox],[uid]
	) ON [PRIMARY] 
GO

ALTER TABLE [dbo].[cache_index] ADD 
	CONSTRAINT [DF_cache_index_changed] DEFAULT (getdate()) FOR [changed],
	CONSTRAINT [DF_cache_index_valid] DEFAULT ('0') FOR [valid]
GO

CREATE  INDEX [IX_cache_index_user_id] ON [dbo].[cache_index]([user_id]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[cache_thread] ADD 
	CONSTRAINT [DF_cache_thread_changed] DEFAULT (getdate()) FOR [changed]
GO

CREATE  INDEX [IX_cache_thread_user_id] ON [dbo].[cache_thread]([user_id]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[cache_messages] ADD 
	CONSTRAINT [DF_cache_messages_changed] DEFAULT (getdate()) FOR [changed],
	CONSTRAINT [DF_cache_messages_flags] DEFAULT (0) FOR [flags]
GO

CREATE  INDEX [IX_cache_messages_user_id] ON [dbo].[cache_messages]([user_id]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[cache_index] ADD CONSTRAINT [FK_cache_index_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[cache_thread] ADD CONSTRAINT [FK_cache_thread_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[cache_messages] ADD CONSTRAINT [FK_cache_messages_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

-- Updates from version 0.7-beta

ALTER TABLE [dbo].[session] ALTER COLUMN [sess_id] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL
GO

