ALTER TABLE [dbo].[users] ALTER COLUMN [language] [varchar] (16) COLLATE Latin1_General_CI_AI NULL
GO
ALTER TABLE [dbo].[dictionary] ALTER COLUMN [language] [varchar] (16) COLLATE Latin1_General_CI_AI NOT NULL
GO
