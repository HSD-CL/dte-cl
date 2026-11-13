# Design

## Context

Motivación y alcance: ver `proposal.md`. Requisitos: ver `specs/pdf-dte/spec.md`.

Estado actual, verificado en el código:

- `PdfDte` extiende `sasco\LibreDTE\Sii\Dte\PDF\Dte` (LibreDTE 21.10.5, TCPDF 6.4.1). `LetterFormatStrategy::build` llama a `PdfDte::agregar($datos, $ted)`; cuando el documento va sin timbre, pasa `null` como timbre.
- `agregar()` del padre despacha a `agregar_papel_0()`, que es **privado**. Ese método dibuja cabecera, detalle (tabla HTML con `<thead>`, que TCPDF repite al saltar de página), subtotal, descuentos/recargos y pagos en flujo; luego dibuja el pie en posiciones fijas porque `timbre_pie = true`:
  - observación en `y = 175` (190 − 15), solo si hay `TermPagoGlosa`, `MedioPago` o `TermPagoDias`;
  - totales, timbre y acuse de recibo en `y = 190`; leyenda cedible en `y = 238`.
- Hoja carta: 279,4 mm de alto; el salto automático de TCPDF ocurre en 259,4 mm (margen inferior de 20 mm) y las páginas nuevas empiezan en `y = 27`.
- `PdfDte::addWithoutTimbre` es una copia de `agregar_papel_0` sin timbre. Nadie lo llama, ni en este paquete ni en contaplex-api.
- `PdfDte::$encoding = 'ISO-8859-1'` es casi inerte: TCPDF solo lo usa para decodificar entidades HTML y para `text-transform`. El texto se dibuja en modo Unicode, así que el daño de codificación viene en los datos de `getDatos()`.
- `PdfFileFactory::make` carga el XML con `EnvioDte::loadXML(file_get_contents(...))`. El parser obedece la cabecera del XML: si declara `ISO-8859-1` y los bytes son UTF-8, cada byte se lee como un carácter.
- La máquina de desarrollo no tiene PHP ni `vendor/` en este repositorio; hay Docker y la imagen `sail-8.1/app` de contaplex-api.

## Goals / Non-Goals

**Goals:**

- Que el pie nunca se superponga a los datos en papel carta, con una regla derivada de las posiciones reales del pie y no de un número mágico.
- Una sola implementación del flujo carta, compartida por el camino con timbre y sin timbre.
- Normalizar la codificación con una heurística que no pueda dañar un XML bien codificado.
- Tests que verifiquen la paginación con datos medibles (número de páginas y coordenadas), no solo visualmente.

**Non-Goals:**

- Cambiar el comportamiento cuando `timbre_pie = false` (pie pegado al detalle): se conserva el flujo del padre.
- Rediseñar el pie, moverlo arriba en la página nueva o repetir la cabecera del emisor en las páginas siguientes.
- Controlar el desborde de un bloque de totales excepcionalmente alto (muchos impuestos retenidos) más allá de 259 mm; es un caso preexistente.
- Editar LibreDTE o TCPDF en `vendor/`.

## Decisions

### 1. Sobrescribir `agregar()` en `PdfDte` solo para papel carta

`agregar()` asigna `$this->dte` (tipo de DTE) y, si `papelContinuo` es 0, delega en un método protegido propio; en cualquier otro caso llama a `parent::agregar()`. El método propio replica `agregar_papel_0` con el punto de control del pie.

- **Por qué**: `agregar_papel_0` es privado, así que no se puede extender ni interceptar entre el detalle y el pie.
- **Alternativa descartada — `setTimbrePie(false)`**: el pie fluye pegado al detalle, pero con 30–40 líneas el bloque de totales + timbre + acuse (más de 50 mm) se parte o se sale de la página.
- **Alternativa descartada — sobrescribir `agregarTotales`/`agregarTimbre`**: cada uno tendría que decidir el salto por separado y coordinarse; el punto de decisión correcto es único y está antes de la observación.

### 2. Regla del salto: límite según el pie realmente presente

El control se hace una vez, después de detalle, subtotal, descuentos/recargos y pagos, y antes de la observación (donde hoy se asigna `x_fin_datos`):

