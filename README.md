Gameserver-Sponsor
====

This is the Source Code archive from Gameserver-Sponsor.me. 


## Requirements

### WebHost

* min. PHP 7.2 with Memcached Extension
* NodeJS 8.0 LTS
* Memcached
* RabbitMQ
* MariaDB 10.2
* gss-worker

### Game Host

* NodeJS 8.0 LTS
* MySQL

## Installation

### Web Host

* Checkout this repository to webroot
* Install dependencies ```composer install --no-dev --no-plugins```
* Build Frontend Files
    * ``cd public/src``
    * ``npm install``
    * ``gulp prod``
* Copy `.env.example` to `.env` and fill all fields
* Import the `install.sql` to your database
* The webhost should be now ready

### Game Host

* Setup will follow