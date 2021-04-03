# Running Tests

Laravel Nova comes with Dusk and Feature Tests which can be executed by following the guide below.

## Feature Tests

Feature Tests includes `Controller`, `Feature` and `Unit` tests using Testbench using `phpunit.xml.dist` configuration file

> You may customise the configuration by copying the file to `phpunit.xml`.

### Executing the Tests

Run the following command:

```
./vendor/bin/phpunit -c phpunit.xml
```

### MYSQL tests

You need to uncomment the following on `phpunit.xml` and set all the database information (if needed) on the same file:

```xml
<env name="RUN_MYSQL_TESTS" value="true"/>
```

## Postgres Tests

You need to uncomment the following on `phpunit.xml` and set all the database information (if needed) on the same file:

```xml
<env name="RUN_POSTGRES_TESTS" value="true"/>
```

## Dusk Tests

Dusk Tests by default will run tests based on configuration defined under `.env.dusk.example` which will be using MySQL database using the following setup:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nova_dusk
DB_USERNAME=root
DB_PASSWORD=
```

You may override the options by copying the file to `.env.dusk` and customize the information.

### Configuring Webpack

Nova's repository ships with a `webpack.mix.js.dist` file to help you get started contributing to Nova. 

You should create a copy of this file and name it `webpack.mix.js` and comment the line `.copy('public', '../nova-app/public/vendor/nova')`.

### Preparing the skeleton

In order to runs the tests we need to prepare the skeleton application with testing environment variables, you can do this by running:

```bash
composer run dusk:prepare
```

#### Updating assets

Whenever there's a change to CSS or JavaScript you need to run the following commands to sync assets with the skeleton:

```bash
composer run dusk:assets
```

### Executing the Tests

Run the following command:

```bash
./vendor/bin/phpunit -c phpunit.dusk.xml
```
