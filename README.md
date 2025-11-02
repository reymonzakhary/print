<p align="center"><img src="https://prindustry.com/wp-content/uploads/2021/02/prindustry_logo-kleur_regular.png" width="400"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## About CEC System 
CEC is a SaaS project
###  Installation
This application using docker, so you need install docker on your machine. <br>
Development steps
- First for development you need to run `docker-compose up -d`
- Login into the gateway container `docker exec -it gateway bash`
- then run `composer install`
- `php artisan key:generate`
- `php artisan migrate --seed`
- `php artisan passport:install`
<br>
Then you need to copy the Password grant client key and secret to your .env file <br>
PASSWORD_CLIENT_ID={key}<br>
PASSWORD_CLIENT_SECRET={secret}
- `php artisan modules:sys:update`
- `php artisan modules:tenancy:migrate`
- `php artisan module:enable`
- 
## Documentation

To generate api documentation, run the flowing command inside your gateway service.
``php artisan scribe:generate``
This will generate api documentation with all the routes in the system.

# CMS

## Tvs

TVs used as an extra fields added to the resources enable you to carry additional data 

Using **inside** the template
```[[*block.text? &name=`{your_tv_name}` &lable=`the lable that occure`]]```

Using inside **injected** resources
```[[*block.{your_tv_name}]]```

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Reymon Zakhary via [reymon@prindustry.nl](mailto:reymon@prindustry.nl). All security vulnerabilities will be promptly addressed.

## License

The CEC framework is not open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
