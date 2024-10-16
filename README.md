# Run the migrations

```
php public\migrate.php migrate
php public\migrate.php migrate '20240927150812_CreateUsersTable'
php public\migrate.php rollback
php public\migrate.php rollback '20240927150812_CreateUsersTable'
```