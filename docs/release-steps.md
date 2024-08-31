# Release Steps

1. Run unit tests in Stonetable
2. Run `./scripts/prepare-release.sh -v <version>`
    - `./scripts/prepare-release.sh -v 0.1.0`
3. Run `./scripts/push-release.sh -v <version> -c "<commit message>" -t "<tag message>"`
    - `./scripts/push-release.sh -v 0.1.0 -c "chore: release version 0.1.0" -t "Initial release version 0.1.0"`
4. Create new release on Github