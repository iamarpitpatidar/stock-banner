# Stock Banner

### Requirements
- Alpaca API Keys

### Installation
```shell
git clone https://github.com/iamarpitpatidar/stock-banner.git
cd stock-banner

# Install Deps
composer install

# Create .env file and update API Keys (from alpaca)
cp .env.example .env

# Migrate the database
php artisan migrate --seed

# Run the app
php artisan serve
```

### Output
![img.png](img.png)