```
limite = 190                      (inicio de totales/timbre/acuse)
si hay observación: limite = 175  (190 − 15)
si getY() > limite − margen:  AddPage()
```

- El margen de seguridad es una constante pequeña (2 mm) para que el borde inferior de la tabla no toque el pie.
- Las posiciones (190, 15, margen) se declaran como constantes con nombre en `PdfDte`, documentando que reflejan los valores por defecto de LibreDTE.
- Tras `AddPage()`, `getY()` vuelve a 27 mm y el pie se dibuja con las mismas llamadas del padre, por lo que queda en su posición habitual.
- La regla solo aplica con `timbre_pie = true`; con `false` no se fuerza ningún salto.
- **Por qué no "185 fijo"**: con observación de pago el choque empieza en 175 mm, unas 3 líneas antes.
- **Por qué no medir con transacciones de TCPDF** (`startTransaction`/`rollbackTransaction`): el pie tiene posición fija, así que no hay nada que medir; basta comparar el fin de los datos con el inicio del pie.

El detalle multipágina no requiere código: el salto automático de TCPDF ya parte la tabla y repite el encabezado. La regla se evalúa sobre la `Y` de la última página del detalle, lo que cubre los casos de 60 líneas (pie en la página 2) y 80 líneas (página 3 solo con el pie).

### 3. `addWithoutTimbre` delega en el mismo flujo

El método protegido del flujo carta recibe el timbre (o `null`). `addWithoutTimbre($dte)` se conserva como método público por compatibilidad y pasa a ser un envoltorio que invoca ese flujo con timbre `null`. `agregarTimbre(null)` ya no dibuja nada en el padre, así que no hace falta una bandera adicional.

- **Alternativa descartada — eliminar `addWithoutTimbre`**: es API pública del paquete; quitarlo sería un cambio incompatible sin beneficio.

`addContinuousPaper80` no se toca.

### 4. Exponer la distribución del pie para los tests

`PdfDte` guarda, al dibujar el pie, la página y las coordenadas relevantes, y las expone con un método público de solo lectura (por ejemplo `getDistribucionPie()` con `pagina`, `y_fin_datos`, `y_inicio_pie`). Los tests comprueban `getNumPages()` y que `y_fin_datos <= y_inicio_pie` en la página del pie.

- **Por qué**: los flujos de contenido del PDF van comprimidos y con fuentes en subconjunto; extraer coordenadas del archivo exigiría un parser de PDF como dependencia de desarrollo.
- **Alternativa descartada — subclase de prueba que sobrescriba `agregarTotales`**: acopla el test a la firma de un método de LibreDTE.

### 5. Normalización de codificación en una utilidad, invocada desde `PdfFileFactory`

Nueva clase en `src/Util/` (por ejemplo `XmlEncoding`) con un método estático `normalize(string $xml): string`. Convierte a ISO-8859-1 solo si se cumplen las tres condiciones:

1. la declaración XML indica `ISO-8859-1` (sin distinguir mayúsculas);
2. el contenido tiene al menos un byte ≥ 0x80;
3. el contenido completo es UTF-8 válido (`mb_check_encoding`).

En cualquier otro caso devuelve el XML sin cambios. `PdfFileFactory::make` la aplica al contenido leído antes de `loadXML`.

Ajuste durante la implementación: si el contenido tiene caracteres que no existen en ISO-8859-1 (por ejemplo comillas tipográficas), la conversión los perdería. En ese caso se conserva el contenido en UTF-8 y se corrige la cabecera a `UTF-8`. Al convertir también se quita el BOM de UTF-8 si lo hay.

- **Por qué es segura**: un texto real en ISO-8859-1 con acentos casi nunca es UTF-8 válido (una "ñ" es el byte 0xF1, que exige bytes de continuación que no estarán). Para ser un falso positivo, el original tendría que contener literalmente secuencias como "Ã±".
- **Por qué convertir los bytes y no cambiar la cabecera a UTF-8**: mantiene el XML coherente con lo que LibreDTE espera (ISO-8859-1) en el resto del flujo, incluido el timbre.
- **Por qué en `PdfFileFactory` y no en `PdfDte`**: cuando los datos llegan a `PdfDte` ya son un arreglo dañado; repararlo ahí obligaría a recorrer y adivinar campo por campo.
- **Alternativa descartada — corregir `DteReceived` en contaplex-api**: decisión del usuario; queda fuera de este cambio (ver Non-Goals de la propuesta).

