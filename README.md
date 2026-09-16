# HR Management

Laravel project nền tảng cho website quản lý nhân sự với ba vai trò **Admin**, **HR** và **Employee**. Database layer và nền tảng authentication/role hiện đã được triển khai; các HR workflow sẽ được triển khai theo [docs/DEVELOPMENT-PLAN.md](docs/DEVELOPMENT-PLAN.md).

## Công nghệ

- PHP 8.3.33+
- Laravel 13.32.0
- Eloquent ORM
- Blade
- Laravel Migration, Seeder, Middleware, Form Request và Policy
- MySQL/MariaDB 8.4+
- Laragon trên Windows

Không dùng Docker, Phroute, React hoặc Vue làm frontend chính.

## Yêu cầu môi trường

- Windows với Laragon.
- PHP 8.3+ được chọn trong Laragon.
- Composer 2.x.
- MySQL/MariaDB đang chạy trong Laragon.
- Git.

## Cài đặt trên Laragon

Mở Laragon Terminal hoặc terminal đã trỏ tới PHP/Composer của Laragon, rồi chạy từ thư mục project:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Nếu dùng Git Bash, có thể sao chép environment bằng:

```bash
cp .env.example .env
php artisan key:generate
```

## Cấu hình environment

`.env` là file local và không được commit. Dùng cấu hình MySQL riêng cho project:

```env
APP_NAME="HR Management"
APP_URL=http://hr-management.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_management
DB_USERNAME=root
DB_PASSWORD=
```

Không ghi mật khẩu thật vào source hoặc `.env.example`.

## Database

1. Bật MySQL trong Laragon.
2. Tạo database development mới tên `hr_management` bằng Laragon Database hoặc MySQL client.
3. Chạy migrations và seed dữ liệu demo:

```bash
php artisan migrate --seed
```

Tài khoản demo (mật khẩu dùng chung: `demo-password`):

- `admin@example.test` — Admin
- `hr@example.test` — HR
- `employee@example.test` — Employee
- `employee2@example.test` — Employee

Không chạy `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hoặc lệnh phá dữ liệu trên database có dữ liệu.

## Chạy project

Đặt project trong thư mục `www` của Laragon, bật Apache và MySQL, rồi truy cập:

```text
http://hr-management.test
```

Document root phải trỏ tới:

```text
D:\HUIT\PHP\hr-management\public
```

Có thể chạy server development để kiểm tra nhanh:

```bash
php artisan serve
```

## Kiểm tra và lệnh hữu ích

```bash
php artisan --version
php artisan about
php artisan route:list
php artisan migrate:status
composer validate
composer dump-autoload
php artisan test
```

Chỉ chạy `php artisan migrate:status` hoặc `migrate` sau khi MySQL và database `hr_management` sẵn sàng.

## Cấu trúc project

```text
app/
├── Http/Controllers/       Controllers theo module
├── Models/                 Eloquent models
└── Providers/              Service providers
bootstrap/                  Laravel bootstrap
config/                     Configuration
database/
├── factories/              Test/development factories
├── migrations/             Versioned schema
└── seeders/                Development seed data
public/                     Document root duy nhất của web server
resources/views/            Blade templates
routes/web.php              Web routes
storage/                    Logs, cache, uploaded/local files
tests/                      Feature and unit tests
docs/                       Architecture and handoff documentation
```

## Vai trò và phạm vi

- **Admin:** dashboard, quản lý tài khoản, xem và xử lý PYC, hồ sơ; không bị bắt buộc check-in và không quản lý attendance của HR.
- **HR:** dashboard, nhân viên, phòng ban, chức vụ, attendance management, tuyển dụng/thôi việc, task, PYC, hồ sơ và check-in/out.
- **Employee:** dashboard, task, PYC, hồ sơ và check-in/out.

Chi tiết quyền và flow nằm trong [docs/AUTHORIZATION.md](docs/AUTHORIZATION.md) và [docs/BUSINESS-FLOWS.md](docs/BUSINESS-FLOWS.md).

## Development phases

1. Authentication, roles và authorization.
2. Migrations, models, relationships và seeders.
3. Employee/Department/Position CRUD.
4. Attendance và mandatory check-in.
5. Tasks, profile và Requests/PYC.
6. HR personnel processes và Admin account management.
7. Dashboard, statistics và Excel export.
8. UI polish.

Chi tiết acceptance criteria và test mapping:

- [Architecture](docs/ARCHITECTURE.md)
- [Database design](docs/DATABASE.md)
- [Authorization](docs/AUTHORIZATION.md)
- [Business flows](docs/BUSINESS-FLOWS.md)
- [Development plan](docs/DEVELOPMENT-PLAN.md)
- [Acceptance criteria](docs/ACCEPTANCE-CRITERIA.md)
- [Coding conventions](docs/CODING-CONVENTIONS.md)
- [Testing strategy](docs/TESTING.md)

## Handoff notes

- Branch hiện tại: `main`.
- Chưa cấu hình remote Git.
- `.env` không nằm trong repository.
- Database migrations, Eloquent models, enums, relationships và seeders đã được triển khai.
- Các quyết định database của Phase 2 đã được ghi nhận trong [docs/DATABASE.md](docs/DATABASE.md).
- Authentication, role authorization và nền tảng mandatory check-in đã được triển khai.
- HR workflows, CRUD, dashboard, UI polish và các phase tiếp theo chưa được triển khai.
