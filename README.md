# Library API

REST API peminjaman buku pakai Laravel + Sanctum.

## Install

```
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Login: `justSMBOelot@gmail.com` / `password`

## Endpoint

```
POST   /api/register
POST   /api/login
POST   /api/logout

GET|POST         /api/categories
GET|PUT|DELETE   /api/categories/{id}

GET|POST         /api/books
GET|PUT|DELETE   /api/books/{id}

GET    /api/loans               buku yang dipinjam
POST   /api/loans               pinjam buku (book_id)
POST   /api/loans/{id}/return   kembalikan buku
```
