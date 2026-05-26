# wCore

wCore: Laravel Modular Hub

Laravel Version 12.0 (Branch-main) 

## Requirements

- PHP >= 8.2
- Ctype PHP Extension
- cURL PHP Extension
- DOM PHP Extension
- Fileinfo PHP Extension
- Filter PHP Extension
- Hash PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PCRE PHP Extension
- PDO PHP Extension
- Session PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

## Installation

- Clone the repo and `cd` into it
- Run `composer update`
- Run `composer install`
- Rename or copy `.env.example` file to `.env`
- Run `php artisan key:generate`
- Set your database credentials in your `.env` file
- Edit db.sql
- On terminal `php artisan tinker`
- After > `bcrypt('pass123')`. Password can be anything.
- Result `$2y$12$AqjCQO/pu5oREqZTYIsOn.N4FwHBwh9t.qGBuNhXTDNuKjlZnUg6y`
- Copy and past at db.sql
- Create database `C:\xampp\mysql\bin\mysql.exe -u root -p < db.sql`
- Run `php artisan serve`  , `php artisan serve --port=8080`,`php artisan serve --host=0.0.0.0`
- Run `composer require spatie/laravel-permission`



## Note

Recommend to install this preset on a project that you are starting from scratch, otherwise your project's design might break.

## Credits

wCore HTPN uses some open-source third-party libraries/packages, many thanks to the web community.

- Laravel - Open source framework.
- LaravelEasyNav - Making managing navigation in Laravel easy.
- wCore HTPN - Thanks to Start Bootstrap.


## Add More module Depend on what Purpose



### Notes
- `modules/` → main folder where all sub-apps/modules live.  
- Each module is self-contained:  
  - `src/` → contains MVC structure (Controllers, Models, Services, Providers).  
  - `routes/` → module-specific routes.  
  - `database/migrations/` → module database tables.  
  - `resources/views/` → module blade templates.  
  - `public/assets/` → module assets (CSS, JS, images).  
  - `permissions.php` → module permissions configuration.  
- `storage/modules/` → stores uploaded module zip files and backups.  


___________________________________

wcore-app/

├── modules/                 👈 ALL SUB-APPS LIVE HERE

├── storage/

|   ├── modules/

        ├── uploaded_zips/
        
        └── backups/

___________________________________

HR_Module_v1.2.zip
│
├── module.json
├── src/
│   ├── Controllers/
│   │   └── HRController.php
│   │
│   ├── Models/
│   │   └── Staff.php
│   │
│   ├── Services/
│   │   └── HRService.php
│   │
│   └── Providers/
│       └── HRServiceProvider.php
│
├── routes/
│   └── web.php
│
├── database/
│   └── migrations/
│       └── create_staff_table.php
│
├── resources/
│   └── views/
│       └── index.blade.php
│
├── public/
│   └── assets/
│
└── permissions.php


## Preview

`login`

<img src="https://i.imgur.com/sKVD7I2.png">

***

`register`

<img src="https://i.imgur.com/mIZmVjz.png">

***

`dashboard`

<img src="https://i.imgur.com/mjM49Uq.png">

***

`profile`

<img src="">

***

`logout`

<img src="">

## License

Licensed under the [MIT](LICENSE) license.
