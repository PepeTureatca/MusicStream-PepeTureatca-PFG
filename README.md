# 🎵 Mi Spotify

Plataforma de streaming de música inspirada en Spotify, desarrollada como Trabajo de Fin de Grado. Permite subir, gestionar y reproducir canciones con un reproductor web completo, autenticación de usuarios (incluyendo Google OAuth), panel de administración y pagos con Stripe.

**[Volver arriba](#-mi-spotify)**

---

## 📋 Tabla de contenidos

1. [Características principales](#-características-principales)
2. [Stack tecnológico](#-stack-tecnológico)
3. [Requisitos previos](#-requisitos-previos)
4. [Instalación](#-instalación)
5. [Configuración](#-configuración)
6. [Estructura del proyecto](#-estructura-del-proyecto)
7. [Uso de la aplicación](#-uso-de-la-aplicación)
8. [Troubleshooting](#-troubleshooting)

---

## ✨ Características principales

| Módulo                 | Descripción                                                                                                 |
| ---------------------- | ----------------------------------------------------------------------------------------------------------- |
| **🔐 Autenticación**   | Registro / login clásico y login con Google OAuth 2.0                                                       |
| **👥 Roles**           | Usuario normal y Administrador (panel separado)                                                             |
| **▶️ Reproductor**     | Barra de reproducción persistente con controles de play/pausa, avance, retroceso, shuffle, repeat y volumen |
| **🔍 Búsqueda**        | Búsqueda en tiempo real por título, artista o álbum                                                         |
| **⬆️ Panel Admin**     | Subida de canciones (audio + portada) con extracción automática de metadatos via getID3                     |
| **💎 Premium**         | Suscripción de pago único (€5,99) procesada con Stripe Checkout                                             |
| **👤 Perfil / Cuenta** | Edición de nombre, email y contraseña                                                                       |
| **❤️ Likes**           | Guardar canciones favoritas                                                                                 |
| **🎵 Playlists**       | Crear y gestionar listas de reproducción personalizadas                                                     |

---

## 🛠️ Stack tecnológico

### Backend

- **PHP 8.0+** (arquitectura MVC manual)
- **MySQL 5.7+ / MariaDB 10.4+**
- **Apache** con `mod_rewrite`

### Dependencias PHP (Composer)

- `google/apiclient` — Google OAuth 2.0
- `stripe/stripe-php` — Pagos con Stripe
- `james-heinrich/getid3` — Extracción de metadatos de audio
- `phpmailer/phpmailer` — Envío de correos
- `vlucas/phpdotenv` — Gestión de variables de entorno

### Frontend

- HTML5, CSS3, JavaScript vanilla
- Font Awesome (iconos)
- Google Fonts (tipografía)

---

## 📦 Requisitos previos

Antes de comenzar, asegúrate de tener instalado:

- **PHP 8.0** o superior ([descargar](https://www.php.net/downloads.php))
- **MySQL 5.7+** o **MariaDB 10.4+** ([descargar](https://www.mysql.com/downloads/))
- **Apache** con `mod_rewrite` habilitado (incluido en XAMPP, WAMP, Laragon)
- **Composer** instalado globalmente ([descargar](https://getcomposer.org/download/))
- **Git** (opcional, para clonar el repositorio)

### Servicios externos (gratuitos)

- Cuenta de [Google Cloud Console](https://console.cloud.google.com) (OAuth 2.0)
- Cuenta de [Stripe](https://stripe.com) (modo test)

**Verifica que todo está instalado:**

```bash
php -v
mysql --version
composer --version
```

---

## 🚀 Instalación

### Paso 1: Clonar/descargar el proyecto

Opción A — Si tienes Git:

```bash
git clone <tu-repositorio> mi-spotify
cd mi-spotify
```

Opción B — Descarga manual:
Coloca la carpeta del proyecto en la raíz de tu servidor web:

```
C:\xampp\htdocs\mi-spotify\     (Windows)
/var/www/html/mi-spotify/        (Linux)
/Library/WebServer/Documents/mi-spotify/ (macOS)
```

### Paso 2: Instalar dependencias con Composer

Navega a la carpeta del proyecto y ejecuta:

```bash
cd mi-spotify
composer install
```

Este comando descargará e instalará todas las dependencias definidas en `composer.json`:

- Google API Client
- Stripe PHP SDK
- GetID3
- PHPMailer
- phpdotenv

La carpeta `vendor/` se creará automáticamente.

### Paso 3: Configurar variables de entorno

**El archivo `.env` con todas las variables de entorno necesarias se proporciona en el ZIP de entrega de Aules.**

Copia el archivo `.env` a la raíz del proyecto. Este archivo contiene:

- Credenciales de base de datos (local y producción)
- Claves de Google OAuth 2.0
- Claves de API de Stripe
- Claves administrativas

**⚠️ IMPORTANTE:**

- No compartas el archivo `.env` en repositorios públicos
- Añádelo a `.gitignore`
- Las claves en el `.env` son sensibles y deben mantenerse confidenciales
- En caso de que necesites regenerar las claves, consulta la sección [Configuración](#-configuración)

### Paso 4: Crear la base de datos

#### Opción A: Importar el dump SQL (recomendado)

Desde phpMyAdmin:

1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Click en "Bases de datos" → crear nueva base de datos llamada `mi_spotify_tfg`
3. Selecciona la base de datos → click en "Importar"
4. Sube el archivo `mi_spotify_tfg.sql` (proporcionado con el proyecto)

Desde la terminal:

```bash
mysql -u root mi_spotify_tfg < mi_spotify_tfg.sql
```

#### Opción B: Crear manualmente la base de datos

Si no tienes el dump SQL, ejecuta estas consultas en MySQL:

```sql
CREATE DATABASE mi_spotify_tfg CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE mi_spotify_tfg;

-- Tabla de usuarios
CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255),
  google_id VARCHAR(100),
  role ENUM('user','admin') DEFAULT 'user',
  is_premium TINYINT(1) DEFAULT 0,
  premium_since DATETIME,
  stripe_payment_id VARCHAR(255),
  subscription_status ENUM('free','premium') DEFAULT 'free',
  stripe_customer_id VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de canciones
CREATE TABLE songs (
  id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(150) NOT NULL,
  artist VARCHAR(100),
  album VARCHAR(100),
  duration INT,
  genre VARCHAR(50),
  audio_url VARCHAR(255) NOT NULL,
  cover_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de playlists
CREATE TABLE playlists (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  is_public TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de canciones en playlists
CREATE TABLE playlist_songs (
  id INT NOT NULL AUTO_INCREMENT,
  playlist_id INT NOT NULL,
  song_id INT NOT NULL,
  position INT DEFAULT 0,
  PRIMARY KEY (id),
  FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
  FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de likes
CREATE TABLE likes (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  song_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY user_song (user_id, song_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de historial de reproducción
CREATE TABLE playback_history (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  song_id INT NOT NULL,
  played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de follows (seguir artistas/playlists)
CREATE TABLE follows (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  playlist_id INT,
  artist_name VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de suscripciones
CREATE TABLE subscriptions (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  stripe_subscription_id VARCHAR(100) NOT NULL,
  status ENUM('active','canceled','past_due','trialing') NOT NULL,
  current_period_end TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Paso 5: Crear carpetas necesarias

El proyecto necesita carpetas para subidas de archivos:

```bash
# Desde la raíz del proyecto
mkdir -p uploads/music
mkdir -p uploads/artCover
```

Asegúrate de que Apache puede escribir en estas carpetas (permisos 755 o 777):

```bash
# Linux/macOS
chmod 755 uploads/music
chmod 755 uploads/artCover

# Windows (desde PowerShell como administrador)
icacls "uploads\music" /grant:r "IIS_IUSRS:(OI)(CI)F"
icacls "uploads\artCover" /grant:r "IIS_IUSRS:(OI)(CI)F"
```

---

## ⚙️ Configuración

### Google OAuth 2.0

Las claves de Google OAuth 2.0 ya están configuradas en el archivo `.env` proporcionado en el ZIP de Aules.

**Si necesitas regenerar las claves** (por ejemplo, en producción):

1. Ve a [Google Cloud Console](https://console.cloud.google.com)
2. Crea un nuevo proyecto (o selecciona uno existente)
3. Habilita la API de Google+:
   - Panel → Biblioteca → busca "Google+ API" → Habilitar
4. Crea credenciales OAuth 2.0:
   - Credenciales → Crear credenciales → OAuth 2.0 (ID de cliente)
   - Tipo: Aplicación web
   - URIs autorizados:
     - `http://localhost:8080` (desarrollo)
     - `http://tu-dominio.com` (producción)
   - URI de redirección autorizada:
     - `http://localhost:8080/mi-spotify/public/google-callback.php` (desarrollo)
     - `http://tu-dominio.com/mi-spotify/public/google-callback.php` (producción)
5. Copia el Client ID y Client Secret al archivo `.env`:
   ```
   GOOGLE_CLIENT_ID=tu_new_client_id.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=tu_new_client_secret
   GOOGLE_REDIRECT_URI=http://tu-dominio/mi-spotify/public/google-callback.php
   ```

### Stripe

Las claves de API de Stripe ya están configuradas en el archivo `.env` proporcionado en el ZIP de Aules (en modo **test**).

**Si necesitas regenerar las claves** (por ejemplo, en producción):

1. Crea una cuenta en [Stripe](https://stripe.com)
2. Ve a Dashboard → Configuración → Claves API
3. Copia las claves:
   - Secret Key (comenzará con `sk_test_` o `sk_live_`)
   - Publishable Key (comenzará con `pk_test_` o `pk_live_`)
4. Actualiza el archivo `.env`:
   ```
   STRIPE_SECRET_KEY=tu_nueva_secret_key
   STRIPE_PUBLISHABLE_KEY=tu_nueva_publishable_key
   ```

---

## 🏃 Iniciar la aplicación

### Opción 1: Usar XAMPP / WAMP

1. Inicia Apache y MySQL desde el panel de control
2. Accede a: `http://localhost/mi-spotify/public/index.php`

### Opción 2: Servidor PHP integrado

```bash
cd mi-spotify/public
php -S localhost:8000
```

Accede a: `http://localhost:8000`

### Opción 3: Usar Laragon

1. Coloca el proyecto en `C:\laragon\www\mi-spotify`
2. Laragon automáticamente creará: `http://mi-spotify.test`

---

## 📁 Estructura del proyecto

```
mi-spotify/
├── app/                          # Lógica de aplicación
│   ├── controller/               # Controladores (MVC)
│   │   ├── AuthController.php
│   │   ├── PaymentController.php
│   │   ├── PlaylistController.php
│   │   ├── SongController.php
│   │   └── UserController.php
│   ├── model/                    # Modelos (acceso a BD)
│   │   ├── db.php                # Clase de conexión a BD
│   │   ├── playlistModel.php
│   │   ├── songModel.php
│   │   └── userModel.php
│   ├── services/                 # Servicios (lógica compartida)
│   │   └── GoogleClient.php
│   └── views/                    # Vistas HTML
│       ├── dashboardView.php
│       ├── loginView.php
│       ├── playlistView.php
│       └── ...
├── public/                       # Carpeta pública (punto de entrada)
│   ├── index.php                 # Punto de entrada principal
│   ├── google-callback.php       # Callback de Google OAuth
│   ├── checkout.php              # Página de pago
│   ├── assets/                   # CSS, JS, imágenes
│   │   ├── css/
│   │   └── js/
│   └── ...
├── uploads/                      # Archivos subidos
│   ├── music/                    # Archivos de audio
│   └── artCover/                 # Portadas de canciones
├── vendor/                       # Dependencias de Composer
├── bootstrap.php                 # Carga de variables de entorno
├── composer.json                 # Dependencias del proyecto
└── README.md                     # Este archivo
```

---

## 🎯 Uso de la aplicación

### Registro e inicio de sesión

#### Usuarios normales

1. Haz click en **"Registrarse"**
2. Completa: nombre, email y contraseña
3. O usa **"Iniciar sesión con Google"** para autenticación OAuth

#### Administradores

1. Solicita al administrador del proyecto la clave de registro
2. La clave se encuentra en la variable `ADMIN_REGISTER_KEY` del archivo `.env` proporcionado en el ZIP de Aules
3. Usa esa clave durante el registro

### Panel de administración

Accede a: `http://localhost/mi-spotify/public/dashboardAdmin.php`

Funciones:

- ⬆️ **Subir canciones:** drag-drop o formulario
- 📝 **Editar/eliminar canciones**
- 👥 **Gestionar usuarios**
- 📊 **Ver estadísticas**

### Reproductor

- **Play/Pausa:** Click en el botón o barra espaciadora
- **Avance/Retroceso:** Flechas izquierda/derecha
- **Shuffle:** Aleatoria
- **Repeat:** Repetir (una canción o lista)
- **Volumen:** Control deslizante
- **Búsqueda:** Busca por título, artista o álbum

### Playlists

1. Crea una nueva playlist desde el dashboard
2. Añade canciones haciendo click en "Añadir a playlist"
3. Ordena las canciones (drag-drop)
4. Comparte con otros usuarios (si es pública)

### Premium

1. Haz click en **"Actualizar a Premium"**
2. Completa el pago con Stripe (modo test)
3. Disfruta de características premium 🎉

---

## 🔧 Troubleshooting

### Error: "Class not found" o "Cannot find module"

**Solución:** Ejecuta `composer install` nuevamente:

```bash
composer install --no-cache
composer dump-autoload
```

### Error: "SQLSTATE[HY000]: General error: 2006"

**Solución:** La conexión a MySQL se ha perdido. Verifica:

- MySQL está corriendo
- Credenciales en `.env` son correctas
- `DB_HOST`, `DB_PORT` son accesibles

```bash
# Verifica la conexión
mysql -u root -h 127.0.0.1 -P 3306 mi_spotify_tfg
```

### Las canciones no se suben

**Solución:** Comprueba los permisos de las carpetas:

```bash
# Permisos de lectura/escritura
chmod 777 uploads/music
chmod 777 uploads/artCover
```

### Google OAuth no funciona

**Problemas comunes:**

1. `GOOGLE_REDIRECT_URI` no coincide con la configurada en Google Cloud Console
2. Cliente ID/Secret incorrecto o expirado
3. URI autorizados no incluyen `http://localhost:8080`

**Solución:**

- Recrea las credenciales OAuth en Google Cloud Console
- Verifica que las URIs exactamente coinciden (con puerto incluido)

### Stripe rechaza el pago

**En modo test:** Usa estas tarjetas de prueba:

- **Aprobada:** `4242 4242 4242 4242`
- **Rechazada:** `4000 0000 0000 0002`
- Fecha futura, cualquier CVC

---

## 📚 Recursos útiles

- [Documentación de PHP](https://www.php.net/manual/es/)
- [MySQL Referencia](https://dev.mysql.com/doc/)
- [Google API Client PHP](https://github.com/googleapis/google-api-php-client)
- [Stripe PHP SDK](https://github.com/stripe/stripe-php)
- [GetID3 Documentation](https://www.getid3.org/)

---

## 📝 Licencia

Este proyecto es de código abierto bajo licencia MIT.

---

## 👨‍💻 Autor

Desarrollado como Trabajo de Fin de Grado.

**¿Preguntas o problemas?** Abre un issue o contacta al administrador del proyecto.

---

**Última actualización:** Mayo 2026
`album` VARCHAR(100) DEFAULT NULL,
`duration` INT DEFAULT NULL,
`genre` VARCHAR(50) DEFAULT NULL,
`audio_url` VARCHAR(255) NOT NULL,
`cover_url` VARCHAR(255) DEFAULT NULL,
`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `playlists` (
`id` INT NOT NULL AUTO_INCREMENT,
`user_id` INT NOT NULL,
`name` VARCHAR(100) NOT NULL,
`description` TEXT DEFAULT NULL,
`is_public` TINYINT(1) DEFAULT 1,
`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `playlist_songs` (
`id` INT NOT NULL AUTO_INCREMENT,
`playlist_id` INT NOT NULL,
`song_id` INT NOT NULL,
`position` INT DEFAULT 0,
PRIMARY KEY (`id`),
FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `likes` (
`id` INT NOT NULL AUTO_INCREMENT,
`user_id` INT NOT NULL,
`song_id` INT NOT NULL,
`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
UNIQUE KEY `user_song` (`user_id`,`song_id`),
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `playback_history` (
`id` INT NOT NULL AUTO_INCREMENT,
`user_id` INT NOT NULL,
`song_id` INT NOT NULL,
`played_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `follows` (
`id` INT NOT NULL AUTO_INCREMENT,
`user_id` INT NOT NULL,
`playlist_id` INT DEFAULT NULL,
`artist_name` VARCHAR(100) DEFAULT NULL,
`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `subscriptions` (
`id` INT NOT NULL AUTO_INCREMENT,
`user_id` INT NOT NULL,
`stripe_subscription_id` VARCHAR(100) NOT NULL,
`status` ENUM('active','canceled','past_due','trialing') NOT NULL,
`current_period_end` TIMESTAMP DEFAULT NULL,
`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

```

```
