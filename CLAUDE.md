# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Qué es este proyecto

Paquete **Laravel** (`hsd-cl/dte-cl`, tipo `library`) para la facturación electrónica de Chile (DTE / SII). Se instala vía Composer dentro de un proyecto Laravel *padre* y expone comandos Artisan `dte:*` y una fachada `DteCl`. Toda la lógica de firma, timbrado y envío al SII se apoya en la librería `sasco/libredte`; este paquete es la capa de orquestación (builders + fuentes de datos + comandos) sobre ella.

Como es un paquete instalable, el código corre dentro del contexto Laravel del proyecto padre: helpers como `env()` y `config()` leen el `.env`/config del padre, no de este repo.

## Comandos

```bash
composer install              # instalar dependencias (requiere PHP ^7.3)
composer test                 # correr toda la suite (vendor/bin/phpunit)
composer test-coverage        # cobertura HTML en coverage/

# un solo test
vendor/bin/phpunit --filter nombreDelMetodo
vendor/bin/phpunit tests/Feature/BasicCertificationBuilderTest.php
```

Los tests usan `orchestra/testbench` para arrancar un Laravel mínimo (el `post-autoload-dump` de composer ejecuta `package:discover`). El `phpunit.xml` inyecta un `APP_KEY` de prueba. StyleCI aplica el preset `laravel` (ver `.styleci.yml`).

## Publicación de versiones

La rama de trabajo real es **`develop`** (no `master`, que está desactualizada); los releases se cortan desde `develop`. Packagist versiona por **tags** de git (serie `0.10.x`), no por ramas: mergear un PR **no publica** — publica el `git tag`. El proyecto padre suele estar pineado a una versión exacta, así que además hay que subirle el número. Ver el procedimiento completo en [`docs/RELEASING.md`](docs/RELEASING.md).

## Documentación

- [`docs/RELEASING.md`](docs/RELEASING.md) — cómo publicar una nueva versión (tag + Packagist) y consumirla en el proyecto padre.
- [`docs/PDF_CONFIG.md`](docs/PDF_CONFIG.md) — configurar los metadatos del PDF de los DTE desde el `.env` del padre.

## Arquitectura

### Registro en Laravel — `DteClServiceProvider`
- `register()`: `mergeConfigFrom(config/config.php, 'dte-cl')` (config accesible como `config('dte-cl.*')`) y registra el singleton `dte-cl` (fachada `DteCl`).
- `boot()` (solo en consola): publica la config a `config_path('dte-cl.php')` (tag `config`) y registra los ~9 comandos `dte:*`.
- **`config/config.php`** expone la configuración del paquete leyendo del `.env` del padre vía `env()` con defaults (hoy la sección `pdf` con los metadatos del PDF — ver [`docs/PDF_CONFIG.md`](docs/PDF_CONFIG.md)). Es el punto natural para nuevos ajustes configurables: agregarlos aquí y leerlos con `config('dte-cl.*')` (patrón seguro ante `config:cache`), nunca con `env()` directo en el código de dominio.

### Flujo principal: certificación / envío de DTEs
Entrada por comandos Artisan en `src/Console/Commands/*` (p. ej. `BasicCertificationCommand`). Cada comando recibe todo por flags CLI (`--firma`, `--pass`, `--folios-*`, `--source`, datos de emisor/receptor, `--output`), arma la firma y los folios, y delega en un *builder*.

El núcleo es `Sii\Base\PacketDteBuilder` (abstracto), extendido por las clases en `Sii\Certification\*Builder`. El pipeline canónico está en `build()`:

```
parse()            # Source::getCases() → array de documentos
  → setStampAndSign()   # por doc: new LibreDTE\Dte, timbrar(folios), firmar(firma), agregar al EnvioDte (agent)
  → setCaratula()
  → setSign()
export($filename)  # escribe el XML del sobre (agent->generar())  |  send() lo envía al SII
```

El `EnvioDte` de LibreDTE es el "agent" acumulador. `Sii::setAmbiente()` se fija en el constructor de `PacketDteBuilder` (default `CERTIFICACION`).

### Piezas transversales
- **Fuentes de datos** — interfaz `Sii\Base\Source` con `FileSource` (lee el "set de pruebas" del SII vía `SetPruebas::getJSON`, con `sanatize()` que corrige encoding ISO-8859-1 y casos especiales) y `JsonSource`. `getCases()` devuelve los documentos parseados.
- **Firma** — trait `Util\SignatureFactory` (`makeFirma($file, $pass)` → `FirmaElectronica` desde un `.pfx` + password). El password suele venir de `env('FIRMA_PASS')`.
- **Configuration** — `Util\Configuration` es un registro *singleton por alias* de rutas de archivos (folios `.xml`, archivos de source). No es config de Laravel; solo resuelve y cachea paths reales.
- **PDF** — `Sii\Factory\PdfFileFactory::make()` toma un XML `EnvioDte` y emite un PDF por documento usando `Sii\Base\Pdf\PdfDte` (extiende `LibreDTE\...\PDF\Dte`). TCPDF y el generador de QR están *vendorizados* dentro de `src/Util/Tcpdf/` — no editar salvo necesidad puntual.

### Capas (según el grafo)
`Console` y `tests/*` son *entry points* → llaman a `Sii` (core, alto fan-in) y `Util` (core). `Sii/Certification/ShipmentBookCertificactionBuilder` y `Util/Configuration` son los hotspots de mayor fan-in.

## Convenciones
- Comentarios y docblocks en español; autoría original de David Lopez.
- Los builders siguen fluent interface (retornan `$this` / `PacketDteBuilder`) para encadenar el pipeline.
- Cada tipo de DTE de la certificación tiene su comando + builder paralelos (basic, exempt, shipment, export, purchase-book, sales-book, etc.).
