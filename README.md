# CatUnicornKiller System

CatUnicornKiller System (CUK) is web-based exchange system made for IFMSA student organization needs. The system is divided into two parts, the private one and public one. More detailed description of all features can be found in [User Documentation](https://github.com/CatUnicornKiller/user-doc/wiki). 

The private part is accessible only through registration and after successful login. These parts are intended to be used by hosting country officers and incomings. There are many features but the most important are on-demand loading of information about incomings/outgoings from [ifmsa.org](www.ifmsa.org) and also creating of events by hosting officers. To these events incomings can sign-up and even pay for them using integrated payment gateway. 

Public part of the system is for now consisting of two subparts, Feedback and Showroom. Showroom is page where profiles of selected officers from hosting country can be found, this is kind of something like hall of fame for the greatest among greatest. The other part, feedback is collection of opinions of people who went abroad through IFMSA exchange program and who want to share their experiences from their travels. Feedback to all countries can be filled by whoever visits this section. 

## Installation

Installation is in more detail described in [Programmer Documentation](https://github.com/CatUnicornKiller/programmer-doc/wiki). Follows brief installation description:

1. Clone the git repository
2. Run `composer install`
3. Create a database and fill in the access information in `app/config/config.local.neon` (for an example, see `app/config/config.local.example.neon`)
4. Create directories for images which was specified in configuration
5. Setup the database schema by running `php index.php orm:schema-tool:update --force`
6. Fill database with initial values by running `php index.php db:fill`

Do not forget to make directories `temp/` and `log/` writable.

## Web Server Setup

The simplest way to get started is to start the built-in PHP server in the root directory of your project:

	php -S localhost:4000 -t .

Then visit `http://localhost:4000` in your browser to see the welcome page.

## Security Warning

It is CRITICAL that whole `app/`, `log/` and `temp/` directories are not accessible directly via a web browser. See [security warning](https://nette.org/security-warning).

## Requirements

PHP 7.0 or higher.

## Documentation

There is of course in-code documentation but aside that, there are two options:

* [User Documentation](https://github.com/CatUnicornKiller/user-doc/wiki)
* [Programmer Documentation](https://github.com/CatUnicornKiller/programmer-doc/wiki)

## License

[MIT License](LICENSE)
