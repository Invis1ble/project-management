Project Management
===============================

![CI Status](https://github.com/Invis1ble/project-management/actions/workflows/ci.yml/badge.svg?event=push)
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)


Getting Started
---------------

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start the project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers
6. Run `docker exec php bin/console secrets:set GITLAB_ACCESS_TOKEN` to set GitLab Access Token
7. Run `docker exec php bin/console secrets:set JIRA_ACCESS_TOKEN` to set Jira Access Token
8. Run `docker exec php bin/console secrets:set POSTGRES_PASSWORD` to set PostgreSQL Password
9. Run `docker exec php bin/console secrets:set RABBITMQ_PASSWORD` to set RabbitMQ Password.

In the `.env` file set following variables:

- `GITLAB_URL`
- `GITLAB_BACKEND_PROJECT_ID`
- `GITLAB_FRONTEND_PROJECT_ID`
- `JIRA_URL`
- `JIRA_USERNAME`
- `JIRA_PROJECT_KEY`
- `JIRA_SPRINT_FIELD_ID` - `customfield` id for Sprint section in Jira issues
- `JIRA_SPRINT_BOARD_ID`
- `JIRA_MERGE_REQUEST_FIELD_ID` - `customfield` id for Development section in Jira issues


Usage
-----

Run queue workers:
```sh
docker compose exec php bin/console messenger:consume async -vvv
```


Prepare release command:
```sh
docker compose exec -it php bin/console release:prepare
```


Monitoring
----------

RabbiMQ Management http://localhost:15672/#/queues/%2F/messages


License
-------

[The MIT License](./LICENSE)
