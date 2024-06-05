## About

### Key features

- Add here

### Tech stack

- PHP 8.3
- Symfony 7 (Doctrine & Twig)
- MySQL 8.3
- Docker

### To test this on your local machine, follow the instructions bellow

#### Add domain to /etc/hosts (host)

```bash
sudo nano /etc/hosts
127.0.0.111  voice-to-text.test
```

#### Install mkcert (host)

```bash
sudo apt install libnss3-tools
curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64"
chmod +x mkcert-v*-linux-amd64
sudo mv mkcert-v*-linux-amd64 /usr/local/bin/mkcert
cd ssls/
mkcert -install voice-to-text.test
```

#### Up containers (host)

```bash
docker-compose up --build -d
```

#### create .env.local inside the app directory

Copy the content of .env file and paste it in .env.local and then, Add the following

```dotenv
DATABASE_URL="mysql://root:root@mysql-service:3306/my_store?serverVersion=8.3.0&charset=utf8mb4"
MAILER_DSN=smtp://mailpit:1025
```

#### Connect to the php container

```bash
docker exec -it php-container bash
```

#### Composer

```bash
composer install
```

#### Migrate database

```bash
php bin/console doctrine:migrations:migrate
```

#### Load fixtures

```bash
php bin/console doctrine:fixtures:load -n
```

#### Install node modules

Open a new terminal and run the following command

```bash
docker-compose run node-service npm install
docker-compose run node-service npm run build
```

#### Bowser

Now, open https://voice-to-text.test in your browser

#### Mailpit

To see the emails sent by the app, open http://localhost:8025/ in your browser

#### PHPMyAdmin

To see the database, open http://localhost:8080/ in your browser
