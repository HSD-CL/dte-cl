# Changelog

All notable changes to `dte-cl` will be documented in this file

## 0.10.44 - 2026-11-12

### Fixed
- PDF en formato carta: el pie del documento (observación, totales, timbre, acuse de recibo y leyenda cedible) ya no se dibuja encima del detalle. Cuando los datos del documento invaden la zona del pie, el pie pasa a una página nueva en su posición habitual; los documentos largos tienen por eso una página más. Los detalles de varias páginas dejan el pie en la última.
- PDF de XML que declaran `ISO-8859-1` pero vienen en UTF-8: los caracteres no ASCII se muestran bien ("Peñuelas" en vez de "PeÃ±uelas"). Los XML correctamente codificados no se alteran.
- PDF en formato carta con detalle de varias páginas: el encabezado de la tabla que se repite en cada página queda alineado con las filas (antes salía corrido 5 mm a la derecha).

### Added
- `Util\XmlEncoding::normalize()` para corregir la codificación del XML antes de generar el PDF; `PdfFileFactory::make()` lo aplica siempre.
- `PdfDte::getDistribucionPie()` para consultar en qué página y posición quedó el pie.

### Changed
- `PdfDte::agregar()` usa un flujo propio en papel carta; el papel continuo sigue delegando en LibreDTE.
- `PdfDte::addWithoutTimbre()` comparte ese mismo flujo.

## 1.0.0 - 201X-XX-XX

- initial release
