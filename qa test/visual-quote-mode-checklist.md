# QA Visual Checklist - Quote Mode (Lag Medical)

## Preparacion

- En `config/lagmedical.php`, deja solo `default` en `quote_mode_channels`.
- Ejecuta: `php artisan optimize:clear`.
- Abre dos sesiones:
  - Canal `default` (quote mode).
  - Canal no listado (ecommerce normal, por ejemplo Florida).

## 1) Product Detail Page (PDP)

### Canal `default`

- No se muestra precio regular/especial/rango.
- Se muestran nombre, imagenes, descripcion y atributos.
- Se muestra CTA de carrito con texto de solicitud (quote).
- No aparece Buy Now (compra directa).

### Canal normal (no listado)

- Pricing visible normal.
- Buy Now visible si aplica por configuracion.

## 2) Product Listing / Category / Search

### Canal `default`

- Cards sin precios.
- Sin descuentos/promociones con monto.
- Nombre, imagen y CTA visibles.

### Canal normal

- Precios/promociones visibles normalmente.

## 3) Mini Cart / Cart Drawer

### Canal `default`

- Se muestran items, nombre, cantidad y remove.
- No se muestra precio unitario.
- No se muestra subtotal/total.
- Boton principal con texto quote (no checkout transaccional).
- Mensaje visible: revision por ventas.

### Canal normal

- Precio y subtotal visibles.
- Boton normal: Continue to Checkout.

## 4) Cart Page

### Canal `default`

- Lista de productos y cantidades visible.
- Sin precio por item.
- Sin subtotal / grand total.
- Sin bloque de cupones.
- Sin tax / shipping amounts.
- Mensaje de revision visible.
- Boton continuar con texto de solicitud.

### Canal normal

- Comportamiento normal con montos/cupones/totales.

## 5) Checkout Onepage

### Canal `default`

- Paso Address funcional.
- Shipping methods visibles sin monto `$`.
- Payment step no visible como pago directo.
- Summary sin montos.
- Boton final con texto de solicitud (no Place Order orientado a pago).

### Canal normal

- Flujo address/shipping/payment/review intacto con montos.

## 6) Place Order (flujo quote)

### Canal `default`

- Permite finalizar y crear orden.
- Pantalla success sin errores.

### Canal normal

- Flujo transaccional normal.

## 7) Customer Account - My Orders

### Canal `default`

- Lista de ordenes sin totales visibles.
- Order detail / PDF sin precios, subtotales, tax, shipping ni grand total.
- Se muestran productos, qty, estado y direcciones.
- Mensaje de revision visible.

### Canal normal

- Totales y montos visibles normalmente.

## 8) Emails de orden

Validar en `default`:

- `created`
- `invoiced`
- `shipped`
- `refunded`
- `canceled`
- `commented`

### En canal `default` deben ocultar

- precio unitario
- subtotal
- discount
- tax
- shipping charge
- grand total
- payment detail

### En canal `default` deben mostrar

- productos
- cantidades
- datos de cliente
- direcciones (billing/shipping cuando aplique)
- mensaje de revision por ventas

### En canal normal

- Emails intactos con montos y totales.

## 9) Idioma (solo mensajes de lagmedical)

Verificar:

- `es`: textos en espanol.
- `pt_BR`: textos en portugues.
- Otros locales (ej. `fr`): fallback a ingles.

Puntos de chequeo:

- Label del boton en PDP/cart/mini-cart/checkout.
- Mensaje de revision en cart/checkout/email/pdf.

## 10) Validacion de seguridad visual

- En quote mode, inspeccionar HTML y confirmar que bloques de precio/totales no se renderizan.
- Confirmar que no se ocultan solo por CSS (`display:none`) como estrategia principal.

---

## Registro QA (llenar)

| Caso | Canal default | Canal normal | Evidencia |
|---|---|---|---|
| PDP sin precio | OK / NOK | OK / NOK | URL + screenshot |
| Listing sin precio | OK / NOK | OK / NOK | URL + screenshot |
| Mini-cart sin subtotal | OK / NOK | OK / NOK | screenshot |
| Cart sin totales | OK / NOK | OK / NOK | screenshot |
| Checkout sin montos | OK / NOK | OK / NOK | screenshot |
| My Orders sin totales | OK / NOK | OK / NOK | screenshot |
| Emails sin montos | OK / NOK | OK / NOK | email capture |
| PDF sin montos | OK / NOK | OK / NOK | PDF capture |
| Locale es/pt_BR + fallback en | OK / NOK | OK / NOK | screenshot |
