# Findbride Release Management

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.
6. Run `docker exec php bin/console secrets:set GITLAB_ACCESS_TOKEN` to set GitLab Access Token
7. Run `docker exec php bin/console secrets:set JIRA_ACCESS_TOKEN` to set Jira Access Token
8. Run `docker exec php bin/console secrets:set POSTGRES_PASSWORD` to set PostgreSQL Password
8. Run `docker exec php bin/console secrets:set RABBITMQ_PASSWORD` to set RabbitMQ Password

In the `.env` file set following variables:

- GITLAB_URL
- GITLAB_BACKEND_PROJECT_ID
- GITLAB_FRONTEND_PROJECT_ID
- JIRA_URL
- JIRA_USERNAME
- JIRA_PROJECT_KEY

## Monitoring

RabbiMQ Management http://localhost:15672/#/queues/%2F/messages

