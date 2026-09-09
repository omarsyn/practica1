# RESTful Tareas - Enfoque API-First

API RESTful desarrollada bajo la metodología **API-First** para la gestión del recurso **Tareas**, utilizando una especificación formal en **OpenAPI 3.0**, interfaz **Swagger UI**, arquitectura MVC en **PHP nativo** y **MySQL** sobre un servidor VPS.

---

## Requisitos Previos

Antes de comenzar, asegúrate de contar con los siguientes componentes en tu servidor o entorno local:

- **PHP**: Versión 8.0 o superior (con extensión `pdo_mysql` habilitada).
- **Servidor Web**: Apache o Nginx con módulo `mod_rewrite` activado.
- **Base de Datos**: MySQL / MariaDB.
- **Git**: Para el control de versiones.
- **Cliente HTTP**: Postman o cURL para pruebas de API.

---

## ⚙️Instrucciones para Levantamiento del Proyecto

Sigue estos pasos detallados para desplegar e instalar el proyecto localmente o en tu servidor:

### 1. Clonar el Repositorio
Abre tu terminal y clona el proyecto desde GitHub:
```bash
git clone [https://github.com/tu_usuario/tu_repositorio.git](https://github.com/tu_usuario/tu_repositorio.git)
cd tu_repositorio

2. Configurar la Base de Datos
Accede a tu gestor de base de datos MySQL (phpMyAdmin o línea de comandos) e ingresa a la base de datos bd_22031401.

Ejecuta la siguiente sentencia SQL para crear la tabla tareas:

SQL
CREATE TABLE IF NOT EXISTS `tareas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(255) NOT NULL,
  `completada` TINYINT(1) DEFAULT 0,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
3. Configurar la Conexión en PHP
Asegúrate de que el archivo config/database.php contenga las credenciales correctas de tu servidor MySQL:

PHP
<?php
class Database {
    private $host = "localhost";
    private $db_name = "bd_22031401";
    private $username = "tu_usuario_bd";
    private $password = "tu_contraseña_bd";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name,$this->username, $this->password);$this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
4. Verificar Permisos y Redirección
Si utilizas Apache, verifica que el archivo .htaccess esté configurado en la carpeta public/ para redirigir las peticiones al enrutador index.php:

Apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]


 http://topicosweb.celaya.tecnm.mx/22031401/public/docs/

2. Pruebas de Endpoints en Postman
A. Creación de una Tarea (POST /tareas)
Endpoint: POST /22031401/public/api/v2/tareas

Headers: Content-Type: application/json

Body (raw JSON):

JSON
{
  "titulo": "Implementar Swagger UI y Postman",
  "completada": false
}
Código de Estado HTTP: 201 Created

B. Consulta y Listado de Tareas (GET /tareas)
Endpoint: GET /22031401/public/api/v2/tareas

Código de Estado HTTP: 200 OK

Respuesta esperada:

JSON
[
  {
    "id": 1,
    "titulo": "Implementar Swagger UI y Postman",
    "completada": false,
    "fecha_creacion": "2026-09-08 21:00:00"
  }
]
