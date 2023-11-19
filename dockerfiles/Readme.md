# Usage


## For Local Development
To work with this folder, you will need to have Docker installed. These are the following commands you will need to run for the project stack locally.

- Running the docker-compose.yaml file
```
docker compose up --build
```

- Composer Examples
```bash
docker compose run --rm composer update
```

- Artisan examples
```bash
docker compose run --rm artisan cache:clear
docker compose run --rm artisan view:cache
docker compose run --rm artisan config:cache

```

- Running npm to build the assets (Run these at build time)
```bash
docker compose run --rm npm install
docker compose run --rm npm run dev
```

- Reload nginx
```bash
nginx -s reload -c /conf/nginx/nginx.conf
```