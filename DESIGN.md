---
name: TEZAN
description: Damero limeño. La tienda pública se ordena como el piso de mármol blanco y negro de una casona limeña.
colors:
  marble-black: "#141413"
  marble-black-soft: "#2a2927"
  marble-white: "#f2f2ef"
  marble-white-deep: "#e4e3de"
  vein: "#5e5c57"
  vein-light: "#b3b0a8"
  surface: "#ffffff"
typography:
  display:
    fontFamily: "Marcellus, 'Times New Roman', serif"
    fontSize: "14.6cqi"
    fontWeight: 400
    lineHeight: 0.82
    letterSpacing: "-0.02em"
  heading:
    fontFamily: "Marcellus, 'Times New Roman', serif"
    fontSize: "clamp(1.35rem, 2.4cqi, 2.1rem)"
    fontWeight: 400
    lineHeight: 1.2
    letterSpacing: "-0.01em"
  body:
    fontFamily: "'Hanken Grotesk Variable', 'Segoe UI', sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  price:
    fontFamily: "'Hanken Grotesk Variable', 'Segoe UI', sans-serif"
    fontSize: "16px"
    fontWeight: 600
    lineHeight: 1.5
    letterSpacing: "normal"
rounded:
  none: "0"
spacing:
  tile-pad: "clamp(1rem, 1.6cqi, 1.25rem)"
  page-gutter: "16px"
  cenefa: "10px"
components:
  button-primary:
    backgroundColor: "{colors.marble-black}"
    textColor: "{colors.marble-white}"
    rounded: "{rounded.none}"
    padding: "16px 24px"
  button-primary-hover:
    backgroundColor: "#000000"
  button-secondary:
    backgroundColor: "transparent"
    textColor: "{colors.marble-black}"
    rounded: "{rounded.none}"
    padding: "16px 24px"
  choice-tile:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.marble-black}"
    rounded: "{rounded.none}"
    height: "44px"
  choice-tile-selected:
    backgroundColor: "{colors.marble-black}"
    textColor: "{colors.marble-white}"
  filter-tile:
    backgroundColor: "{colors.marble-white-deep}"
    textColor: "{colors.marble-black}"
    rounded: "{rounded.none}"
    padding: "12px 18px"
  filter-tile-active:
    backgroundColor: "{colors.marble-black}"
    textColor: "{colors.marble-white}"
---

# Design System: TEZAN

## Overview

La tienda pública de TEZAN se recorre como el piso de damero de una casona de Barranco o del Centro de Lima. Un solo módulo de baldosa cuadrada ordena la página: la portada es una retícula de 8×4 baldosas en escritorio (4 columnas en celular) y cada prenda ocupa sus propias baldosas. El blanco y negro no es una paleta elegida: es el material del piso. Todo vive en `resources/sass/_damero.scss`, aplicado solo bajo `body.tz` (el panel admin queda fuera).

La identidad "old money" se transmite con una serif lapidaria a escala monumental y con la renuncia a los adornos: sin sombras, sin esquinas redondeadas, sin rótulos en mayúsculas.

## Colors

### Primary
- **Mármol negro** `#141413`: baldosas oscuras, botones principales, cabecera de marca, pie y todo lo activo o seleccionado.

### Neutral
- **Mármol blanco** `#f2f2ef`: suelo de la página y baldosas claras.
- **Mármol blanco en sombra** `#e4e3de`: fondo de fotos que aún cargan, filtros sin elegir y la franja de navegación móvil.
- **Mármol negro pulido** `#2a2927`: controles sin elegir sobre una baldosa negra.
- **Veta** `#5e5c57`: texto secundario sobre blanco (6:1).
- **Veta clara** `#b3b0a8`: texto secundario sobre negro (8:1).
- **Superficie** `#ffffff`: campos de formulario y la barra de cabecera.

### Named Rules
- **La baldosa más oscura manda.** Lo activo o seleccionado (talla, color, filtro, enlace de navegación actual, bolsa) es siempre la baldosa más oscura de su grupo. Sobre una baldosa negra, lo elegido pasa a negro puro con borde blanco.
- **Sin tercer color.** No hay acento: el color solo aparece en las fotos de las prendas y en las muestras de color de cada variante.

## Typography

