# SQL Tools

Execute raw SQL queries directly against the store database. For expert use only.

> [!NOTE]
> An "expert use only" warning is displayed each time you load this page as a reminder that incorrect queries can cause permanent damage.

## Database Information

| Field | Description |
| --- | --- |
| Server Version | The MySQL/MariaDB server version. |
| Host | The database server hostname. |
| Username | The database user. |
| Table Prefix | The prefix applied to all CubeCart tables (e.g. `CubeCart_`). |

## Query Execution

Enter SQL statements in the text area and submit. Results show the number of affected rows or any error messages.

To run multiple queries in a single submission, separate them with `; #EOQ` as the delimiter.

> [!NOTE]
> Incorrect SQL queries can permanently damage your database. Always create a database backup before running queries. This tool is intended for advanced users and developers only.
