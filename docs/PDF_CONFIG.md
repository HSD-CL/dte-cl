# Configuración de los metadatos del PDF de los DTE

Los PDF que genera el paquete (`HSDCL\DteCl\Sii\Base\Pdf\PdfDte`) estampan cuatro
metadatos: **título**, **autor**, **creador** y **texto de pie de página**.
Antes estos valores estaban *hardcodeados* con la identidad de ARISA. Ahora son
configurables desde el proyecto padre a través de su `.env`.

## Cómo funciona

El flujo sigue el patrón estándar de Laravel: `.env` → archivo de config → `config()`.

1. `config/config.php` expone la sección `pdf`, leyendo cada valor del entorno con
   un *default* (la identidad ARISA original como fallback):

   ```php
   'pdf' => [
       'title'   => env('DTE_PDF_TITLE',   'ARISA'),
       'author'  => env('DTE_PDF_AUTHOR',  'ARISA - https://www.arisa.cl'),
       'creator' => env('DTE_PDF_CREATOR', 'ARISA - https://www.arisa.cl'),
       'footer'  => env('DTE_PDF_FOOTER',  'ARISA - https://www.arisa.cl'),
   ],
   ```

   El `DteClServiceProvider` hace `mergeConfigFrom(config/config.php, 'dte-cl')`, por
   lo que la sección queda accesible como `config('dte-cl.pdf.*')`.

2. `PdfDte::__construct()` lee esos valores (con el mismo default como red de
   seguridad) y, opcionalmente, permite sobreescribirlos por instancia mediante el
   array `$options`:

   ```php
   $this->SetTitle($options['title'] ?? config('dte-cl.pdf.title', 'ARISA'));
   $this->SetAuthor($options['author'] ?? config('dte-cl.pdf.author', 'ARISA - https://www.arisa.cl'));
   $this->SetCreator($options['creator'] ?? config('dte-cl.pdf.creator', 'ARISA - https://www.arisa.cl'));
   $this->setFooterText($options['footer'] ?? config('dte-cl.pdf.footer', 'ARISA - https://www.arisa.cl'));
   ```

Se usa `config()` en lugar de `env()` directo dentro del código de dominio porque
`env()` devuelve `null` cuando el proyecto padre ejecuta `php artisan config:cache`,
salvo que se invoque dentro de un archivo de config. Este patrón sobrevive al
cacheo de configuración.

## Uso en el proyecto padre

### Opción A — variables de entorno (recomendada)

Basta con definir las variables en el `.env` del proyecto padre:

```dotenv
DTE_PDF_TITLE=Mi Empresa
DTE_PDF_AUTHOR=Mi Empresa - https://miempresa.cl
DTE_PDF_CREATOR=Mi Empresa - https://miempresa.cl
DTE_PDF_FOOTER=Mi Empresa - https://miempresa.cl
```

Si no se define alguna, se mantiene el default `ARISA`.

### Opción B — publicar y editar la config

Publicar el archivo de configuración y editarlo directamente:

```bash
php artisan vendor:publish --provider="HSDCL\DteCl\DteClServiceProvider" --tag=config
```

Esto copia el archivo a `config/dte-cl.php` en el proyecto padre, donde se pueden
ajustar los valores de la sección `pdf`.

### Override por instancia

Para un PDF puntual se pueden pasar valores directamente al construir `PdfDte`:

```php
$pdf = new PdfDte([
    'title'  => 'Documento especial',
    'footer' => 'Pie personalizado',
]);
```

## Archivos involucrados

| Archivo | Cambio |
| --- | --- |
| `config/config.php` | Nueva sección `pdf` que lee del entorno con defaults. |
| `src/Sii/Base/Pdf/PdfDte.php` | El constructor lee de `$options` / `config('dte-cl.pdf.*')` en vez de valores fijos. |
| `.env.example` | Documenta las variables `DTE_PDF_*`. |
