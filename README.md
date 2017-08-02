# SEO CRM for SEO specialists.

**Please, no PR at this point. Until alpha.**

## About project
What is this? (todo: write intro or video)

This is an attempt to completely rewrite the [old project](http://www.rsite.ru/en/lastprojects/crm-robot-automation-seo-promotion-sites)
with separate frontend and backend.<br>
Prepare for highload.<br>
??Support: horizontal sharding (ElasticSearch)<br>

This is Backend part of the project.<br>
See: Frontend part [no link yet]

Project status: slow development, at free time.<br>
Features implemented: 15%


## Built With (current stack)

Frontend:
* [Angular 4](https://angular.io)

  tests:
  * ...

<br>

Backend:
* [Symfony 3](https://symfony.com)

  tests:
  * BDD [Behat](http://behat.org)
  * TDD [PhpSpec](http://www.phpspec.net)
  * [PhpUnit](https://phpunit.de)

<br>

Other:
* Docker: Easy environment.
* ElasticSearch: Big data. We store downloaded sites data here.
* Redis: Fast cache. Cross-process synchronization manager.
* PhantomJS: Remote Javascript browser. We download full copy of sites with Javascript support.



## Contributing

Please no pr until alpha.

## Authors

* **Vladimir Miagkov** - *Initial work* - [RSITE DEVELOPMENT](http://www.rsitedevelopment.com)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

