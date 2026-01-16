# Sistema de Logs - Projecte Cantina

## Introducció

Aquest projecte ara inclou un sistema complet de logging per registrar errors i accessos. Els logs es guarden en el directori `/logs/` i es generen automàticament.

## Estructura de Logs

### Fitxers de log:
- **`error.log`**: Registra errors, warnings i informació general
- **`access.log`**: Registra tots els accessos i operacions dels usuaris

## Ubicació dels Logs

- **Desenvolupament local**: `./logs/`
- **Docker**: `/var/www/html/logs/`

## Tipus de Logs

### 1. Logs d'Error (`error.log`)
Format:
```
[TIMESTAMP] [LEVEL] [IP: xxx.xxx.xxx.xxx] [User: username] - MESSAGE | Context: {...} | User-Agent: ...
```

Nivells:
- `ERROR`: Errors crítics
- `WARNING`: Avisos importants
- `INFO`: Informació general

### 2. Logs d'Accés (`access.log`)
Format:
```
[TIMESTAMP] [STATUS] [IP: xxx.xxx.xxx.xxx] [User: username] [Method: GET/POST] [URI: /path] - ACTION | Details: ...
```

Estats:
- `SUCCESS`: Operació exitosa
- `FAILURE`: Operació fallida

## Ús de la Classe Logger

### Importar la classe:
```php
require_once 'Logger.php';
```

### Mètodes disponibles:

#### 1. Registrar errors
```php
Logger::error('Missatge d\'error', ['context' => 'dades adicionals']);
```

#### 2. Registrar warnings
```php
Logger::warning('Missatge d\'avís', ['user' => 'nom_usuari']);
```

#### 3. Registrar informació
```php
Logger::info('Operació completada', ['details' => 'info']);
```

#### 4. Registrar accessos
```php
Logger::access('Descripció de l\'acció', $success = true, 'Detalls adicionals');
```

#### 5. Registrar intents de login
```php
Logger::loginAttempt($username, $success, $isAdmin = false);
```

#### 6. Registrar logout
```php
Logger::logout($username);
```

#### 7. Registrar operacions de productes
```php
Logger::productOperation('Added', $productData);
Logger::productOperation('Updated', $productData);
Logger::productOperation('Deleted', $productData);
```

#### 8. Registrar excepcions
```php
try {
    // codi...
} catch (Exception $e) {
    Logger::exception($e);
}
```

#### 9. Netejar logs antics
```php
Logger::cleanOldLogs($days = 30);
```

## Gestió de Logs (viewLogs.php)

El fitxer `viewLogs.php` proporciona una API per gestionar els logs (només per administradors):

### Endpoints:

#### Llistar logs
```
GET /php/viewLogs.php?action=list
```

#### Llegir un log
```
GET /php/viewLogs.php?action=read&file=error.log&lines=100
```

#### Descarregar un log
```
GET /php/viewLogs.php?action=download&file=error.log
```

#### Netejar un log (crea backup)
```
GET /php/viewLogs.php?action=clear&file=error.log
```

#### Eliminar logs antics
```
GET /php/viewLogs.php?action=clean-old&days=30
```

## Integració Actual

Els logs ja estan integrats en:

1. **`login.php`**: 
   - Login exitós/fallit
   - Intents amb usuari inexistent
   - Errors de contrasenya

2. **`logout.php`**:
   - Registre de logout d'usuaris

3. **`adminProducts.php`**:
   - Llistat de productes
   - Addició de productes
   - Actualització de productes
   - Eliminació de productes
   - Errors en operacions

4. **`enviaProductes.php`**:
   - Accés a productes públics
   - Errors en càrrega de productes

## Exemples d'Ús

### Afegir logging a un nou fitxer PHP:

```php
<?php
require_once 'Logger.php';
session_start();

try {
    // La teva operació
    $result = some_operation();
    
    if ($result) {
        Logger::access('Operació exitosa', true);
        Logger::info('Detalls de l\'operació', ['data' => $result]);
    } else {
        Logger::access('Operació fallida', false);
        Logger::warning('L\'operació no s\'ha pogut completar');
    }
    
} catch (Exception $e) {
    Logger::exception($e);
    Logger::error('Error crític en l\'operació');
}
?>
```

## Configuració de Docker

El Dockerfile ja està configurat per:
- Crear el directori `/var/www/html/logs/`
- Assignar permisos d'escriptura (777)
- Propietari: www-data

## Seguretat

- Els logs **NO** s'inclouen al control de versions (`.gitignore`)
- Accés a `viewLogs.php` només per administradors
- Backups automàtics abans de netejar logs
- Validació de noms de fitxer per prevenir directory traversal

## Manteniment

### Neteja automàtica:
Es pot configurar un cron job per netejar logs antics:
```php
// Executar setmanalment o mensualment
Logger::cleanOldLogs(30); // Elimina logs amb més de 30 dies
```

### Rotació de logs:
Es recomana implementar rotació de logs si els fitxers creixen molt:
- Crear fitxers amb dates: `error-2026-01-09.log`
- Comprimir logs antics
- Arxivar logs històrics

## Monitoring

Per monitoritzar els logs en temps real:

```bash
# Dins del contenidor Docker
tail -f /var/www/html/logs/error.log
tail -f /var/www/html/logs/access.log

# Filtrar errors específics
grep "ERROR" /var/www/html/logs/error.log | tail -n 50
grep "FAILURE" /var/www/html/logs/access.log | tail -n 50
```

## Millores Futures

Possibles millores:
1. Dashboard web per visualitzar logs
2. Alertes per email en errors crítics
3. Integració amb serveis externs (Sentry, Loggly)
4. Estadístiques d'ús
5. Rotació automàtica de logs
6. Compressió de logs antics
