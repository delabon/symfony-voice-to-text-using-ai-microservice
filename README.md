## About

### Key features

- User can upload an audio file
- User can convert the audio file to text

### Tech stack

- PHP 8.3: Programming language
- Symfony 7.1: PHP Framework
- PHPUnit: Unit and Feature Tests
- Docker: Containerization
- Github actions: Continuous Integration
- API: Openai Wisper API

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
#### Connect to the php container

```bash
docker exec -it php-container bash
```
#### Composer

```bash
composer install
```
#### create .env.local inside the app directory

Copy the content of .env file and paste it in .env.local

#### Bowser

Now, open https://voice-to-text.test in your browser