CREATE TABLE [dbo].[responses] (
	[response_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[changed] [datetime] NOT NULL ,
	[del] [char] (1) COLLATE Latin1_General_CI_AI NOT NULL ,
	[name] [varchar] (255) COLLATE Latin1_General_CI_AI NOT NULL ,
	[data] [text] COLLATE Latin1_General_CI_AI NOT NULL, 
	[is_html] [char] (1) COLLATE Latin1_General_CI_AI NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[responses] WITH NOCHECK ADD 
	 PRIMARY KEY  CLUSTERED 
	(
		[response_id]
	) ON [PRIMARY] 
GO

ALTER TABLE [dbo].[responses] ADD 
	CONSTRAINT [DF_responses_user] DEFAULT ('0') FOR [user_id],
	CONSTRAINT [DF_responses_del] DEFAULT ('0') FOR [del],
	CONSTRAINT [DF_responses_is_html] DEFAULT ('0') FOR [is_html],
	CHECK ([del] = '1' or [del] = '0'),
	CHECK ([is_html] = '1' or [is_html] = '0')
GO

CREATE INDEX [IX_responses_user_id] ON [dbo].[responses]([user_id]) ON [PRIMARY]
GO
ALTER TABLE [dbo].[responses] ADD CONSTRAINT [FK_responses_user_id] 
    FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id])
    ON DELETE CASCADE ON UPDATE CASCADE
GO

