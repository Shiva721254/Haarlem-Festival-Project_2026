# Database

The schema is now managed by **forward-only migrations**, not a single
`schema.sql` dump. Migration files live in `app/database/migrations/` (inside
the `app` folder so they are available within the `php` container).

## Apply migrations

```bash
docker compose exec php php database/migrate.php
```

The runner tracks applied files in a `migrations` table and only runs new ones,
so it is safe to run repeatedly.

## Add a migration

Create a new file in `app/database/migrations/` with the next zero-padded
number, e.g. `006_add_orders_index.sql`. Filenames are applied in sort order.
Migrations are never edited once applied to a shared database — add a new one.
