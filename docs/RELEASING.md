# Publicación de una nueva versión de la librería

Esta librería (`hsd-cl/dte-cl`) se distribuye como paquete Composer a través de
**Packagist** y se consume desde proyectos padre (p. ej. `hsd-cl/billing-api` /
Contaplex). Publicar una versión y que el proyecto padre la reciba son **pasos
distintos**; este documento describe la cadena completa.

## Conceptos clave

- **La rama de trabajo real es `develop`, no `master`.** `master` está desactualizada
  (~2 años atrás) y los releases se cortan desde `develop`. El último tag vive en
  `develop`.
- **Packagist versiona por _tags_ de git, no por ramas.** Mergear un PR a `develop`
  **no publica** nada instalable: solo deja el código en la rama. Lo que publica una
  versión es **crear y pushear un tag** (semver, serie `0.10.x`).
- **Un merge a `develop` no basta para el proyecto padre.** Además del tag, el padre
  suele estar pineado a una versión exacta, así que hay que subirle el número.

## Paso a paso

### 1. Trabajar el cambio (repo `dte-cl`)

```bash
git checkout develop && git pull
git checkout -b feat/mi-cambio
# ... editar, commitear ...
git push -u origin feat/mi-cambio
# abrir PR: feat/mi-cambio -> develop y mergear
```

### 2. Publicar la versión con un tag (repo `dte-cl`)

Una vez mergeado a `develop`, cortar el tag **desde `develop`**. Elegir el número
según la naturaleza del cambio (retrocompatible = patch en la serie `0.10.x`):

```bash
git checkout develop && git pull
git tag 0.10.43
git push origin 0.10.43        # ← ESTE paso publica la versión
```

Con el webhook de GitHub → Packagist (lo normal), la versión aparece en segundos en
<https://packagist.org/packages/hsd-cl/dte-cl>. Si no está el webhook, entrar al
paquete en Packagist y pulsar **Update**.

### 3. Consumir la versión en el proyecto padre (repo `billing-api` / Contaplex)

El `composer.json` del padre suele pinear la versión **exacta**:

```json
"hsd-cl/dte-cl": "0.10.42",
```

Con un pin exacto, `composer update` **no** sube solo aunque exista el tag nuevo: hay
que editar el número a mano y luego actualizar.

```json
"hsd-cl/dte-cl": "0.10.43",
```

```bash
composer update hsd-cl/dte-cl
```

> 💡 Alternativa: usar `"^0.10.43"` en vez de exacto para recibir parches futuros
> (`0.10.44`, …) con un simple `composer update`, sin editar el `composer.json` cada
> vez. Mantener el pin exacto es más conservador; es decisión del equipo.

### 4. Refrescar config en el padre (si el cambio toca configuración)

```bash
php artisan vendor:publish --provider="HSDCL\DteCl\DteClServiceProvider" --tag=config
php artisan config:clear       # o config:cache en producción
```

## Resumen de la cadena

```
PR -> develop        (no publica: solo deja el código en la rama)
        │
        ▼
tag 0.10.43 + push   (publica en Packagist)   ← paso que realmente publica
        │
        ▼
subir el pin en el padre a 0.10.43
        │
        ▼
composer update hsd-cl/dte-cl  (+ vendor:publish / config:clear si aplica)
```

Sin el tag, no hay versión publicada. Sin subir el pin en el padre, el padre se
queda en la versión anterior aunque el tag exista.