### Hierarchy
- **Display** (Marcellus 400, 14.6cqi en escritorio y 22cqi en celular, interlineado 0.82): solo la palabra TEZAN en la portada.
- **Títulos** (Marcellus 400): h1 a h3, nombres de prenda destacados y la frase de marca. Nunca en negrita: la serif no tiene corte bold y la negrita falsa queda prohibida.
- **Texto** (Hanken Grotesk Variable 400/500, 16px/1.5): todo lo demás, siempre en tipo oración.
- **Precio** (Hanken Grotesk 600 con cifras tabulares): clase `.tz-price`.

### Named Rules
- **Tipo oración siempre.** Nada de `text-uppercase` ni de tracking ancho en la tienda pública.
- Las dos fuentes se sirven desde el proyecto vía `@fontsource` (importadas en `resources/js/app.js`).

## Layout

- **La baldosa como regla de medida.** `.tz-floor` es una retícula con `--cols` (4 en celular, 8 desde 992px) y `--tile: 100cqi / --cols`, de modo que las filas son cuadradas a cualquier ancho. La portada asigna áreas (`word`, `photo`, `copy`, `buy`, `f1`–`f4`, `l1`–`l4`) que alternan blanco y negro como un damero.
- **Catálogo sin juntas.** `.tz-grid` coloca las prendas contiguas (2, 3 o 4 columnas). La banda de información de cada prenda alterna negro y blanco con una paridad de damero calculada para cada número de columnas.
- **Cenefa.** `.tz-cenefa` es la franja de baldosas pequeñas que cierra el primer viewport, el pie y la cabecera de la bolsa lateral.
- El margen de página en celular es de 16px.

## Elevation & Depth

No hay elevación. La profundidad la dan el cambio de tono entre baldosas y las fotos. No se usan sombras ni líneas divisorias: las separaciones son por tono, espacio o escala.

## Shapes

Esquinas rectas en todo (`border-radius: 0`): las baldosas son cuadradas.

## Components

### Buttons
- Principal: negro sólido con texto blanco y peso 600. En hover pasa a negro puro.
- Secundario: contorno negro de 1px. En hover se rellena de negro.
- Sobre una baldosa negra, el principal se invierte (`.btn-light` en blanco mármol).

### Chips (baldosas de elección)
`.tz-choice`: radio oculto más una baldosa de 44px con borde de 1px. Si está elegida, se rellena de negro. Si no hay stock, lleva borde punteado y texto tachado. Las muestras de color usan `hex_code` de la variante.

### Cards / Containers
`.tz-card` dentro de `.tz-grid`: foto cuadrada y banda de información con nombre, precio y la marca "Esencial" si la prenda está destacada. Al mover el cursor sobre la foto se recorren las imágenes de la prenda.

### Inputs / Fields
Borde negro de 1px, fondo blanco y esquinas rectas. El foco es un anillo negro sólido de 2px.

### Navigation
La cabecera blanca está fija arriba. A la izquierda, la baldosa negra de marca; a la derecha, la baldosa negra de la bolsa con su contador. El enlace activo se vuelve baldosa negra. En celular, las categorías van en una franja desplazable en mármol blanco en sombra.

### Baldosa de compra (componente distintivo)
`.tz-buy`, en la portada: un formulario completo que funciona sin JavaScript (talla, color y "Agregar a la bolsa") sobre una baldosa negra. Con JavaScript, deshabilita las combinaciones sin stock.

## Do's and Don'ts

### Do:
- Componer cada nueva superficie con el módulo de baldosa y alternar blanco y negro como un piso.
- Dejar que lo activo sea la baldosa más oscura.
- Usar Marcellus a escala grande y siempre en peso 400.
- Mostrar solo hechos confirmados: hecho en Perú, envío gratis desde S/ 200, tarjeta o Yape.

### Don't:
- No añadir un color de acento, degradados ni sombras.
- No usar rótulos en mayúsculas ni etiquetas tipo eyebrow sobre los títulos.
- No imitar vetas de mármol con CSS: el material se expresa con el tono plano, no con una textura falsa.
- No separar con líneas finas lo que puede separarse por tono.
- No inventar políticas, por ejemplo de cambios o devoluciones, mientras no estén definidas en PRODUCT.md.
