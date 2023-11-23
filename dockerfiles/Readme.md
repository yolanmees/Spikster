# Usage


## For Local Development
To work with this folder, you will need to have Docker installed. These are the following commands you will need to run for the project stack locally.

Make sure to create a copy of the `docker.env` file and name it `.env`

- Running the docker-compose.yaml file
```
docker compose up --build
```

- Once you see `spikster-php-1 NOTICE: ready to handle connections` in the logs, you can now nagigate to `http://localhost/login` to see the home page of the applications login screen.


---

## Maintenance Dev Commands
- Composer
```bash
docker compose run --rm composer update
```

- Artisan
```bash
docker compose run --rm artisan cache:clear
docker compose run --rm artisan view:cache
docker compose run --rm artisan migrate --seed --force
docker compose run --rm artisan config:cache

```

- Running npm to build the assets
```bash
docker compose run --rm npm install
docker compose run --rm npm run dev
```

- Reload nginx
```bash
nginx -s reload -c /conf/nginx/nginx.conf
```

## The init.sh
The init.sh shell contains the initial artisan commands. If the `/var/www/html/initialize` file is present, **these scripts will not be executed again.**

- If you want to run those commands again on your own, that would be done with the `docker compose run --rm artisan ...` command.