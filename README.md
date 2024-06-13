This web-based application converts audio files into text using OpenAI's Whisper ASR API. Built with PHP and the Symfony framework, it offers robust and scalable performance. Key features include:

- **Audio Upload**: Users can upload audio files in formats like MP3, MP4, and WAV. The application checks if the file format is compatible.
- **Audio to Text Conversion**: Converts uploaded audio files into text with Whisper ASR API, handling various scenarios including errors.
- **Error Handling**: Manages errors gracefully, checking for issues like invalid file formats and API errors.
- **Testing**: Includes thorough unit and feature tests to ensure reliability.
- **Docker Support**: Containerized with Docker for easy setup and deployment.
- **Continuous Integration**: Uses GitHub Actions to maintain code quality and reliability.

This makes the application easy to set up, reliable, and capable of running in any environment.
### Tech stack

- PHP 8.3: Programming language
- Symfony 7.1: PHP Framework
- PHPUnit: Automation Testing
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
