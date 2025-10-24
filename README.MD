# UserPlay Nette project

Setup:
- prepare `.env` file based on `.env.example`, plain copy is enough
- run `docker compose up --build`

To purge all caches completely and restore default
- run `docker compose -f docker-compose.dev.yml exec -it userplay composer purge`

DB related commands:
- `docker compose -f docker-compose.dev.yml exec -it userplay vendor/bin/phinx` migrations
- `docker compose -f docker-compose.dev.yml exec -it userplay vendor/bin/phinx status -c temp/phinx.json -e all` current state
- `docker compose -f docker-compose.dev.yml exec -it userplay vendor/bin/phinx migrate -c temp/phinx.json -e all` update to latest
- `docker compose -f docker-compose.dev.yml exec -it userplay vendor/bin/phinx rollback -c temp/phinx.json -e all --target 0` rollback back to empty

Other commands:
- `docker compose -f docker-compose.dev.yml exec -it userplay /bin/bash` to peek inside
- `docker compose -f docker-compose.dev.yml exec -it userplay composer phpstan` phpstan lvl 8 test
- `docker compose -f docker-compose.dev.yml exec -it userplay composer fixcs` automatically re-format based on fixed ruleset
- `docker compose -f docker-compose.dev.yml exec -it userplay composer testcs` check formatting


# DB structure:

Mysql used, but does not really matter, switchable directly to different make just by few changes.

    +----------------------+
    |        user          |
    +----------------------+
    | id (PK)              |
    | email (UNIQUE)       |
    | date_of_birth        |
    | name                 |
    +----------------------+
    | 1
    |
    | ∞
    +---------------------------+
    |      user_audit_log       |
    +---------------------------+
    | id (PK)                   |
    | user_id (FK → user.id)    |
    | changes                   |
    | change_type (ENUM)        |
    | changed_at (DATETIME)     |
    +---------------------------+

Every change to user is audited.

# Architecture

* UI+API together in single app with different bootstrap methods to be bit more performant but still consistent codewise.
* Phinx used for migrations instead of Nettrine. Mainly as it felt too much to import whole Doctrine for such small project and also.. well fun.
* Testing done via .http tests against API - quick, convenient, definite ( no mocks )
* Next steps: better tracy integration management, DTO instead of arrays in API ( deleted later, felt bit too complex for purpose ), more tests - with db rollback and seeds, github/lab test integration for CI, https



