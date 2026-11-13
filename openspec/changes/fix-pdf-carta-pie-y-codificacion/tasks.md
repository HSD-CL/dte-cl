# Tasks

## 1. Preparación

- [x] 1.1 Renombrar la rama local `ajuste-pdf` a `fix/pdf-carta-pie-y-codificacion` (sale del mismo commit de `develop`) y verificar con `git status -sb` que es la rama activa; no hacer commit ni push
- [x] 1.2 Instalar dependencias con Docker en PHP 8.1 (imagen `sail-8.1/app`, o `php:8.1-cli` con Composer) y verificar que `vendor/bin/phpunit --version` responde dentro del contenedor — Resuelto de otra forma: Composer 2.10 bloquea la instalación por avisos de seguridad en Laravel 10, así que se usaron las dependencias ya instaladas de contaplex-api (PHP 8.1.34, PHPUnit 10.5.20, LibreDTE 21.10.5, TCPDF 6.4.1); verificación aceptada por el usuario
- [x] 1.3 Ejecutar un test existente (`tests/JsonSourceTest.php`) para confirmar que la suite arranca con el `phpunit.xml` actual; si la configuración lo impide, informar al usuario antes de modificarla — No ejecutado: la suite propia requiere `orchestra/testbench`, que no se pudo instalar; aceptado por el usuario

## 2. Paginación del pie en papel carta

- [x] 2.1 Agregar en `PdfDte` las constantes de posición del pie (inicio en 190 mm, desplazamiento de observación de 15 mm, margen de seguridad) con su docblock en español; verificar que no quedan números mágicos en la regla del salto
- [x] 2.2 Sobrescribir `agregar()` en `PdfDte`: asignar el tipo de DTE, delegar en un método protegido propio cuando `papelContinuo` es 0 y en `parent::agregar()` en otro caso; verificar que el papel continuo sigue pasando por el padre
- [x] 2.3 Implementar el método protegido del flujo carta replicando `agregar_papel_0`, con el salto de página antes de la observación según la regla del diseño (solo con `timbre_pie = true`); verificar con el test de 30 líneas que el PDF tiene 2 páginas
- [x] 2.4 Registrar la distribución del pie (página, fin de datos, inicio del pie) y exponerla con un método público de solo lectura; verificar que devuelve valores coherentes en un documento de 10 líneas
- [x] 2.5 Convertir `addWithoutTimbre` en un envoltorio del mismo flujo con timbre `null`, manteniendo su firma pública, y dejar los métodos privados/protegidos de apoyo al final de la clase; verificar que `addContinuousPaper80` queda sin cambios en el diff
- [x] 2.6 Crear `tests/Feature/PdfDteLetterLayoutTest.php` con *data provider* para 10, 24, 30, 45, 60 y 80 líneas, comprobando número de páginas y que el fin de datos no supera el inicio del pie; verificar que pasa con `vendor/bin/phpunit tests/Feature/PdfDteLetterLayoutTest.php`
- [x] 2.7 Agregar al mismo test los casos con observación de pago (detalle que termina entre 175 y 190 mm) y sin timbre (30 líneas); verificar que ambos pasan
- [x] 2.8 Hacer que el test escriba cada PDF en `build/pdf-samples/` y verificar que se generan los archivos de todos los casos

## 3. Normalización de codificación

- [x] 3.1 Crear la utilidad `src/Util/XmlEncoding.php` con `normalize(string $xml): string` según las tres condiciones del diseño, con docblock en español; verificar con su test unitario
- [x] 3.2 Crear `tests/Unit/XmlEncodingTest.php` con los cuatro escenarios de la spec (UTF-8 declarado ISO-8859-1, ISO-8859-1 real, UTF-8 declarado UTF-8, solo ASCII), comprobando bytes de salida; verificar que pasa
- [x] 3.3 Aplicar `XmlEncoding::normalize` en `PdfFileFactory::make` antes de `loadXML`; verificar en el diff que es el único cambio de esa clase
- [x] 3.4 Agregar un test que cargue con LibreDTE un DTE mínimo con "Peñuelas" en UTF-8 declarado ISO-8859-1, ya normalizado, y compruebe que `getDatos()` entrega "Peñuelas", generando además su PDF en `build/pdf-samples/`; verificar que pasa

## 4. Documentación

- [x] 4.1 Agregar la entrada `0.10.44` en `CHANGELOG.md` con ambos arreglos; verificar que describe el cambio visible (página adicional en documentos largos) y avisar al usuario de que la entrada `0.10.43` vive en la rama `docs/changelog-0.10.43`, aún sin integrar en `develop`

## 5. Verificación integral y entrega

- [x] 5.1 Ejecutar todos los tests nuevos juntos y la suite completa (`composer test`) en el contenedor; informar el resultado tal cual, distinguiendo fallos preexistentes de fallos del cambio — Ejecutados solo los tests nuevos (24, en verde) con las dependencias de contaplex-api; la suite completa no se ejecutó; aceptado por el usuario
- [x] 5.2 Copiar los PDF de `build/pdf-samples/` al scratchpad de la sesión y revisarlos visualmente (10, 24, 30, 45, 60, 80 líneas, con observación, sin timbre y con "ñ"); verificar que ninguno muestra superposición — PDF copiados y revisados; se agregaron los casos de 40 y 150 líneas
- [x] 5.3 Comparar el PDF de 30 líneas con el PDF del caso real y confirmar que el choque de la línea 25 ya no ocurre
- [x] 5.4 Entregar al usuario el resumen del cambio con los pasos de release (tag `0.10.44`, pin en `composer.json` de contaplex-api, regeneración manual de PDF ya emitidos) según el plan de migración del diseño; no hacer commit, tag ni push hasta que lo pida — Entregado

## Workflow follow-up

- Hacer commit, abrir el PR hacia `develop` y publicar el tag `0.10.44` solo cuando el usuario lo solicite.
- Subir el pin en contaplex-api a `0.10.44` y ejecutar `composer update hsd-cl/dte-cl`.
- Registrar como pendiente aparte en contaplex-api la asimetría de `utf8_encode` en `DteReceived`.
- Archivar el cambio cuando esté revisado y publicado.
