# Symfony Backend

## Setup Instructions

1. Run `composer install`
2. Configure `.env.local` with your DB credentials
3. Run:
   ```
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   symfony serve
   ```

## API Endpoints

- `GET /api/tracks`
- `POST /api/tracks`
- `PUT /api/tracks/{id}`
