Project Management
==================

![CI Status](https://github.com/Invis1ble/project-management/actions/workflows/ci.yml/badge.svg?event=push)
[![codecov](https://codecov.io/gh/Invis1ble/project-management/graph/badge.svg?token=296M82SE17)](https://codecov.io/gh/Invis1ble/project-management)
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

[![codecov](https://codecov.io/gh/Invis1ble/project-management/graphs/tree.svg?token=296M82SE17)](https://codecov.io/gh/Invis1ble/project-management)


Getting Started
---------------

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start the project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers
6. Run `docker compose exec php bin/console secrets:set GITLAB_ACCESS_TOKEN` to [set GitLab Access Token](https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html#create-a-personal-access-token)
7. Run `docker compose exec php bin/console secrets:set JIRA_ACCESS_TOKEN` to set Jira Access Token
8. Run `docker compose exec php bin/console secrets:set POSTGRES_PASSWORD` to set PostgreSQL Password
9. Run `docker compose exec php bin/console secrets:set RABBITMQ_PASSWORD` to set RabbitMQ Password.

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


Publish hotfix command:
```sh
docker compose exec -it php bin/console pm:hotfix:publish
```


Prepare release command:
```sh
docker compose exec -it php bin/console pm:release:prepare
```


Monitoring
----------

RabbiMQ Management http://localhost:15672/#/queues/%2F/messages


Development
-----------

### Check for Coding Standards violations

Run PHP_CodeSniffer checks:

```sh
docker compose exec -it php bin/php_codesniffer
```

Run PHP-CS-Fixer checks:

```sh
docker compose exec -it php bin/php-cs-fixer
```

Run Rector checks:

```sh
docker compose exec -it php bin/rector
```


Testing
-------

To get started with testing, you need to create a test database first and run migrations:

```sh
docker compose exec -it php bin/console -e test doctrine:database:create
docker compose exec -it php bin/console -e test doctrine:migrations:migrate
```

After creating the database, you can run unit tests:

```sh
docker compose exec -it php bin/phpunit
```

To run unit tests with coverage, you need to enable XDEBUG first:

```sh
XDEBUG_MODE=coverage docker compose up -d --wait
docker compose exec -it php bin/phpunit --coverage-clover var/log/coverage-clover.xml
```


License
-------

[The MIT License](./LICENSE)
