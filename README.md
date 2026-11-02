# Library API

REST API sederhana untuk peminjaman buku, dibuat dengan Laravel 13 + Sanctum.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

Default pakai SQLite. Kalau mau MySQL tinggal ganti `DB_*` di `.env`.

Jalankan test:

```bash
php artisan test
```

## Auth

Semua endpoint selain register/login butuh header:

```
Authorization: Bearer <token>
Accept: application/json
```

| Method | Endpoint        | Body                                                  |
|--------|-----------------|-------------------------------------------------------|
| POST   | `/api/register` | `name`, `email`, `password`, `password_confirmation`  |
| POST   | `/api/login`    | `email`, `password`                                   |
| POST   | `/api/logout`   | -                                                     |
| GET    | `/api/user`     | -                                                     |

## Kategori

| Method | Endpoint               | Keterangan                                   |
|--------|------------------------|----------------------------------------------|
| GET    | `/api/categories`      | list kategori + jumlah buku                  |
| POST   | `/api/categories`      | `name`                                       |
| GET    | `/api/categories/{id}` | detail + daftar buku                         |
| PUT    | `/api/categories/{id}` | `name`                                       |
| DELETE | `/api/categories/{id}` | gagal (422) kalau masih ada buku di kategori |

## Buku

| Method | Endpoint          | Keterangan                                                   |
|--------|-------------------|--------------------------------------------------------------|
| GET    | `/api/books`      | paginate, filter `?category_id=` dan `?search=` (judul)      |
| POST   | `/api/books`      | `category_id`, `title`, `author`, `stock`                    |
| GET    | `/api/books/{id}` | detail                                                       |
| PUT    | `/api/books/{id}` | field yang mau diubah saja                                   |
| DELETE | `/api/books/{id}` | gagal (422) kalau buku masih dipinjam                        |

## Peminjaman

| Method | Endpoint                  | Keterangan                                                 |
|--------|---------------------------|------------------------------------------------------------|
| POST   | `/api/loans`              | pinjam buku, body `book_id`. Stok berkurang 1               |
| POST   | `/api/loans/{id}/return`  | kembalikan buku. Stok bertambah 1                          |
| GET    | `/api/loans`              | daftar buku yang sedang dipinjam user, `?history=1` untuk semua riwayat |

Aturan:
- Tidak bisa pinjam kalau stok 0.
- User tidak bisa pinjam buku yang sama dua kali sebelum dikembalikan.
- Hanya peminjam yang bisa mengembalikan bukunya.
- Pengurangan stok dilakukan langsung di query (`where stock > 0`) supaya aman dari request bersamaan.
