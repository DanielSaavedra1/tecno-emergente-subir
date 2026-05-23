# Tecno Emergente

Plataforma educativa para resolver ejercicios de programacion en un workspace interactivo. Incluye editor de codigo, ejecucion automatica con Judge0, chat con un modelo local mediante LM Studio y clasificacion de intenciones con Dialogflow ES.

## Requisitos

- PHP 8.3 o superior
- Composer
- Node.js 22 o superior
- pnpm 10.21.0
- PostgreSQL
- LM Studio
- Proyecto de Google Cloud con Dialogflow ES

## Servicios Necesarios

### PostgreSQL

La aplicacion usa PostgreSQL como base de datos principal.

Base sugerida:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tecno_emergente
DB_USERNAME=postgres
DB_PASSWORD=
```

### LM Studio

LM Studio debe estar abierto con el servidor local activo.

Valores sugeridos:

```env
LM_STUDIO_BASE_URL=http://localhost:1234/v1
LM_STUDIO_MODEL=google/gemma-4-e2b
LM_STUDIO_API_KEY=
LM_STUDIO_TIMEOUT=120
LM_STUDIO_CONNECT_TIMEOUT=10
LM_STUDIO_RETRIES=2
```

### Judge0

El proyecto usa Judge0 para ejecutar el codigo del estudiante. Esta rama deja configurado el endpoint publico de Judge0.

```env
JUDGE0_BASE_URL=https://ce.judge0.com
JUDGE0_TIMEOUT=30
JUDGE0_CONNECT_TIMEOUT=10
JUDGE0_RETRIES=1
```

### Dialogflow

Dialogflow detecta la intencion del mensaje del usuario. No genera la respuesta final; eso lo hace el modelo local por medio de LM Studio.

```env
DIALOGFLOW_PROJECT_ID=
DIALOGFLOW_LANGUAGE_CODE=es
DIALOGFLOW_CONFIDENCE_THRESHOLD=0.55
GOOGLE_APPLICATION_CREDENTIALS=/ruta/absoluta/service-account.json
```

## Instalacion en macOS

1. Instala dependencias base:

```bash
brew install php composer node postgresql@16
corepack enable
corepack prepare pnpm@10.21.0 --activate
```

2. Inicia PostgreSQL:

```bash
brew services start postgresql@16
```

3. Crea la base de datos:

```bash
createdb tecno_emergente
```

4. Instala dependencias del proyecto:

```bash
composer install --no-dev
pnpm install
```

5. Crea el archivo de entorno:

```bash
cp .env.example .env
php artisan key:generate
```

6. Configura `.env` con PostgreSQL, LM Studio, Judge0 y Dialogflow.

7. Ejecuta migraciones y seeders:

```bash
php artisan migrate --seed
```

8. Levanta la aplicacion:

```bash
composer run dev
```

9. Abre la app en:

```txt
http://127.0.0.1:8000
```

## Instalacion en Windows

Se recomienda usar Windows con WSL2 y Ubuntu.

### Opcion Recomendada: WSL2

1. Instala WSL2 con Ubuntu desde PowerShell como administrador:

```powershell
wsl --install
```

2. Dentro de Ubuntu, instala dependencias:

```bash
sudo apt update
sudo apt install php php-cli php-mbstring php-xml php-curl php-pgsql unzip git curl postgresql postgresql-contrib composer
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install nodejs
corepack enable
corepack prepare pnpm@10.21.0 --activate
```

3. Inicia PostgreSQL:

```bash
sudo service postgresql start
```

4. Crea la base de datos:

```bash
sudo -u postgres createdb tecno_emergente
```

5. Instala dependencias del proyecto:

```bash
composer install --no-dev
pnpm install
```

6. Crea `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

7. Configura `.env` con PostgreSQL, LM Studio, Judge0 y Dialogflow.

8. Ejecuta migraciones y seeders:

```bash
php artisan migrate --seed
```

9. Levanta la aplicacion:

```bash
composer run dev
```

10. Abre:

```txt
http://127.0.0.1:8000
```

### Opcion Windows Nativo

Tambien puedes usar Windows nativo instalando manualmente:

- PHP 8.3 o superior agregado al PATH
- Composer
- Node.js 22 o superior
- pnpm 10.21.0
- PostgreSQL
- LM Studio para Windows

Luego ejecuta los mismos comandos de instalacion usando PowerShell:

```powershell
composer install --no-dev
pnpm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

## Usuario de Prueba

El seeder crea un usuario de prueba:

```txt
Email: test@example.com
Password: password
```

## Como Probar

1. Inicia sesion con el usuario de prueba.
2. Entra al workspace.
3. Selecciona un ejercicio desde el sidebar.
4. Escribe codigo en el editor.
5. Ejecuta el codigo.
6. Revisa el resultado de ejecucion.
7. Usa el chat para pedir una pista, revisar codigo, depurar errores o aclarar el enunciado.

## Flujo del Chat

1. React envia el mensaje al backend de Laravel.
2. Laravel envia solo el mensaje del usuario a Dialogflow.
3. Dialogflow devuelve la intencion detectada.
4. Laravel construye el prompt con el contexto del ejercicio y la intencion.
5. LM Studio genera la respuesta final.
6. Laravel guarda la respuesta y la devuelve al frontend.

## Comandos Utiles

```bash
php artisan migrate --seed
php artisan queue:listen --tries=1 --timeout=0
pnpm run dev
pnpm run build
composer run dev
```
