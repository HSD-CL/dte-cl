# pdf-dte Specification

## Purpose

Genera la representación impresa (PDF) de los DTE a partir de su XML, garantizando que el contenido sea legible: distribución en la página, paginación y codificación correcta del texto.

## Requirements

### Requirement: El pie del PDF carta no se superpone a los datos del documento

En formato carta, el pie del documento (observación, totales, timbre, acuse de recibo y leyenda cedible) SHALL dibujarse sin superponerse al detalle ni a los bloques que lo siguen (subtotal, descuentos y recargos globales, pagos). Cuando esos datos invaden la zona reservada para el pie, el pie MUST dibujarse en una página nueva, en la misma posición vertical que ocupa en un documento corto.

#### Scenario: Documento corto en una sola página

- **WHEN** se genera el PDF carta de un documento con 10 líneas de detalle
- **THEN** el PDF tiene 1 página y el pie queda en su posición habitual, debajo del final del detalle

#### Scenario: El detalle invade la zona del pie

- **WHEN** se genera el PDF carta de un documento con 30 líneas de detalle
- **THEN** el PDF tiene 2 páginas, la primera contiene todo el detalle y la segunda contiene el pie completo en su posición habitual

#### Scenario: Documento en el límite

- **WHEN** se genera el PDF carta de un documento con 24 líneas de detalle
- **THEN** el pie comienza después del final de los datos en la página donde se dibuja, sin superposición, tenga el PDF 1 o 2 páginas

#### Scenario: Documento con observación de pago

- **WHEN** el documento incluye glosa de término de pago, medio de pago o días de pago, y el detalle termina dentro de la zona donde se dibuja la observación
- **THEN** la observación y el resto del pie se dibujan en una página nueva, sin superponerse al detalle

### Requirement: El pie queda en la última página cuando el detalle ocupa varias páginas

Cuando el detalle no cabe en una página, el detalle SHALL continuar en las páginas siguientes repitiendo el encabezado de la tabla, y el pie MUST aparecer una sola vez, en la última página del documento.

#### Scenario: Detalle de dos páginas con espacio para el pie

- **WHEN** se genera el PDF carta de un documento con 60 líneas de detalle
- **THEN** el detalle continúa en la segunda página con el encabezado de tabla repetido, y el pie se dibuja en esa misma segunda página sin agregar otra

#### Scenario: La última página del detalle invade la zona del pie

- **WHEN** se genera el PDF carta de un documento con 80 líneas de detalle, cuyo detalle termina en la segunda página dentro de la zona del pie
- **THEN** el PDF tiene 3 páginas y el pie se dibuja solo en la tercera

### Requirement: El PDF carta sin timbre aplica las mismas reglas de paginación

Cuando el PDF carta se genera sin timbre (por ejemplo, órdenes de compra), el sistema SHALL aplicar las mismas reglas de paginación del pie y MUST omitir únicamente el timbre.

#### Scenario: Documento largo sin timbre

- **WHEN** se genera el PDF carta sin timbre de un documento con 30 líneas de detalle
- **THEN** el PDF tiene 2 páginas, los totales quedan en la segunda sin superponerse al detalle y no se dibuja ningún timbre

### Requirement: El papel continuo no cambia

La generación de PDF en papel continuo (formato térmico) SHALL conservar su comportamiento actual: una sola página con alto autocalculado.

#### Scenario: Boleta en papel continuo

- **WHEN** se genera el PDF de una boleta en papel continuo de 80 mm
- **THEN** el resultado es una sola página, igual que antes de este cambio

### Requirement: El texto del PDF se muestra con la codificación correcta

Antes de generar el PDF, el sistema SHALL interpretar correctamente un XML cuya cabecera declara `ISO-8859-1` pero cuyo contenido es UTF-8 válido con caracteres multibyte, de modo que los caracteres no ASCII se muestren tal como los escribió el emisor.

#### Scenario: XML con UTF-8 declarado como ISO-8859-1

- **WHEN** se genera el PDF de un XML que declara `ISO-8859-1` y contiene "Peñuelas" codificado en UTF-8
- **THEN** la dirección del emisor se lee "Peñuelas" y no "PeÃ±uelas"

### Requirement: Los XML correctamente codificados no se alteran

El sistema MUST NOT modificar el contenido de un XML cuya codificación real coincide con la declarada, ni el de un XML que solo contiene caracteres ASCII.

#### Scenario: XML realmente codificado en ISO-8859-1

- **WHEN** se genera el PDF de un XML que declara `ISO-8859-1` y contiene "Peñuelas" codificado en ISO-8859-1
- **THEN** el contenido del XML no se modifica y la dirección se lee "Peñuelas"

#### Scenario: XML declarado como UTF-8

- **WHEN** se genera el PDF de un XML que declara `UTF-8` y contiene "Peñuelas" codificado en UTF-8
- **THEN** el contenido del XML no se modifica y la dirección se lee "Peñuelas"

#### Scenario: XML solo con caracteres ASCII

- **WHEN** se genera el PDF de un XML que declara `ISO-8859-1` y no contiene caracteres fuera de ASCII
- **THEN** el contenido del XML no se modifica

### Requirement: El PDF muestra la resolución del SII del sobre

Cuando el XML es un sobre (`EnvioDTE` o `EnvioBOLETA`) cuya carátula trae número y fecha de resolución, el PDF SHALL mostrar bajo el timbre la línea "Resolución N de AAAA" con esos datos, tanto en formato carta como en papel continuo. El número de resolución 0 MUST tratarse como válido.

#### Scenario: Sobre con resolución

- **WHEN** se genera el PDF de un sobre cuya carátula indica resolución 80 del 2014-08-22
- **THEN** bajo el timbre se lee "Resolución 80 de 2014"

#### Scenario: Sobre del ambiente de certificación

- **WHEN** se genera el PDF de un sobre cuya carátula indica resolución 0 del 2014-08-22
- **THEN** bajo el timbre se lee "Resolución 0 de 2014"

### Requirement: La línea de resolución se omite cuando no se conoce

Cuando no hay una resolución real disponible, el PDF MUST omitir la línea de resolución bajo el timbre y conservar las demás leyendas. El sistema MUST NOT mostrar una resolución que no provenga de la carátula original del XML.

#### Scenario: Carátula sin resolución

- **WHEN** se genera el PDF de un sobre cuya carátula no trae número o fecha de resolución
- **THEN** bajo el timbre aparecen "Timbre Electrónico SII" y "Verifique documento", sin la línea de resolución

#### Scenario: DTE suelto sin sobre

- **WHEN** se genera el PDF de un XML que contiene solo el DTE, sin sobre
- **THEN** no se muestra ninguna resolución, tampoco una generada automáticamente

### Requirement: Los valores numéricos del detalle caben en un renglón

En formato carta, las columnas numéricas del detalle (cantidad, precio unitario, descuento, recargo y total) SHALL ensancharse según el contenido del documento para que cada valor quede en un solo renglón, tomando el espacio de la columna del nombre del ítem. Cuando los valores caben en el ancho habitual, los anchos MUST NOT cambiar.

#### Scenario: Precio unitario con decimales

- **WHEN** se genera el PDF carta de un documento cuyo precio unitario es 1563211.76
- **THEN** el precio se muestra como "1.563.211,76" en un solo renglón y la fila tiene el mismo alto que con un precio entero

#### Scenario: Valores que caben en el ancho habitual

- **WHEN** se genera el PDF carta de un documento cuyos valores numéricos caben en el ancho habitual de sus columnas
- **THEN** las columnas conservan su ancho habitual
