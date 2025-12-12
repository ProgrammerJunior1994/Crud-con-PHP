# Sistema ERP - Gestión Integral de Negocio

Un sistema de planificación de recursos empresariales (ERP) desarrollado en **PHP** con **MySQL/MariaDB** y **Bootstrap 5**. Diseñado para gestionar clientes, proveedores, productos, ventas y compras de forma centralizada.

---

##  Tabla de Contenidos

- [Características](#características)
- [Requisitos Previos](#requisitos-previos)
- [Instalación](#instalación)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Configuración](#configuración)
- [Base de Datos](#base-de-datos)
- [Uso](#uso)
- [Credenciales de Prueba](#credenciales-de-prueba)
- [Seguridad](#seguridad)
- [Notas de Desarrollo](#notas-de-desarrollo)
- [Troubleshooting](#troubleshooting)

---

##  Características

- ✅ **Autenticación segura** con sesiones PHP y contraseñas hasheadas (bcrypt)
- ✅ **Control de permisos** - Admin (CRUD completo) vs Secretaria (solo lectura/edición)
- ✅ **Gestión de Clientes** - crear, leer, editar, eliminar (CRUD)
- ✅ **Gestión de Proveedores** - mantener registro con imágenes
- ✅ **Gestión de Productos** - inventario y stock con imágenes
- ✅ **Gestión de Ventas** - registrar, editar con control automático de stock
- ✅ **Gestión de Compras** - órdenes de compra con proveedores
- ✅ **Dashboard Principal** - estadísticas y resumen en tiempo real
- ✅ **Formularios estandarizados** - Interfaz consistente en todos los módulos
- ✅ **Validación robusta** - Cliente y servidor con manejo de errores
- ✅ **Carga de imágenes** - Validación de tipo/tamaño, rutas absolutas
- ✅ **Interfaz moderna** - Bootstrap 5 con diseño responsivo
- ✅ **Base de datos relacional** - MySQL/MariaDB con integridad referencial y transacciones

---

##  Requisitos Previos

- **XAMPP** (Apache 2.4+, PHP 8.2+, MySQL 5.7+/MariaDB 10.4+)
- **Navegador web** moderno (Chrome, Firefox, Edge)
- **Acceso a phpMyAdmin** para gestión de base de datos

---

##  Instalación

### 1. Descargar/Clonar el Proyecto

```bash
# Dentro de c:\xamppa\htdocs\
cd c:\xamppa\htdocs
# Copiar ProyectoWeb aquí (si no está ya)
```

### 2. Crear la Base de Datos

Opción A - **phpMyAdmin** (Recomendado)
1. Abre `http://localhost/phpmyadmin`
2. Crea una nueva base de datos llamada `phpcrud`
3. Selecciona `phpcrud` → Pestaña **Importar**
4. Selecciona el archivo `sql/crudphp1.sql`
5. Haz clic en **Importar**

Opción B - **Terminal (PowerShell)**
```powershell
# Navega a la carpeta mysql de XAMPP
cd C:\xamppa\mysql\bin

# Importa el volcado
.\mysql.exe -u root -p < "C:\xamppa\htdocs\ProyectoWeb\sql\crudphp1.sql"
# Cuando pida contraseña, ingresa: root
```

### 3. Verificar Configuración

Edita `config/config.php` y verifica:
```php
$dbHost = '127.0.0.1';
$dbName = 'phpcrud';
$dbUser = 'root';
$dbPass = 'root';  // Cambia si tu XAMPP tiene otra contraseña
```

### 4. Prueba la Conexión

1. Inicia Apache y MySQL desde **XAMPP Control Panel**
2. Abre en tu navegador: `http://127.0.0.1/ProyectoWeb/auth/test_conn.php`
3. Deberías ver "Conexión OK" y el nombre de la base de datos

---

##  Estructura del Proyecto

```
ProyectoWeb/
├── auth/                           # Autenticación
│   ├── login.php                  # Formulario de login
│   ├── procesar_login.php         # Procesamiento de login
│   ├── logout.php                 # Cierre de sesión
│   ├── test_conn.php              # Test de conexión a BD
│   └── test_password.php          # Test de hashes de contraseña
│
├── config/
│   └── config.php                 # Configuración BD (PDO)
│
├── modules/                        # Módulos CRUD principales
│   ├── clientes/
│   │   ├── readcliente.php        # Listar clientes
│   │   ├── createcliente.php      # Crear cliente
│   │   ├── updatecliente.php      # Editar cliente
│   │   └── deletecliente.php      # Eliminar cliente
│   ├── proveedores/               # Similar a clientes
│   ├── productos/                 # Similar a clientes
│   ├── ventas/                    # Similar a clientes
│   └── compras/                   # Similar a clientes
│
├── views/
│   ├── dashboard.php              # Panel principal (requiere sesión)
│   ├── header.php                 # Encabezado (si es modular)
│   ├── footer.php                 # Pie de página (si es modular)
│   └── ...                        # Otras vistas
│
├── imagen/                         # Almacenamiento de imágenes
│   ├── favicon.ico
│   └── [avatares de usuarios]
│
├── sql/
│   └── crudphp1.sql               # Volcado de base de datos
│
├── index.php                       # Redirige al dashboard
├── README.md                       # Este archivo
└── [otros archivos]

```

---

##  Configuración

### Archivo config/config.php

El archivo de configuración está pre-establecido para conexión PDO a MySQL:

```php
$dbHost = '127.0.0.1';           // Host de la BD
$dbName = 'phpcrud';             // Nombre de la BD
$dbUser = 'root';                // Usuario BD
$dbPass = 'root';                // Contraseña BD
$dbCharset = 'utf8mb4';          // Codificación
```

**Nota**: Si tu XAMPP tiene una contraseña diferente para `root`, actualiza `$dbPass` con la contraseña correcta.

---

##  Estructura de Formularios Estandarizados

### CREATE (Crear/Ingresar Datos)
Todos los formularios de creación tienen:
- ✅ Validación de campos requeridos
- ✅ Reglas de validación específicas (longitud, formato email, etc.)
- ✅ Carga de imágenes con validación (JPEG, PNG, GIF, WebP, máx 5 MB)
- ✅ Mensajes de error alertas dismissibles
- ✅ Bootstrap 5 responsive
- ✅ Headers temáticos por módulo

### READ (Listar)
Todos los listados incluyen:
- ✅ Tablas o tarjetas responsivas
- ✅ Búsqueda y filtrado
- ✅ Botones de acciones (editar, eliminar)
- ✅ Permisos diferenciados (Admin/Secretaria)
- ✅ Paginación donde aplica

### UPDATE (Editar)
Todos los formularios de edición tienen:
- ✅ Verificación de sesión
- ✅ Validación completa de campos
- ✅ Manejo seguro de imágenes
- ✅ Prepared statements contra SQL injection
- ✅ Vista previa de imágenes actuales
- ✅ Repopulación automática en caso de error
- ✅ Card-based layout con header temático
- ✅ Botones consistentes ( Actualizar, ← Volver)

**Colores de Headers por Módulo:**
- Clientes:  Amarillo (bg-warning)
- Productos:  Amarillo (bg-warning)
- Compras:  Azul (bg-primary)
- Proveedores:  Verde (bg-success)
- Ventas:  Azul Claro (bg-info)

### DELETE (Eliminar)
Todos los delete incluyen:
- ✅ Control de permisos (solo Admin puede eliminar)
- ✅ Validación de integridad referencial
- ✅ Eliminación de archivos adjuntos (imágenes)
- ✅ Redirección segura post-eliminación

---

##  Base de Datos

### Tablas Principales

1. **usuarios** - Autenticación
   - `id`, `nombre`, `correo`, `password` (bcrypt), `tipo` (Admin/Secretaria), `imagen`

2. **clientes** - Clientes del negocio
   - `id`, `nombre`, `email`, `telefono`, `direccion`, `imagen`

3. **proveedores** - Proveedores registrados
   - `id`, `nombre`, `email`, `telefono`, `direccion`, `empresa`, `imagen`

4. **productos** - Catálogo de productos
   - `id`, `nombre`, `descripcion`, `precio`, `stock`, `imagen`, `proveedor_id`

5. **ventas** - Registro de ventas
   - `id`, `cliente_id`, `producto_id`, `cantidad`, `fecha_venta`, `total`, `estado`

6. **compras** - Órdenes de compra
   - `id`, `fecha_compra`, `producto_id`, `cantidad`, `precio`

### Relaciones (Claves Foráneas)

- `productos.proveedor_id` → `proveedores.id` (ON DELETE CASCADE)
- `ventas.cliente_id` → `clientes.id`
- `ventas.producto_id` → `productos.id`

---

##  Uso

### 1. Acceso Inicial

1. Abre: `http://127.0.0.1/ProyectoWeb/auth/login.php`
2. Ingresa credenciales (ver sección de credenciales)
3. Se redirige automáticamente al dashboard: `http://127.0.0.1/ProyectoWeb/views/dashboard.php`

### 2. Navegación

Desde el dashboard, usa el **sidebar** para:
- **Clientes** - Gestionar clientes
- **Proveedores** - Gestionar proveedores
- **Productos** - Gestionar productos
- **Ventas** - Registrar y ver ventas
- **Compras** - Registrar y ver compras
- **Reportes** - Ver reportes (en desarrollo)
- **Configuración** - Ajustes del sistema (en desarrollo)

### 3. Cerrar Sesión

Haz clic en **"Cerrar Sesión"** en el sidebar o dropdown de usuario.

---

##  Credenciales de Prueba

### Usuario Admin
- **Email**: `admin@hotmail.com`
- **Contraseña**: (Generada con bcrypt; si no funciona, usar la siguiente)
- **Hash almacenado**: `$2y$10$01Ijmpp3pKdfdGGyTywi.OT1L1HokM.oXK.gG0BFsX.m5amCR7VHy`

### Usuario Secretaria
- **Email**: `secretaria1@gmail.com`
- **Contraseña**: (Generada con bcrypt; si no funciona, usar la siguiente)
- **Hash almacenado**: `$2y$10$WyIolwWRcX24o9Z.tYheM.d4NVIMu9AitsUp2XY0J5EMBL5fHrzHm`

### Generar Nuevas Contraseñas

Si necesitas cambiar contraseñas, usa el script de test:

1. Abre: `http://127.0.0.1/ProyectoWeb/auth/test_password.php`
2. Verá contraseñas generadas y sus hashes
3. Copia el hash generado
4. Actualiza en phpMyAdmin:
   ```sql
   UPDATE usuarios 
   SET password = '[HASH_COPIADO]' 
   WHERE correo = 'admin@hotmail.com';
   ```

---

##  Seguridad

### Medidas Implementadas

- ✅ **Contraseñas hasheadas** con bcrypt (`password_hash` / `password_verify`)
- ✅ **Sesiones PHP** para autenticación
- ✅ **Validación de entrada** con `htmlspecialchars` para prevenir XSS
- ✅ **Prepared statements (PDO)** para prevenir SQL injection
- ✅ **Control de acceso** - redirect a login si no hay sesión

### Recomendaciones para Producción

- 🔐 Cambiar contraseña de usuario `root` en MySQL
- 🔐 Usar HTTPS en lugar de HTTP
- 🔐 Implementar CSRF tokens en formularios
- 🔐 Validar y sanitizar **todas** las entradas de usuario
- 🔐 Usar variables de entorno para credenciales sensibles
- 🔐 Implementar rate limiting en login
- 🔐 Registrar intentos fallidos de login

---

##  Notas de Desarrollo

### Archivos de Prueba Disponibles

- **test_conn.php** - Verifica conexión a base de datos
- **test_password.php** - Prueba hashes y genera nuevos

### Rutas Dinámicas

El sistema usa URLs relativas y calcula la base URL dinámicamente:
```php
$url_base = $scheme . '://' . $host . '/ProyectoWeb/';
```

### Escaping de Salidas

Todas las salidas de usuario están escapadas con `htmlspecialchars()`:
```php
echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8');
```

### PDO Connection

Se usa PDO para conexión a base de datos (más seguro que mysqli):
```php
$conn = new PDO($dsn, $dbUser, $dbPass, $options);
```

---

##  Troubleshooting

### "Error de conexión a la base de datos: SQLSTATE[HY000] [1045]"

**Causa**: Contraseña incorrecta para usuario `root`

**Solución**:
1. Abre phpMyAdmin (`http://localhost/phpmyadmin`)
2. Verifica la contraseña de `root` en la sección de usuarios
3. Actualiza `config/config.php` con la contraseña correcta

### "Credenciales incorrectas" en login

**Causa**: Usuario no existe o contraseña es diferente

**Solución**:
1. Abre `http://127.0.0.1/ProyectoWeb/auth/test_password.php`
2. Verifica qué contraseña coincide con el email
3. O resetea la contraseña desde phpMyAdmin (ver sección de Credenciales)

### "Not Found - The requested URL was not found on this server"

**Causa**: Archivo no existe o ruta incorrecta

**Solución**:
1. Verifica que Apache esté corriendo
2. Confirma la ruta: `http://127.0.0.1/ProyectoWeb/[archivo]`
3. Si es necesario, ajusta la URL según tu configuración de XAMPP

### "Fatal error: Call to a member function prepare() on null"

**Causa**: `$conn` es null (conexión falló)

**Solución**: Verifica config.php y la conexión a BD (ver primer error)

### Imágenes rotas en dashboard

**Causa**: Archivo de imagen no existe

**Solución**:
1. Coloca imágenes en la carpeta `imagen/`
2. O actualiza el nombre en la tabla `usuarios` en phpMyAdmin

---

##  Licencia

Este proyecto es de uso libre para propósitos educativos y comerciales.

---

##  Soporte

Si encuentras problemas:

1. Revisa la sección **Troubleshooting**
2. Verifica los logs de Apache en XAMPP
3. Usa los scripts de test (`test_conn.php`, `test_password.php`)
4. Consulta phpMyAdmin para validar datos

---

##  Próximas Mejoras

- [ ] Implementar módulo de reportes dinámicos
- [ ] Agregar gráficos con Chart.js
- [ ] Sistema de notificaciones en tiempo real
- [ ] Exportar datos a PDF/Excel
- [ ] API REST para integración externa
- [ ] Autenticación de dos factores (2FA)
- [ ] Registro de auditoría (logs)

---

**Versión**: 2.1.0  
**Última actualización**: Diciembre 2025  
**Desarrollado con**: PHP 8.2, MySQL/MariaDB, Bootstrap 5

