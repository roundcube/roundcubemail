CREATE TABLE [dbo].[cache] (
	[cache_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[cache_key] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[created] [datetime] NOT NULL ,
	[data] [text] COLLATE Latin1_General_CI_AI NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[contacts] (
	[contact_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[changed] [datetime] NOT NULL ,
	[del] [char] (1) COLLATE Latin1_General_CI_AI NOT NULL ,
	[name] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[email] [varchar] (255) COLLATE Latin1_General_CI_AI NOT NULL ,
	[firstname] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[surname] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[vcard] [text] COLLATE Latin1_General_CI_AI NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
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

CREATE TABLE [dbo].[identities] (
	[identity_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[changed] [datetime] NOT NULL ,
	[del] [char] (1) COLLATE Latin1_General_CI_AI NOT NULL ,
	[standard] [char] (1) COLLATE Latin1_General_CI_AI NOT NULL ,
	[name] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[organization] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[email] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[reply-to] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[bcc] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[signature] [text] COLLATE Latin1_General_CI_AI NULL, 
	[html_signature] [char] (1) COLLATE Latin1_General_CI_AI NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[messages] (
	[message_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[del] [tinyint] NOT NULL ,
	[cache_key] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[created] [datetime] NOT NULL ,
	[idx] [int] NOT NULL ,
	[uid] [int] NOT NULL ,
	[subject] [varchar] (255) COLLATE Latin1_General_CI_AI NOT NULL ,
	[from] [varchar] (255) COLLATE Latin1_General_CI_AI NOT NULL ,
	[to] [varchar] (255) COLLATE Latin1_General_CI_AI NOT NULL ,
	[cc] [varchar] (255) COLLATE Latin1_General_CI_AI NOT NULL ,
	[date] [datetime] NOT NULL ,
	[size] [int] NOT NULL ,
	[headers] [text] COLLATE Latin1_General_CI_AI NOT NULL ,
	[structure] [text] COLLATE Latin1_General_CI_AI NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[session] (
	[sess_id] [varchar] (32) COLLATE Latin1_General_CI_AI NOT NULL ,
	[created] [datetime] NOT NULL ,
	[changed] [datetime] NULL ,
	[ip] [varchar] (40) COLLATE Latin1_General_CI_AI NOT NULL ,
	[vars] [text] COLLATE Latin1_General_CI_AI NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[users] (
	[user_id] [int] IDENTITY (1, 1) NOT NULL ,
	[username] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[mail_host] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[alias] [varchar] (128) COLLATE Latin1_General_CI_AI NOT NULL ,
	[created] [datetime] NOT NULL ,
	[last_login] [datetime] NULL ,
	[language] [varchar] (5) COLLATE Latin1_General_CI_AI NULL ,
	[preferences] [text] COLLATE Latin1_General_CI_AI NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[cache] WITH NOCHECK ADD 
	 PRIMARY KEY  CLUSTERED 
	(
		[cache_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[contacts] WITH NOCHECK ADD 
	CONSTRAINT [PK_contacts_contact_id] PRIMARY KEY  CLUSTERED 
	(
		[contact_id]
	)  ON [PRIMARY] 
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

ALTER TABLE [dbo].[identities] WITH NOCHECK ADD 
	 PRIMARY KEY  CLUSTERED 
	(
		[identity_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[messages] WITH NOCHECK ADD 
	 PRIMARY KEY  CLUSTERED 
	(
		[message_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[session] WITH NOCHECK ADD 
	CONSTRAINT [PK_session_sess_id] PRIMARY KEY  CLUSTERED 
	(
		[sess_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[users] WITH NOCHECK ADD 
	CONSTRAINT [PK_users_user_id] PRIMARY KEY  CLUSTERED 
	(
		[user_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[cache] ADD 
	CONSTRAINT [DF_cache_user_id] DEFAULT ('0') FOR [user_id],
	CONSTRAINT [DF_cache_cache_key] DEFAULT ('') FOR [cache_key],
	CONSTRAINT [DF_cache_created] DEFAULT (getdate()) FOR [created]
GO

CREATE  INDEX [IX_cache_user_id] ON [dbo].[cache]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [IX_cache_cache_key] ON [dbo].[cache]([cache_key]) ON [PRIMARY]
GO

CREATE  INDEX [IX_cache_created] ON [dbo].[cache]([created]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[contacts] ADD 
	CONSTRAINT [DF_contacts_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_contacts_changed] DEFAULT (getdate()) FOR [changed],
	CONSTRAINT [DF_contacts_del] DEFAULT ('0') FOR [del],
	CONSTRAINT [DF_contacts_name] DEFAULT ('') FOR [name],
	CONSTRAINT [DF_contacts_email] DEFAULT ('') FOR [email],
	CONSTRAINT [DF_contacts_firstname] DEFAULT ('') FOR [firstname],
	CONSTRAINT [DF_contacts_surname] DEFAULT ('') FOR [surname],
	CONSTRAINT [CK_contacts_del] CHECK ([del] = '1' or [del] = '0')
GO

CREATE  INDEX [IX_contacts_user_id] ON [dbo].[contacts]([user_id]) ON [PRIMARY]
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


ALTER TABLE [dbo].[identities] ADD 
	CONSTRAINT [DF_identities_user] DEFAULT ('0') FOR [user_id],
	CONSTRAINT [DF_identities_del] DEFAULT ('0') FOR [del],
	CONSTRAINT [DF_identities_standard] DEFAULT ('0') FOR [standard],
	CONSTRAINT [DF_identities_name] DEFAULT ('') FOR [name],
	CONSTRAINT [DF_identities_organization] DEFAULT ('') FOR [organization],
	CONSTRAINT [DF_identities_email] DEFAULT ('') FOR [email],
	CONSTRAINT [DF_identities_reply] DEFAULT ('') FOR [reply-to],
	CONSTRAINT [DF_identities_bcc] DEFAULT ('') FOR [bcc],
	CONSTRAINT [DF_identities_html_signature] DEFAULT ('0') FOR [html_signature],
	 CHECK ([standard] = '1' or [standard] = '0'),
	 CHECK ([del] = '1' or [del] = '0')
GO

CREATE  INDEX [IX_identities_user_id] ON [dbo].[identities]([user_id]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[messages] ADD 
	CONSTRAINT [DF_messages_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_messages_del] DEFAULT (0) FOR [del],
	CONSTRAINT [DF_messages_cache_key] DEFAULT ('') FOR [cache_key],
	CONSTRAINT [DF_messages_created] DEFAULT (getdate()) FOR [created],
	CONSTRAINT [DF_messages_idx] DEFAULT (0) FOR [idx],
	CONSTRAINT [DF_messages_uid] DEFAULT (0) FOR [uid],
	CONSTRAINT [DF_messages_subject] DEFAULT ('') FOR [subject],
	CONSTRAINT [DF_messages_from] DEFAULT ('') FOR [from],
	CONSTRAINT [DF_messages_to] DEFAULT ('') FOR [to],
	CONSTRAINT [DF_messages_cc] DEFAULT ('') FOR [cc],
	CONSTRAINT [DF_messages_date] DEFAULT (getdate()) FOR [date],
	CONSTRAINT [DF_messages_size] DEFAULT (0) FOR [size]
GO

CREATE  INDEX [IX_messages_user_id] ON [dbo].[messages]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [IX_messages_cache_key] ON [dbo].[messages]([cache_key]) ON [PRIMARY]
GO

CREATE  INDEX [IX_messages_uid] ON [dbo].[messages]([uid]) ON [PRIMARY]
GO

CREATE  INDEX [IX_messages_created] ON [dbo].[messages]([created]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[session] ADD 
	CONSTRAINT [DF_session_sess_id] DEFAULT ('') FOR [sess_id],
	CONSTRAINT [DF_session_created] DEFAULT (getdate()) FOR [created],
	CONSTRAINT [DF_session_ip] DEFAULT ('') FOR [ip]
GO

CREATE  INDEX [IX_session_changed] ON [dbo].[session]([changed]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[users] ADD 
	CONSTRAINT [DF_users_username] DEFAULT ('') FOR [username],
	CONSTRAINT [DF_users_mail_host] DEFAULT ('') FOR [mail_host],
	CONSTRAINT [DF_users_alias] DEFAULT ('') FOR [alias],
	CONSTRAINT [DF_users_created] DEFAULT (getdate()) FOR [created]
GO

CREATE  UNIQUE INDEX [IX_users_username] ON [dbo].[users]([username],[mail_host]) ON [PRIMARY]
GO

CREATE  INDEX [IX_users_alias] ON [dbo].[users]([alias]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[identities] ADD CONSTRAINT [FK_identities_user_id] 
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[contacts] ADD CONSTRAINT [FK_contacts_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[contactgroups] ADD CONSTRAINT [FK_contactgroups_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[cache] ADD CONSTRAINT [FK_cache_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[messages] ADD CONSTRAINT [FK_messages_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[contactgroupmembers] ADD CONSTRAINT [FK_contactgroupmembers_contactgroup_id]
    FOREIGN KEY ([contactgroup_id]) REFERENCES [dbo].[contactgroups] ([contactgroup_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

ALTER TABLE [dbo].[contactgroupmembers] ADD CONSTRAINT [FK_contactgroupmembers_contact_id] 
    FOREIGN KEY ([contact_id]) REFERENCES [dbo].[contacts] ([contact_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

