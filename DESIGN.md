---
name: TEZAN
description: E-commerce de moda clásico con Zara como barra de calidad. La fotografía manda y la interfaz se retira.
colors:
  ink: "#111111"
  paper: "#ffffff"
  warm: "#f3f2ef"
  warm-deep: "#e6e4df"
  muted: "#6b6760"
  line: "#e2ded7"
  field-border: "#b9b4ab"
typography:
  logo:
    fontFamily: "'Bodoni Moda', Didot, 'Times New Roman', serif"
    fontSize: "clamp(1.6rem, 3vw, 2.25rem)"
    fontWeight: 500
    lineHeight: 1
    letterSpacing: "0.18em"
  campaign:
    fontFamily: "'Bodoni Moda', Didot, 'Times New Roman', serif"
    fontSize: "clamp(2.5rem, 6vw, 5.25rem)"
    fontWeight: 400
    lineHeight: 1
    letterSpacing: "normal"
  label:
    fontFamily: "'Hanken Grotesk Variable', 'Helvetica Neue', Arial, sans-serif"
    fontSize: "0.8125rem"
    fontWeight: 500
    lineHeight: 1.5
    letterSpacing: "0.08em"
  body:
    fontFamily: "'Hanken Grotesk Variable', 'Helvetica Neue', Arial, sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
rounded:
  none: "0"
spacing:
  grid-gap: "4px"
  page-gutter: "16px"
  page-gutter-lg: "48px"
components:
  button-primary:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.paper}"
    rounded: "{rounded.none}"
    padding: "14px 24px"
  button-primary-hover:
    backgroundColor: "#000000"
  button-secondary:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    padding: "14px 24px"
  choice:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    height: "44px"
  choice-selected:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.paper}"
---

# Design System: TEZAN

## Overview

La tienda pública es un e-commerce de moda convencional, ejecutado con el nivel de Zara, que es la referencia que el usuario eligió. Las fotos de campaña y de producto ponen el color y la calidez. La interfaz es blanca, negra y gris cálido, con esquinas rectas y sin sombras. Todo vive en `resources/sass/_tienda.scss`, aplicado bajo `body.tz`; el panel admin no lo usa.

Esta dirección reemplazó a «Damero limeño», descartada por el usuario por verse plana y poco comercial (ver PRODUCT.md, Brand Commitments).

## Colors

### Primary
- **Tinta** `#111111`: texto, botones principales, opción elegida y barra de anuncio.

### Neutral
- **Papel** `#ffffff`: fondo de la página.
- **Gris cálido** `#f3f2ef`: fondo de las fotos mientras cargan, franja de beneficios y pie.
- **Gris cálido profundo** `#e6e4df`: pista de la barra de envío gratis.
- **Apagado** `#6b6760`: texto secundario (5.6:1).
- **Línea** `#e2ded7`: borde inferior de la cabecera y divisores del pie y de los detalles.
- **Borde de campo** `#b9b4ab`: inputs y opciones sin elegir.

### Named Rules
- **La foto pone el color.** No hay color de acento en la interfaz.

## Typography

### Hierarchy
- **Logo** (Bodoni Moda 500, tracking 0.18em): TEZAN en la cabecera, el menú móvil y el pie.
- **Campaña** (Bodoni Moda 400): el titular del banner, los títulos de sección del catálogo y los nombres en los banners de categoría.
- **Etiqueta** (Hanken Grotesk 500, 13px, mayúsculas, tracking 0.08em, clase `.tz-label`): navegación, botones, nombres de prenda en la grilla, encabezados del pie y de los detalles.
- **Texto** (Hanken Grotesk 400, 16px/1.5): descripciones, formularios y textos de ayuda.

### Named Rules
- **Las mayúsculas son solo de interfaz.** Navegación, botones y etiquetas van en mayúsculas pequeñas, como en la referencia; los párrafos nunca.
- **Sin eyebrow sobre los títulos.** El titular de campaña va solo.

## Layout

- La cabecera es una rejilla de tres columnas: categorías a la izquierda, logo centrado y Buscar, Cuenta y Bolsa (n) a la derecha. En celular, menú lateral y bolsa.
- Portada: banner de campaña (82vh, máximo 820px) con el texto abajo a la izquierda sobre un degradado oscuro de legibilidad; pestañas de categorías; grilla; banners de categoría (solo si hay dos con foto); franja de beneficios.
- Grilla de productos: 2 columnas en celular y 4 desde 992px, fotos 3:4, 4px de separación horizontal y 2rem vertical.
- Ficha de producto: fotos en dos columnas en escritorio (deslizables en celular) y un panel de compra fijo al hacer scroll.

## Elevation & Depth

No hay elevación ni sombras. El único degradado es el de legibilidad sobre las fotos del banner y de los banners de categoría.

## Shapes

Esquinas rectas en todo.

## Components

### Buttons
- Principal: tinta sólida con texto en mayúsculas pequeñas.
- Secundario: contorno de 1px.
- Sobre una foto: blanco sólido o contorno blanco.

### Chips
`.tz-choice`: talla o color en una caja de 44px de alto. Al elegirla se rellena de tinta. Sin stock, queda tachada y deshabilitada.

### Cards / Containers
`.tz-card`: foto 3:4 sobre gris cálido, nombre en mayúsculas pequeñas y precio debajo. La marca "Esencial" va como etiqueta blanca sobre la foto. Al mover el cursor se recorren las fotos de la prenda.

### Inputs / Fields
Borde `#b9b4ab` de 1px, esquinas rectas. Al enfocar, borde de tinta.

### Navigation
Los enlaces de la cabecera se subrayan al pasar el cursor y cuando están activos. Las pestañas de categorías bajo el banner van centradas, y la activa lleva subrayado de tinta.

## Do's and Don'ts

### Do:
- Dejar que la fotografía lleve el peso visual.
- Usar fotos propias de TEZAN cuando existan. El banner usa hoy una foto de stock de Unsplash.
- Mantener visibles las categorías, el precio y la bolsa en todo momento.

### Don't:
- No volver al blanco y negro plano ni a la marca gigante como protagonista.
- No usar fondos crema ni colores de acento en la interfaz.
- No poner etiquetas eyebrow sobre los titulares.
- No inventar políticas (cambios, devoluciones) que no estén en PRODUCT.md.
