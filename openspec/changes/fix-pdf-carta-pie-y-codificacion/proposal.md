# Proposal

## Why

Los PDF en formato carta de los DTE con más de ~24 líneas de detalle salen ilegibles: los totales, el timbre y el acuse de recibo se dibujan en una posición fija (190 mm) encima del detalle. Caso real: una factura recibida de 32 líneas, donde el choque empieza en la línea 25. Afecta por igual a documentos recibidos y emitidos, porque comparten el mismo código.

En el mismo PDF, los textos con caracteres no ASCII de documentos recibidos salen dañados ("PeÃ±uelas" en vez de "Peñuelas"), porque el XML llega con bytes UTF-8 pero declarando `ISO-8859-1` en su cabecera.

## What Changes

- El PDF carta deja de superponer el pie (observación, totales, timbre, acuse de recibo y leyenda cedible) sobre el detalle: cuando los datos del documento invaden la zona del pie, el pie se dibuja en una página nueva, en su posición habitual.
- Los detalles que ocupan más de una página siguen fluyendo con el encabezado de tabla repetido, y el pie queda siempre en la última página.
- El flujo carta "sin timbre" (órdenes de compra) recibe el mismo comportamiento; `addWithoutTimbre` pasa a compartir la misma lógica.
- Antes de generar el PDF, el XML se normaliza cuando declara `ISO-8859-1` pero su contenido es UTF-8 válido con caracteres multibyte. Los XML realmente codificados en ISO-8859-1, los declarados UTF-8 y los solo ASCII no se alteran.
- Tests nuevos de paginación (10, 24, 30, 45, 60 y 80 líneas) y de codificación, más PDF de ejemplo para revisión visual.
- Sin cambios en papel continuo (`addContinuousPaper80`) ni en la API pública que usa contaplex-api.

Fuera de alcance:

- La asimetría de `utf8_encode` en `DteReceived` de contaplex-api (origen del XML mal codificado); queda como pendiente aparte en ese repositorio.
- La resolución vacía en el timbre ("Resolución  de"), causada por la errata `cartula` en `LetterFormatStrategy`.
- El uso de `$options` indefinido en `LetterFormatStrategy::build`.

## Capabilities

### New Capabilities

- `pdf-dte`: generación de la representación impresa (PDF) de los DTE a partir de su XML: distribución del contenido en la página, paginación y codificación del texto.

### Modified Capabilities

Ninguna (el proyecto aún no tiene specs).

## Impact

- **Código**: `src/Sii/Base/Pdf/PdfDte.php` (sobrescritura de `agregar()` para papel carta y refactor de `addWithoutTimbre`), `src/Sii/Factory/PdfFileFactory.php` (normalización del XML) y una utilidad nueva de codificación en `src/Util/`.
- **Tests**: nuevos tests en `tests/Feature/`; requieren PHP 8.1 vía Docker, porque la máquina de desarrollo no tiene PHP ni `vendor/` instalado.
- **Consumidores**: contaplex-api (`BuildPdfFromDteFactory::makePdf` → `LetterFormatStrategy`) recibe el arreglo al subir el pin de `0.10.43` a `0.10.44`; no requiere cambios de código ni de configuración.
- **Comportamiento visible**: los documentos largos pasan a tener una página más; los documentos cortos no cambian.
- **Dependencias**: ninguna nueva. Se apoya en `sasco/libredte` 21.10 y TCPDF 6.4.1 tal como están.
