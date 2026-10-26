# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
Jóvenes peruanos, hombres y mujeres por igual (unisex), que compran básicos para el día a día desde el celular o la computadora. Buscan prendas que se vean bien y duren, sin pagar precios de marca importada. Compran como invitados o con cuenta, y pagan con tarjeta o Yape.

## Product Purpose
TEZAN es una tienda online de ropa (polos y básicos) con catálogo, carrito, checkout para invitados o clientes registrados e historial de pedidos. Éxito = que el visitante encuentre su talla y color disponibles, confíe en la calidad y complete la compra; y que el equipo gestione catálogo, inventario y pedidos desde el panel admin.

## Positioning
Prendas hechas en Perú, con telas de calidad y buen fit, a precio accesible, dentro de una estética "old money" atemporal. La combinación de confección local + calidad de tela + precio es lo que una marca importada o de fast fashion no puede afirmar con verdad.

## Operating Context
- Moneda: soles (S/). Envío S/ 12, gratis desde S/ 250 (desde el 2026-10-24; antes S/ 200), despachos a todo el Perú.
- Pago: Mercado Pago (tarjeta) y Yape/Plin (manual). Ver Brand Commitments.
- Cada prenda tiene variantes por talla (S, M, L, XL) y color, con stock por variante.
- Secciones Varones y Mujeres. Categorías (2026-10-24): Polos clásicos, Poleras, Conjuntos polera y pantalón, Conjuntos casaca y pantalón buzo, Polos piqué con cuello (varones y mujeres); Tops (solo mujeres); Polos waffle (solo varones). Cada prenda es de varón, de mujer o unisex.
- Panel admin: productos, categorías, pedidos (con estados logísticos y de pago), clientes y alertas de stock bajo.

## Capabilities and Constraints
- Stack existente: Laravel 13 + Blade, Bootstrap 5 + SCSS compilados con Vite. Sin framework JS.
- Interfaz en español (Perú).
- Sin generación de imágenes disponible en este entorno.
- Abierto: política real de cambios y devoluciones, términos y condiciones, y textos de "Envíos y Entregas" (hoy los enlaces del footer no llevan a ningún lado).

## Brand Commitments
- Nombre: TEZAN (razón social mostrada: TEZAN S.A.C.).
- Estética "old money" / atemporal: es identidad de marca y debe mantenerse.
- La tienda debe leerse de inmediato como un e-commerce de moda convencional, con Zara como referencia de nivel (preferencia confirmada el 2026-10-20, tras descartar una dirección conceptual "Damero limeño"). Lo que se descartó: el blanco y negro plano y la portada tipo póster con la marca gigante. La calidez viene de la fotografía.
- Existe un logo propio. **Pendiente:** el archivo aún no está en el repositorio; hoy el sitio usa la palabra "TEZAN" en texto.
- Contacto: tezancompany@gmail.com; WhatsApp y Yape 942 817 550 (titular TEZAN S.A.C.). RUC 20616005163, Av. Lima 767.
- Pago (desde 2026-10-25): tarjeta vía Mercado Pago (se confirma solo) y Yape o Plin al mismo número 942 817 550 (confirmación manual del admin). Los pedidos nacen "pendiente" + "por pagar".

## Evidence on Hand
- Redes sociales activas. **Pendiente:** las URLs reales; hoy los íconos de Instagram/Facebook del footer apuntan a "#".
- Las imágenes del hero son de stock (Unsplash). Las fotos de producto en `storage/app/public/products` no están confirmadas como propias.
- No hay testimonios, reseñas, cifras de ventas ni prensa: no inventarlos.

## Product Principles
1. La disponibilidad manda: talla, color y stock siempre visibles y verdaderos antes de pedir un clic.
2. La calidad se demuestra con detalle concreto (tela, fit, hecho en Perú), no con adjetivos.
3. Comprar debe ser corto y confiable en el celular: envío, pago y total claros en todo momento.
4. Atemporal antes que de temporada: nada que envejezca en seis meses.
