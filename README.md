# Metal-Addict-back

## General info
You don't know where you can share reviews and/or pictures of the bests heavy metal concerts of your life ?
This project provides an api which can be consumed by [Metal-Addict-front](https://github.com/fmillox/Metal-Addict-front)
	
## Technologies
Project is created with:
* Symfony version: 5.2
* LexikJWTAuthenticationBundle version: 2.11
* NelmioCorsBundle version: 2.1
	
## Setup
Copy the **.env** file in the root directory and rename it **.env.local**. Change the content of the file with your parameters.

```
DATABASE_URL="mysql://user:ben1234@127.0.0.1:3306/metal_addict"  // for example

SETLIST_API_URL="https://api.setlist.fm/rest/1.0"
SETLIST_API_KEY="46ffb5c5-f0c4-4341-a1ab-889647d9a994"  // for example

FANART_API_URL="http://webservice.fanart.tv/v3"
FANART_API_KEY="c796b65b-22f0-457f-813c-33ece3bb186b"  // for example

CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'

JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=4399ee52-043d-4e9a-bec6-dde9c60b32c7  // for example
```

To run this project, install it locally using composer:

```
$ cd ../Metal-Addict-back
$ composer install
$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate
$ php bin/console doctrine:fixtures:load
$ php -S 0.0.0.0:8000 -t public
```
