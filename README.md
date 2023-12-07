# Subscription API Workshop
## Requirements
- PHP 8.1
- MySQL
- Composer

## Configuration
You can configure environment variables in the .env file after composer install.

### Environment Variables
-  Z_ENDPOINT = {url}
-  Z_ACCESS_KEY = {access_key} 
-  Z_ACCESS_SECRET = {access_secret}
-  Z_APP_ID=128

## Installation

You can run the code below to install the project setup.
```sh
composer install
```

You can run the code below to database migration
```sh
php artisan migrate
```

You can run the code below to create jwt secret keys.
```sh
php artisan jwt:secret
```

## Run

You can use the code below to run the server.
```sh
php artisan serve
```

## Worker

This command can be added to cron to create jobs and can be run at the desired interval.
```sh
php artisan app:create-check-subscription-job
```

## Tests
You can use the code below to run the tests.
```sh
php artisan test
```

## Comments
- I used file cache. It can be used in redis or memcache.

## API Documentation
Postman Documentation: https://documenter.getpostman.com/view/12629017/2s9YeN3UTA

 
