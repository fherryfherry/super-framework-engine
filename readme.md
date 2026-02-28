# Super Framework Engine

Core engine untuk PHP [SuperFramework](https://github.com/crudbooster/superframework). Framework ini dirancang untuk kecepatan, kemudahan penggunaan, dan arsitektur yang bersih mengikuti standar modern PHP 8.3+.

## Fitur Utama

- **PSR-12 Compliant**: Codebase yang rapi dan konsisten.
- **PHP 8.3 Ready**: Memanfaatkan fitur terbaru seperti typed properties, return types, dan match expressions.
- **Service Container**: Dependency Injection container yang powerful untuk manajemen objek.
- **Advanced ORM**: Fluent interface dengan dukungan eager loading, caching, dan transaksi.
- **Active Record**: Implementasi model yang intuitif.
- **Blade Templating**: Integrasi dengan engine template Laravel Blade.

## Prasyarat

- PHP >= 8.3
- Extension: `pdo`, `json`, `openssl`, `curl`, `gd`

## Instalasi

```bash
composer require fherryfherry/super-framework-engine
```

## Konfigurasi

Pastikan Anda memiliki file konfigurasi di direktori `configs/`:

**Database (configs/Database.php)**
```php
return [
    'driver' => 'mysql', // mysql, pgsql, sqlite, sqlsrv
    'host' => '127.0.0.1',
    'database' => 'super_db',
    'username' => 'root',
    'password' => '',
];
```

## Penggunaan ORM

### Inisialisasi
Anda dapat menggunakan helper global `db()` untuk memulai query:

```php
use SuperFrameworkEngine\App\UtilORM\ORM;

// Ambil data berdasarkan ID
$user = db('users')->find(1);

// Ambil semua data
$users = db('users')->all();
```

### Query Builder
```php
$users = db('users')
    ->select('name', 'email')
    ->where('status = ?', ['active'])
    ->whereIn('role_id', [1, 2])
    ->orderBy('created_at desc')
    ->limit(10)
    ->get();
```

### Eager Loading
Mencegah masalah N+1 dengan memuat relasi sekaligus:
```php
// Mengasumsikan ada kolom user_id di tabel posts
$posts = db('posts')->with('users')->all();
```

### Caching
Simpan hasil query di cache untuk meningkatkan performa:
```php
// Cache hasil query selama 60 detik
$products = db('products')->remember(60)->all();
```

### Transaksi
Kelola transaksi database secara aman:
```php
use SuperFrameworkEngine\App\UtilORM\ORM;

ORM::beginTransaction();
try {
    db('orders')->insert([...]);
    db('inventory')->update([...]);
    ORM::commit();
} catch (\Exception $e) {
    ORM::rollback();
}
```

### Raw SQL
Gunakan query SQL mentah dengan parameter binding:
```php
$results = db()->raw("SELECT * FROM users WHERE age > ?", [25])->fetchAll();
```

## Model (Active Record)

Definisikan model Anda dengan mewarisi `SuperFrameworkEngine\App\UtilModel\Model`:

```php
namespace App\Models;

use SuperFrameworkEngine\App\UtilModel\Model;

class User extends Model
{
    protected ?string $table = 'users';
    public $id;
    public $name;
    public $email;
}
```

### Operasi Model
```php
// Simpan data baru
$user = new User();
$user->name = "Ferry";
$user->save();

// Update data
$user = User::findById(1);
$user->name = "Updated Name";
$user->save();

// Hapus data
User::delete(1);

// Pagination
$data = User::paginate(15); // Mengembalikan array ['data' => [...], 'total' => 100, 'links' => '...']
```

## Dependency Injection

Gunakan `Container` untuk manajemen dependensi:

```php
use SuperFrameworkEngine\Foundation\Container;

$container = Container::getInstance();

// Binding
$container->singleton(MyService::class, fn() => new MyService());

// Resolving
$service = $container->make(MyService::class);
```

## Helper Global

- `db($table)`: Memulai query ORM.
- `config($key, $default)`: Mengambil nilai konfigurasi.
- `request($name, $default)`: Mengambil data input (GET/POST/FILES).
- `dd(...$args)`: Dump and Die untuk debugging.
- `base_url($path)`: Menghasilkan URL absolut aplikasi.

## Testing

Framework ini menggunakan PHPUnit untuk pengujian:

```bash
./vendor/bin/phpunit
```

## Lisensi

Proyek ini berlisensi MIT.
