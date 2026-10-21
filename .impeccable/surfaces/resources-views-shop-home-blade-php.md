---
version: 1
slug: "resources-views-shop-home-blade-php"
primary_target: "resources/views/shop/home.blade.php"
related_targets: ["resources/views/shop/product.blade.php","resources/views/layouts/app.blade.php"]
---

# Tienda pública TEZAN

Scope: todas las vistas públicas de la tienda (portada, catálogo, ficha de producto, bolsa, checkout, confirmación, mis pedidos, login y registro). El panel admin queda fuera. Mode: Persuade en la portada y la ficha; Operate en la bolsa, el checkout y los pedidos, dentro del mismo mundo.

Audiencia: jóvenes peruanos unisex, sobre todo desde el celular. Éxito = verse premium. No perder el blanco y negro; no parecer plantilla.

## Direction contract

THESIS: La tienda se recorre como el piso de damero de una casona limeña: un solo módulo de baldosa cuadrada ordena toda la página y cada prenda ocupa sus baldosas. Rechaza la plantilla de foto a todo el ancho con titular centrado y una fila de tarjetas iguales.

OWN-WORLD: Mármol negro #141413 y mármol blanco #f2f2ef como únicas superficies, con una veta gris solo para texto secundario. Las esquinas son rectas porque las baldosas lo son. La marca y los títulos van en una serif lapidaria (Marcellus); el texto, en una grotesca de trabajo (Hanken Grotesk), siempre en tipo oración y sin rótulos en mayúsculas. Lo activo o seleccionado es siempre la baldosa más oscura. Una cenefa de baldosas pequeñas marca el borde del piso.

STORY: El visitante entiende en una pantalla que son polos hechos en Perú, de buena tela y precio claro. Cree en la calidad por el detalle concreto y elige talla y color sin salir de la portada.

FIRST VIEWPORT: Retícula de 8×4 baldosas. TEZAN monumental ocupa 4×2 baldosas negras arriba a la izquierda; la foto de la prenda destacada, 3×3 blancas a la derecha; la frase de la marca, 4×1. La baldosa negra de compra (3×1, bajo la foto) lleva nombre, precio, tallas y colores elegibles y «Agregar a la bolsa», que funciona sin JS. Las baldosas restantes llevan los hechos (hecho en Perú, envío gratis desde S/ 200, tarjeta o Yape, cambios de talla).

FORM: Damero limeño, candidata 3 de 7 en la lista propia ordenada. seed:427eeb4a8bf343b1. Interacción distintiva: elegir talla o color oscurece esa baldosa. Movimiento: un solo momento al cargar la portada, cuando las baldosas del primer viewport se asientan en diagonal como un piso que se coloca; respeta prefers-reduced-motion.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance
