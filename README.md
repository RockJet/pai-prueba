#Proyecto prueba para gestionar biblioteca.

Desarrollar una API REST básica para un sistema de biblioteca que permita gestionar libros y préstamos, ADEMÁS de
una funcionalidad de suscripciones para miembros premium.

#Prerrequisitos

Para poder instalar y ejecutar este proyecto en tu entorno local, necesitarás:

    PHP >= 8.2
    Composer
    Node.js >= 22
    Un servidor de base de datos (ej. MySQL, PostgreSQL) o SQLite.


Pasos de Instalación

Sigue estos pasos para poner en marcha el proyecto:

1. Clonar el repositorio:
    ```bash

	git clone https://github.com/RockJet/pai-prueba.git
	cd tu-repositorio

  
    ```
2. Instalar dependencias con los siguientes comandos:
    ```bash

    
	composer install
	npm install


    ```
3. Crear el archivo de entorno:
	
Copia el archivo de ejemplo .env.example para crear tu propio archivo de configuración.

  

4. Generar la clave de la aplicación con el siguiente comando:
    ```bash
    
	php artisan key:generate

    ```
5. Configurar el archivo .env:
        ```bash

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booklend
DB_USERNAME=root
DB_PASSWORD=
  
    ```
6. Ejecutar las migraciones y seeders con el siguiente comando:
    ```bash
    
	php artisan migrate --seed

  
    ```
7. Compilar los assets del frontend:
    ```bash
        
    	npm run dev

      
    ```
8. Ejecución de la Aplicación
        ```bash

	php artisan serve

    ```
      

Ahora puedes acceder a la aplicación en http://127.0.0.1:8000.
Ejecución de los Tests

9. Para asegurarte de que toda la funcionalidad de la API funciona como se espera, puedes ejecutar el conjunto de pruebas de PHPUnit con el siguiente comando:
    ```bash
    
	./vendor/bin/phpunit

      ```
