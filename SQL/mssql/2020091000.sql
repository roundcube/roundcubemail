CREATE TABLE [dbo].[collected_addresses] (
	[address_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[changed] [datetime] NOT NULL ,
	[name] [varchar] (255) COLLATE Latin1_General_CI_AI NOT NULL ,
	[email] [varchar] (255) COLLATE Latin1_General_CI_AI NOT NULL ,
	[type] [int] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[collected_addresses] WITH NOCHECK ADD 
	CONSTRAINT [PK_collected_addresses_address_id] PRIMARY KEY  CLUSTERED 
	(
		[address_id]
	) ON [PRIMARY] 
GO

ALTER TABLE [dbo].[collected_addresses] ADD 
	CONSTRAINT [DF_collected_addresses_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_collected_addresses_changed] DEFAULT (getdate()) FOR [changed],
	CONSTRAINT [DF_collected_addresses_name] DEFAULT ('') FOR [name],
GO

CREATE UNIQUE INDEX [IX_collected_addresses_user_id] ON [dbo].[collected_addresses]([user_id],[type],[email]) ON [PRIMARY]
GO

ALTER TABLE [dbo].[collected_addresses] ADD CONSTRAINT [FK_collected_addresses_user_id]
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

