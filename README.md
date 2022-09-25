<a name="readme-top"></a>

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/e257142aab944ec3b9eb44d2129c20ad)](https://www.codacy.com/gh/Kimealabs/BileMoP7/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Kimealabs/BileMoP7&amp;utm_campaign=Badge_Grade)
<img src="https://img.shields.io/badge/PHP 8.1-black?style=flat-square&logo=Php" />
<img src="https://img.shields.io/badge/LICENCE-MIT-blue" />

<br />
<div align="center">
    <h2 align="center">API Bilemo</h2>

  <p align="center">
    An Open Class Rooms Project
    <br />
    <a href="https://github.com/Kimealabs/BileMoP7/"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/Kimealabs/BileMoP7/issues">Report Bug</a>
    ·
    <a href="https://github.com/Kimealabs/BileMoP7/issues">Request Feature</a>
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#my-development-environment">My development environment</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project

Here is the API Bilemo project of my OpenClassRooms (P7) formation "PHP Symfony Dev".

Purpose: Create with the Symfony framework an API for Bilemo about mobiles and clients/users database:

- Authenficate Client by Email/Password with JWT.


- List all products.
- Show product details.
- List all users of client.
- Show user details of Client.
- Create a new user by a Client.
- Delete user of users Client list.
- Only Authenficated Client can be do these actions




<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- DEV ENV -->
## My development environment 
### Here the list of frameworks, programs and libraries

<img src="https://img.shields.io/badge/Symfony 6.1.4-black?style=for-the-badge&logo=Symfony" />  <img src="https://img.shields.io/badge/Symfony CLI 5.4.11-black?style=for-the-badge&logo=Symfony" />

<img src="https://img.shields.io/badge/Composer 2.3.10-280?style=for-the-badge&logo=Composer" /> <img src="https://img.shields.io/badge/Twig 3.4.2-green?style=for-the-badge" />

<img src="https://img.shields.io/badge/PHP 8.1-eef?style=for-the-badge&logo=PHP" /> <img src="https://img.shields.io/badge/Apache 2.4.54-fa0303?style=for-the-badge&logo=Apache" /> <img src="https://img.shields.io/badge/PhpMyAdmin 5.2.0-f2cb61?style=for-the-badge&logo=phpMyAdmin" />


<img src="https://img.shields.io/badge/VSCode 1.71.0-0055aa?style=for-the-badge&logo=Visual Studio Code" />

<img src="https://img.shields.io/badge/Docker 4.11.1-eee?style=for-the-badge&logo=Docker" />  <img src="https://img.shields.io/badge/WSL2 with Ubuntu 20.04 LTS-eee?style=for-the-badge&logo=Ubuntu" />

<img src="https://img.shields.io/badge/NelmioApiDocBundle-v4.0-%230088dd" />
<img src="https://img.shields.io/badge/LexikJWTAuthentificationBundle-v2.16-%23dd8800" />

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- GETTING STARTED -->
## Getting Started

How to install and configure snowtricks

### Prerequisites

- PHP version 8.0.2 or higher
- AMP (MAMP, WAMP, ...) environment if local use (create Database) OR install Docker (docker-compose up with docker-compose.yml) and employ php bin/console serve:start
- You can install Symfony CLI to facilitate commands

### Installation

Below is an example of how you can install on local with Docker and Symfony CLI.

1. Clone the repo into your directory (got clone)
2. Make a composer install / composer update
3. Create SSL public and private .pem into config/jwt :

  ```
      openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096&nbsp;
      
      openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```


4. Edit .env.local :

```
      ###> lexik/jwt-authentication-bundle ###
      
      JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
  
      JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
  
      JWT_PASSPHRASE='YourSentenceOfSSLCertificatesCreation'
  
      ###< lexik/jwt-authentication-bundle ###
  ```

5. Run docker-compose up
6. symfony server:start -d
7. symfony console doctrine:migrations:migrate
8. symfony console doctrine:fixtures:load (add Clients, users and mobiles into database)

Now you can Open 127.0.0.1:8000/api/doc for Documentation API Website
* :8081 for PhpMyAdmin
      
      
## ENJOY :-)

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

This is a list of resources you find helpful and i would like to give credit to !

* [Benoit - nouvelle-techno.fr](https://nouvelle-techno.fr/)
* [Img Shields](https://shields.io)
* [UML tools](https://app.diagrams.net/)
* [OCR cours Contruisez une API REST avec Symfony ](https://openclassrooms.com/fr/courses/7709361-construisez-une-api-rest-avec-symfony)


<p align="right">(<a href="#readme-top">back to top</a>)</p>
