# Deploying to Firebase App Hosting

This app is a **PHP / Laravel** application. Firebase App Hosting runs it as a
container on Cloud Run, so PHP works out of the box. (Firebase Hosting alone
only serves static files; App Hosting is required for a server-side app.)

## 1. Prerequisites

- A Firebase project with **Blaze (pay-as-you-go)** billing — Cloud Run requires it.
- Firebase CLI ≥ 13.21.0: `npm install -g firebase-tools`
- `firebase login`
- Set your project id in `.firebaserc`.

## 2. Configure secrets

```powershell
# from the project root
firebase apphosting:secrets:set DB_PASSWORD --data-file=db_password.txt
# (or create it in the Firebase console: App Hosting > Secrets)
```

Edit `apphosting.yaml`:
- `APP_URL` → your assigned `web.app` domain.
- `DB_HOST` / `DB_DATABASE` / `DB_USERNAME` → your Cloud SQL (or other MySQL) instance.
- Reference the secret for `DB_PASSWORD` if you created one.

> For Cloud SQL, the easiest option is to enable the **Cloud SQL Admin API** and
> connect via the instance's private IP, or run the app in the same VPC as Cloud SQL.

## 3. Deploy

```powershell
# Build & push the container image
gcloud builds submit --tag gcr.io/YOUR-PROJECT-ID/citywide

# Create the backend (first time only)
firebase apphosting:backends:create --project YOUR-PROJECT-ID

# Deploy
firebase apphosting:backends:deploy --project YOUR-PROJECT-ID
```

Or link your GitHub repo and let App Hosting build automatically.

## 4. After the first deploy

```powershell
# Run migrations (from Cloud Shell or locally against the production DB)
php artisan migrate --force
php artisan config:cache
php artisan key:generate
```

Seed accounts if needed:
```powershell
php artisan db:seed
```

Sign in with the same seeded accounts (`city.admin@barangay.gov`, password `password`).

## Local container test

```powershell
docker build -t citywide .
docker run -p 8080:80 --env-file .env citywide
# open http://localhost:8080
```

## Notes

- Keep `.env` out of the image (`.dockerignore` handles it); set production
  values in `apphosting.yaml` or secrets.
- `SESSION_DRIVER=cookie` avoids needing a shared sessions table across instances.
- Uploaded files are not persistent on Cloud Run; use Cloud Storage for user
  uploads if you add a lot of images/documents.
