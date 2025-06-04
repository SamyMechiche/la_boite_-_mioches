# Symfony Project

This is a Symfony 7.2 based web application project.

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL/MariaDB
- Node.js and npm (for frontend assets)
- Symfony CLI (optional but recommended)

## Installation

1. **Install PHP 8.2 or higher**
   - For Windows: Download and install from [PHP official website](https://windows.php.net/download/)
   - For Linux: `sudo apt install php8.2 php8.2-cli php8.2-common php8.2-curl php8.2-mbstring php8.2-mysql php8.2-xml php8.2-zip`

2. **Install Composer**
   - Download and install from [Composer official website](https://getcomposer.org/download/)

3. **Install Symfony CLI** (optional but recommended)
   ```bash
   # For Windows (using scoop)
   scoop install symfony-cli/tap/symfony-cli

   # For Linux/macOS
   curl -sS https://get.symfony.com/cli/installer | bash
   ```

4. **Clone the repository**
   ```bash
   git clone [https://github.com/SamyMechiche/la_boite_-_mioches]
   cd [ECF-4_SYMFONY]
   ```

5. **Install dependencies**
   ```bash
   composer install
   ```

6. **Configure your database**
   - Create a `.env.local` file in the root directory
   - Add your database configuration:
     ```
     DATABASE_URL="mysql://user:password@127.0.0.1:3306/db_name?serverVersion=8.0"
     ```

7. **Create the database and run migrations**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

8. **Install frontend dependencies and build assets**
   ```bash
   npm install
   npm run build
   ```

9. **Start the development server**
   ```bash
   # Using Symfony CLI
   symfony server:start

   # Or using PHP's built-in server
   php -S localhost:8000 -t public
   ```

## Development

- The application will be available at `http://localhost:8000`
- Symfony Profiler is available in dev environment at `http://localhost:8000/_profiler`

## Project Structure

- `src/` - Contains all PHP classes
- `templates/` - Contains all Twig templates
- `public/` - Contains the web root directory
- `config/` - Contains all configuration files
- `migrations/` - Contains database migrations
- `assets/` - Contains frontend assets (JavaScript, CSS, etc.)
- `tests/` - Contains all tests

## Available Commands

```bash
# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Clear cache
php bin/console cache:clear

# Run tests
php bin/phpunit
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is proprietary software. All rights reserved.
