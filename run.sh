docker compose rm cache
docker compose rm db
# docker compose -f docker-compose-prod.yml --compatibility up --force-recreate --build
docker compose -f docker-compose.yml --compatibility up --force-recreate --build
