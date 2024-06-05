## About

### Key features

- Add here

### Tech stack

- PHP 8.3
- Symfony 7.1
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

#### Bowser

Now, open https://voice-to-text.test in your browser