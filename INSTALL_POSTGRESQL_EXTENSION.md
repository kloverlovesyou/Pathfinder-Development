# Installing PostgreSQL Extension for PHP (Windows)

## Step 1: Download PostgreSQL DLL Files

You need to download the PostgreSQL extension DLLs for PHP 8.4:

1. Go to: https://windows.php.net/downloads/pecl/releases/pgsql/
2. Download the latest version compatible with PHP 8.4
   - Look for files: `php_pdo_pgsql.dll` and `php_pgsql.dll`
3. OR use PECL (if available):
   ```bash
   pecl install pdo_pgsql
   ```

**Alternative**: If the above doesn't work, you can:
- Download from: https://pecl.php.net/package/pdo_pgsql
- Extract the DLL files to your PHP `ext` directory (usually `C:\php-8.4.10\ext\`)

## Step 2: Enable Extensions in php.ini

1. Open your `php.ini` file (located at: `C:\php-8.4.10\php.ini`)

2. Find the section with extensions (look for lines starting with `;extension=`)

3. Add these lines (remove the semicolon `;` to enable):
   ```ini
   extension=pgsql
   extension=pdo_pgsql
   ```

4. Make sure the `extension_dir` is set correctly:
   ```ini
   extension_dir = "ext"
   ```
   (This should already be set based on your configuration)

5. Save the file

## Step 3: Verify Installation

Restart your terminal/command prompt and run:
```bash
php -m | grep pgsql
```

You should see:
- `pdo_pgsql`
- `pgsql`

## Step 4: Test Database Connection

```bash
cd backend
php artisan tinker
```

Then run:
```php
DB::connection()->getPdo();
```

## Alternative: Using XAMPP/WAMP

If you're using XAMPP or WAMP:
1. The extensions might already be available but commented out
2. Just uncomment the lines in php.ini:
   ```ini
   extension=pgsql
   extension=pdo_pgsql
   ```

## Troubleshooting

### "Unable to load dynamic library"
- Make sure the DLL files are in the `ext` directory
- Check that the DLL version matches your PHP version (8.4)
- Verify the file names are exactly: `php_pgsql.dll` and `php_pdo_pgsql.dll`

### "The specified module could not be found"
- PostgreSQL client libraries might be missing
- Download PostgreSQL client libraries from: https://www.postgresql.org/download/windows/
- Or install the full PostgreSQL server (you only need the client libraries)

### Still not working?
- Check PHP error log for more details
- Verify your PHP is thread-safe (TS) or non-thread-safe (NTS) and download matching DLLs
- Try restarting your web server if using one

