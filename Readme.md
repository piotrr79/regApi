#### Overview:
* Minimal requirements to run application is [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git), [PHP](http://php.net/manual/en/install.php) and [composer](https://getcomposer.org/download/) installed
* Additionally install [Symfony-cli](https://symfony.com/download)
* Set .env file based on local.env and complete it with chosen DELIVERY_MAIL
* With Git, composer, PHP and symfony-cli installed run `git clone`, `composer install` and finally to start server `symfony server:start`
* Built-in Symfony server will be running under: `http://127.0.0.1:8000`
* Api is available under http://127.0.0.1:8000/api/register
* Postman payload, cUrl:
```
curl --location --request POST 'http://127.0.0.1:8000/api/register' \
--form 'filename=@"/path/file.png"' \
--form 'Name="User Name"' \
--form 'Email="email@emmail.com"'
```