`PdfDte::$encoding` no se modifica.

### 6. Estrategia de tests y PDF de ejemplo

- **Paginación**: test en `tests/Feature/` que construye `PdfDte` directamente con un arreglo de documento sintético (encabezado, N líneas de detalle, totales) y un timbre ficticio (cadena cualquiera; `write2DBarcode` no valida el contenido). Casos con *data provider*: 10, 24, 30, 45, 60 y 80 líneas, más una variante con observación de pago y otra sin timbre.
- **Codificación**: test unitario de `XmlEncoding::normalize` con los cuatro escenarios de la spec, y un test que carga un DTE mínimo normalizado con LibreDTE y verifica que `getDatos()` entrega "Peñuelas".
- **Ejemplos visuales**: los tests escriben los PDF en `build/pdf-samples/` (ya ignorado por git); después se copian al scratchpad de la sesión para la revisión.
- **Ejecución**: Docker con PHP 8.1 (imagen `sail-8.1/app`, o `php:8.1-cli` con Composer si no está disponible), montando el repositorio.

Resultado medido con el documento sintético del test: hasta 23 líneas, 1 página; de 24 a 40, el pie pasa a la página 2; de 41 a ~75, el detalle sigue en la página 2 y el pie cabe ahí; 76 o más, 3 páginas. Por eso el caso de 45 líneas no agrega página solo para el pie y se sumó el de 40.

Antes de medir, las páginas esperadas de 24, 45, 60 y 80 líneas eran estimaciones a partir del PDF real (tabla desde ~78 mm, ~4,3 mm por línea); se confirman al ejecutar y se ajustan en el test si la cabecera sintética mide distinto. El invariante que no depende de la estimación es `y_fin_datos <= y_inicio_pie`.

## Risks / Trade-offs

- [El pie queda solo en una página casi vacía] → Es el comportamiento pedido (posición habitual); se revisa visualmente con los PDF de ejemplo.
- [Las constantes 190/15 duplican valores por defecto de LibreDTE y podrían desalinearse si la librería cambia] → LibreDTE está fijado en `^21.10`; las constantes quedan documentadas y los tests fallarían ante un cambio.
- [`phpunit.xml` usa un formato anterior a PHPUnit 10 y la suite no se ha ejecutado en este entorno] → Los tests nuevos se ejecutan por archivo; si la configuración impide correrlos, se informa antes de tocarla, porque migrarla no es parte de este cambio.
- [Falso positivo de la heurística de codificación] → Requiere texto ISO-8859-1 que sea a la vez UTF-8 válido con multibyte; extremadamente improbable y cubierto con tests de no alteración.
- [El XML sigue mal guardado en contaplex-api] → Aceptado; pendiente aparte en ese repositorio.
- [Cargar un DTE mínimo sin firma con LibreDTE podría fallar en el test de codificación] → Si ocurre, la verificación de extremo a extremo se hace con `DOMDocument` sobre el XML normalizado y se deja constancia.

## Migration Plan

Sin migración de datos ni de configuración. Publicación según `docs/RELEASING.md`, solo cuando el usuario lo pida:

1. Commit en la rama `fix/` y PR hacia `develop` (el destino por defecto en GitHub es `master`; hay que cambiarlo).
2. Tras el merge: `git checkout develop && git pull`, `git tag 0.10.44`, `git push origin 0.10.44`.
3. En contaplex-api: cambiar `"hsd-cl/dte-cl": "0.10.43"` por `"0.10.44"` en `composer.json` y ejecutar `composer update hsd-cl/dte-cl`. No hace falta `vendor:publish` ni `config:clear`.
4. Los PDF ya generados no se regeneran solos (contaplex-api omite los DTE con `has_pdf`); hay que regenerar a mano los afectados.

Reversión: volver el pin de contaplex-api a `0.10.43`.
