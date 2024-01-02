# Database Schema <!-- omit in toc -->

## ToC <!-- omit in toc -->

1. [Database](#database)
2. [Tables](#tables)
	1. [calendar](#calendar)
		1. [SQL Creation](#sql-creation)

## Database

```sql
CREATE OR REPLACE DATABASE calendar_track;
```

## Tables

### calendar

| Column Name | Description    | Datatype   |
| ----------- | -------------- | ---------- |
| id          | Auto-generated | BINARY(16) |
| name        |                | VARCHAR    |
| description |                | VARCHAR    |

#### SQL Creation

```sql
CREATE TABLE `calendar` (
	id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	ics_link MEDIUMTEXT NOT NULL,
	active BOOLEAN NOT NULL DEFAULT true
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

```
