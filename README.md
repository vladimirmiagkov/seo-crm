# SEO CRM (backend)

## About project
SEO CRM for SEO specialists and clients.<br>
This is backend part of the project.<br>
See: [Frontend part](https://github.com/vladimirmiagkov/seo-crm-frontend)

What is this? (todo: write intro or video)

This is an attempt to completely rewrite the [old project](http://www.rsite.ru/en/lastprojects/crm-robot-automation-seo-promotion-sites)
with separated frontend and backend.<br>
??Highload.<br>
??Support: horizontal sharding (ElasticSearch)<br>

## Project status
Slow development, at free time.<br>
Features implemented: 15%

## Built With (current stack)

Frontend:
* [Angular 4](https://angular.io)

  tests:
  * [Protractor](http://www.protractortest.org/)
  * [Karma](https://karma-runner.github.io)

Backend:
* [Symfony 3](https://symfony.com)

  tests:
  * BDD [Behat](http://behat.org)
  * TDD [PhpSpec](http://www.phpspec.net)
  * [PhpUnit](https://phpunit.de)

Other:
* Docker: Easy environment.
* ElasticSearch: Big data. We store downloaded sites data here.
* Redis: Fast cache. Cross-process synchronization manager.
* PhantomJS: Remote Javascript browser. We download full copy of sites with Javascript support.

## Install

(todo: write installation process.)

## Contributing

This is an open source, community-driven project.

## Authors

* **Vladimir Miagkov** - *Initial work* - [RSITE DEVELOPMENT](http://www.rsitedevelopment.com)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

