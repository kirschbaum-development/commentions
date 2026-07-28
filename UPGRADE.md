![](screenshots/commentions-logo.png)

![Laravel Supported Versions](https://img.shields.io/badge/laravel-10.x/11.x/12.x/13.x-green.svg)
![Filament Supported Versions](https://img.shields.io/badge/filament-3.x/4.x/5.x-green.svg)
[![CI](https://github.com/kirschbaum-development/commentions/actions/workflows/ci.yml/badge.svg)](https://github.com/kirschbaum-development/commentions/actions/workflows/ci.yml)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/kirschbaum-development/commentions.svg?style=flat-square)](https://packagist.org/packages/kirschbaum-development/commentions)
[![Total Downloads](https://img.shields.io/packagist/dt/kirschbaum-development/commentions.svg?style=flat-square)](https://packagist.org/packages/kirschbaum-development/commentions)

## Upgrading

Run Migrations:

```bash
php artisan vendor:publish --tag="commentions-migrations"
php artisan migrate
```

Update Filament Assets:
```bash
php artisan filament:upgrade
php artisan filament:assets
```
