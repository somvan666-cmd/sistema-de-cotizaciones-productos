# Sistema de Cotizaciones de Productos

Una aplicación web completa para la gestión de cotizaciones de productos desarrollada con HTML, PHP, MySQL y Bootstrap.

## 🚀 Características

- **Búsqueda de Productos**: Buscador en tiempo real con resultados dinámicos
- **Gestión de Cotizaciones**: Creación y gestión de cotizaciones con múltiples productos
- **Gestión de Clientes**: Registro y reutilización de información de clientes
- **Exportación PDF**: Generación de cotizaciones en formato PDF (Carta o Rollo)
- **Interfaz Moderna**: Diseño responsive con Bootstrap 5
- **Base de Datos MySQL**: Almacenamiento persistente de datos

## 📋 Requisitos del Sistema

- **PHP**: Versión 7.4 o superior
- **MySQL**: Versión 5.7 o superior
- **Servidor Web**: Apache/Nginx con soporte PHP
- **Navegador Web**: Chrome, Firefox, Safari, Edge (últimas versiones)

## 🛠️ Instalación y Configuración

### 1. Configuración de la Base de Datos

1. Crear la base de datos MySQL:
```sql
CREATE DATABASE mi_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Ejecutar el script de inicialización:
```bash
php init_database.php
```

O ejecutar manualmente el archivo `create_database.sql` en phpMyAdmin o MySQL Workbench.

### 2. Configuración del Servidor Web

1. Copiar todos los archivos al directorio raíz del servidor web (ej: `/var/www/html/` o `htdocs/`)

2. Asegurar que PHP tenga acceso a MySQL:
   - Instalar extensión PDO MySQL si no está disponible
   - Verificar configuración en `php.ini`

3. Configurar permisos:
```bash
chmod 755 /ruta/al/proyecto
chmod 644 /ruta/al/proyecto/*.php
chmod 755 /ruta/al/proyecto/images/
```

### 3. Configuración de Conexión a Base de Datos

Editar el archivo `db.php` si es necesario:
```php
$host = 'localhost';      // Cambiar si es necesario
$username = 'root';       // Usuario de MySQL
$password = '';          // Contraseña de MySQL
$database = 'mi_base';   // Nombre de la base de datos
```

## 📁 Estructura del Proyecto

```
/
├── db.php                    # Conexión a base de datos
├── index.php                 # Página principal de la aplicación
├── search_products.php       # API de búsqueda de productos
├── search_client.php         # API de búsqueda de clientes
├── save_quotation.php        # API para guardar cotizaciones
├── generate_pdf.php          # Generación de PDFs
├── init_database.php         # Script de inicialización de BD
├── create_database.sql       # Esquema de la base de datos
├── fpdf/                     # Biblioteca PDF
│   └── fpdf.php
├── images/                   # Imágenes de productos
│   ├── default-product.jpg
│   └── [otras imágenes]
└── README.md                 # Este archivo
```

## 🗄️ Estructura de la Base de Datos

### Tabla `products`
- `id`: Identificador único
- `nombre`: Nombre del producto
- `descripcion`: Descripción detallada
- `imagen`: Ruta de la imagen
- `precio_unitario`: Precio por unidad
- `created_at`: Fecha de creación
- `updated_at`: Fecha de actualización

### Tabla `clientes`
- `id`: Identificador único
- `ci`: Carnet de identidad/NIT
- `nombre`: Nombre del cliente
- `apellido`: Apellido del cliente
- `celular`: Número de teléfono
- `created_at`: Fecha de creación
- `updated_at`: Fecha de actualización

### Tabla `cotizaciones`
- `id`: Identificador único
- `numero_cotizacion`: Número único de cotización
- `cliente_id`: Referencia al cliente
- `fecha`: Fecha de creación
- `validez`: Días de validez
- `observaciones`: Notas adicionales
- `total`: Total de la cotización
- `moneda`: Moneda (BOB/USD)
- `tipo_cambio`: Tipo de cambio
- `created_at`: Fecha de creación

### Tabla `cotizacion_detalles`
- `id`: Identificador único
- `cotizacion_id`: Referencia a la cotización
- `producto_id`: Referencia al producto
- `cantidad`: Cantidad solicitada
- `precio_unitario`: Precio unitario
- `subtotal`: Subtotal del producto
- `created_at`: Fecha de creación

## 🎯 Uso de la Aplicación

### 1. Acceso a la Aplicación
Abrir el navegador web y acceder a:
```
http://localhost/index.php
```

### 2. Búsqueda de Productos
- Ingresar términos de búsqueda en el campo principal
- Los resultados aparecen automáticamente
- Hacer clic en "Agregar" para incluir productos en la cotización

### 3. Gestión de Clientes
- Ingresar CI/NIT del cliente
- Si existe, se autocompletan los datos
- Si no existe, completar nombre, apellido y teléfono

### 4. Creación de Cotización
- Seleccionar productos y cantidades
- Configurar vigencia de la oferta
- Agregar observaciones si es necesario
- Hacer clic en "Guardar Cotización"

### 5. Exportación PDF
- Hacer clic en "Exportar PDF"
- Seleccionar moneda (Bolivianos/Dólares)
- Elegir formato (Carta/Rollo)
- El PDF se descarga automáticamente

## 🔧 Solución de Problemas

### Error de Conexión a Base de Datos
- Verificar credenciales en `db.php`
- Asegurar que MySQL esté ejecutándose
- Verificar permisos de usuario MySQL

### Error al Generar PDF
- Verificar permisos de escritura en el directorio
- Asegurar que la biblioteca FPDF esté completa
- Verificar configuración de PHP

### Problemas de Permisos
```bash
# Dar permisos correctos
chmod -R 755 /ruta/al/proyecto/
chown -R www-data:www-data /ruta/al/proyecto/
```

## 📊 Datos de Prueba

La aplicación incluye datos de prueba:
- **8 productos** de limpieza y hogar
- **3 clientes** de ejemplo
- Productos con nombres como "Dimax Pro", "Detergente Líquido", etc.

## 🔒 Seguridad

- Uso de prepared statements para prevenir SQL injection
- Validación de datos de entrada
- Sanitización de datos del usuario
- Conexión segura a base de datos con PDO

## 📝 Notas de Desarrollo

- **Framework Frontend**: Bootstrap 5 para diseño responsive
- **Backend**: PHP puro con arquitectura MVC simple
- **Base de Datos**: MySQL con índices optimizados
- **PDF Generation**: Biblioteca FPDF personalizada
- **AJAX**: JavaScript nativo para interacciones dinámicas

## 🤝 Soporte

Para soporte técnico o reportes de errores:
1. Verificar los logs de error de PHP
2. Revisar la consola del navegador para errores JavaScript
3. Verificar permisos de archivos y directorios
4. Asegurar que todas las dependencias estén instaladas

## 📄 Licencia

Este proyecto es de código abierto y puede ser utilizado libremente para fines comerciales y no comerciales.

---

**Desarrollado con ❤️ para la gestión eficiente de cotizaciones**
