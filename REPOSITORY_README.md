# Customer History Refactor

## Run

```sh
docker exec personal-loan-app php artisan migrate:fresh --seed --force
```

Open `http://localhost:8080/customer-history` and sign in

## Review

- Customer history is the only remaining customer workflow module.
- Master lookup CSV data is imported by `MasterLookupSeeder`.
- Consent, consent review, home, and their database tables have been removed.
