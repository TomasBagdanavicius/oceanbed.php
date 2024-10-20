# Release Steps

1. Run task "Remove special comments in all file"
2. Run PHP CS Fixer
    - Dry run first: `./vendor/bin/php-cs-fixer fix --dry-run --diff`
    - Fix using `./vendor/bin/php-cs-fixer fix` or `composer fix`
3. Run unit tests in Stonetable
4. Run `./scripts/prepare-release.sh -v <version>`
    - `./scripts/prepare-release.sh -v 0.1.1`
5. Run `./scripts/push-release.sh -v <version> -c "<commit message>" -t "<tag message>"`
    - `./scripts/push-release.sh -v 0.1.1 -c "Added v0.1.1 modifications" -t "Releasing version 0.1.1: improvements and fixes"`
6. Create new release on Github.com