Manual Guide

Aplikasi Absensi Karyawan – Fullstack (Laravel + Next.js)

1. Pada FrontEnd lakukan git clone pada frontend-absensi
- install depedencies (npm install)
- buat file .env.local
- masukkan dengan endpoint laravel (kalau di local saya NEXT_PUBLIC_API_URL=http://localhost:8000/api)
- Jika sudah npm run dev

2. Pada BackEnd lakukan git clone pada backend-absensi
- install depedencies (composer install)
- sesuaikan dulu .env nya dengan mysql
- kemudian lakukan (php artisan migrate) untuk membuat table di db dan table dari migrations
- setelah itu lakukan (php artisan serve) 
