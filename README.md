## Requisitos

* PHP 8.2 ou superior
* MySQL 8 ou superior
* Composer

## Como rodar o projeto baixado

Duplicar o arquivo ".env.example" e renomear para ".env".<br>
Alterar no arquivo .env as credenciais do banco de dados<br>

Instalar as dependências do PHP
```
composer install
```

Gerar a chave no arquivo .env
```
php artisan key:generate
```

php artisan install:api

Executar as migration
```
php artisan migrate
```

Executar as seed
```
php artisan db:seed
```

Iniciar o projeto criado com Laravel
```
php artisan serve
```

Para acessar a API, é recomendado utilizar o Insomnia para simular requisições à API.
```
http://127.0.0.1:8000/api/users
```


## Sequencia para criar o projeto
Como criar o projeto com Laravel
```
composer create-project laravel/laravel .
```

Alterar no arquivo .env as credenciais do banco de dados<br>

Como criar o arquivo de rotas para API no Laravel 11
```
php artisan install:api
```

Como iniciar o projeto criado com Laravel
```
php artisan serve
```

Para acessar a API, é recomendado utilizar o Insomnia para simular requisições à API.
```
http://127.0.0.1:8000/api/users
```

Como criar seed com artisan no Laravel 11
```
php artisan make:seeder NomeDaSeeder
php artisan make:seeder UserSeeder
```

Como executar as seed com artisan no Laravel 11
```
php artisan db:seed
```

Como criar a Controller com artisan no Laravel 11
```
php artisan make:model NomeDaController
php artisan make:controller UserController
```

Como criar o Request com artisan no Laravel 11
```
php artisan make:request NomeDoRequest
php artisan make:request UserRequest
```
