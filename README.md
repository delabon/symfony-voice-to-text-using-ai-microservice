## About

This project is a web-based application that allows users to convert audio files into text. It leverages the power of OpenAI's Whisper ASR API to transcribe the audio content. The application is built using PHP and the Symfony framework, ensuring robustness and scalability. It also includes a comprehensive suite of unit and feature tests, ensuring the reliability of the application. The application is containerized using Docker, making it easy to set up and run in any environment.

### Key features

- **Audio Upload**: Users can upload audio files in various formats such as MP3, MP4, WAV, and more. The application validates the file format for compatibility.
- **Audio to Text Conversion**: The application converts the uploaded audio file into text using OpenAI's Whisper ASR API. It handles various scenarios such as invalid API responses and errors.
- **Error Handling**: The application is designed to handle various error scenarios gracefully. It includes checks for invalid file formats, API errors, and more.
- **Unit and Feature Tests**: The application includes a comprehensive suite of tests, ensuring each component and feature works as expected.
- **Docker Support**: The application is containerized using Docker, making it easy to set up and run in any environment.
- **Continuous Integration**: The project uses GitHub Actions for Continuous Integration, ensuring the codebase's health and reliability.

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