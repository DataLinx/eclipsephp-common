# Eclipse common package

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/eclipsephp/common)
![Packagist Version](https://img.shields.io/packagist/v/eclipsephp/common)
![Packagist Downloads](https://img.shields.io/packagist/dt/eclipsephp/common)
[![Tests](https://github.com/DataLinx/eclipsephp-common/actions/workflows/test-runner.yml/badge.svg)](https://github.com/DataLinx/eclipsephp-common/actions/workflows/test-runner.yml)
[![codecov](https://codecov.io/gh/DataLinx/eclipsephp-common/graph/badge.svg?token=1HKSY5O6IW)](https://codecov.io/gh/DataLinx/eclipsephp-common)
[![Conventional Commits](https://img.shields.io/badge/Conventional%20Commits-1.0.0-%23FE5196?logo=conventionalcommits&logoColor=white)](https://conventionalcommits.org)
![Packagist License](https://img.shields.io/packagist/l/eclipsephp/common)

## About
This package contains all common non-opinionated code that is used in our Eclipse Filament plugins.

## Requirements

- PHP 8.3 / 8.4 / 8.5
- Laravel 11 / 12
- Filament 5
- Laravel Scout

See [composer.json](composer.json) for details.

## Usage
```shell
  composer require eclipsephp/common
````

## Contributing

### Issues

If you have some suggestions on how to make this package better, please open an issue or even better, submit a pull
request.

Should you want to contribute, please see the development guidelines in
the [DataLinx PHP package template](https://github.com/DataLinx/php-package-template).

### Development

1. All development is subject to
   our [PHP package development guidelines](https://github.com/DataLinx/php-package-template/blob/bc39ae340e7818614ae2aaa607e97088318dd754/docs/Documentation.md).
2. Our [Filament app development docs](https://datalinx.github.io/eclipsephp-app/) will also be helpful.
3. Any PRs will generally need to adhere to these before being merged.

#### Requirements

See [here](https://datalinx.github.io/eclipsephp-app/introduction/requirements.html).

#### Get started

1. Clone the git repo
2. Start the Lando container
    ```shell
    lando start
    ````
3. Install dependencies (this also runs the setup composer script)
    ```shell
    lando composer install
    ````
4. You can now develop and run tests. Happy coding 😉

#### Workbench + Lando (browser testing)

This package ships with a minimal Testbench Workbench, so you can run the Filament UI without a separate app:

1. Clone the repository
2. Start the container
   ```shell
   lando start
   ```

3. Open the admin panel at `https://eclipse-plugin-template.lndo.site/admin`

**No login required** – you'll be automatically signed in as a test user with full permissions.

Notes:

- The container serves `workbench/public` as the webroot.
- Use `lando test` for `package:test` and `lando testbench` for other Testbench commands.
- No Telescope, websockets or health checks are enabled to keep the setup minimal.

### Changelog

All notable changes to this project are automatically documented in the [CHANGELOG.md](CHANGELOG.md) file using the
release workflow, based on the [release-please](https://github.com/googleapis/release-please) GitHub action.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

For all this to work, commit messages must follow the [Conventional commits](https://www.conventionalcommits.org/)
specification, which is also enforced by a Git hook.
