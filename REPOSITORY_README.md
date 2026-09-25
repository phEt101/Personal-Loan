# Customer History Refactor

## Run

```sh
docker exec personal-loan-app php artisan migrate:fresh --seed --force
```

Open `http://localhost:8080/customer-history` and sign in with `user@bigmoneyplus.co.th` / `P@ssw0rd`.

## Review

- Customer history is the only remaining customer workflow module.
- H Meter master CSV data is imported by `HMeterMasterSeeder`.
- Consent, consent review, home, and their database tables have been removed.