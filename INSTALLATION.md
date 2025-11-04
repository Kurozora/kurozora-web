# Installation

## Table of Contents
* [Requirements](#requirements)
* [Installation](#1-get-the-source)
    * [1. Get the source](#1-get-the-source)
    * [2. Install Composer dependencies](#2-install-composer-dependencies)
    * [3. Set up environment variables](#3-set-up-environment-variables)
    * [4. Set up database](#4-set-up-database)
* [Notes](#notes)

## Requirements

You are, of course, free to install this project your way. However if you plan on following this install guide, there are a few requirements:

*   [PHP](https://www.php.net/)
*   [Composer](https://getcomposer.org/)
*   [Laravel Valet](https://github.com/laravel/valet)

For detailed steps on how to install these requirements, please refer to their respective install guides. For information on what version to install, refer to our **[composer.json](composer.json)** file. 

## 1. Get the source
Clone the repository using git. Make sure to do this in a folder that is parked by Valet.  
```bash
$ git clone https://github.com/Kurozora/kurozora-web.git && cd kurozora-web
``` 

## 2. Install Composer dependencies
Use Composer to install all of the project's dependencies.
```bash
$ composer install
```

## 3. Set up environment variables
Create a copy of the `.env.example` file ...
```bash
$ cp .env.example .env
```
... and modify the values accordingly.

## 4. Set up database
Create a database and configure the details in your `.env` file. After that, you can migrate the database to get the correct schema.
```bash
$ php artisan migrate
```
Optionally, you can choose to also seed the database with test data.
```bash
$ php artisan migrate:fresh --seed
```

## 5. Link storage
Create a symlink between `storage/app/public` and `public/storage` to make uploaded images and files accessible from the website.
```bash
$ php artisan storage:link
```

## Notes
If you are using PHPStorm, you may suppress the "multiple definitions exist" notice by disabling the following option in the preferences.
```text
Settings | Editor | Inspections | PHP | Undefined symbols | Multiple class declarations 
```
