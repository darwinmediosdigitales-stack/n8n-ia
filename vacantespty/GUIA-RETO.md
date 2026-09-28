# Reto diario en empleoshoypanama.com/reto (versión 1.2)

## Instalación (5 minutos)
1. **Respaldo** en Hostinger: Websites → Files → **Backups**.
2. WordPress → **Plugins → Añadir nuevo plugin → Subir plugin** → `vacantespty-core.zip` → **Reemplazar el actual con el subido**.
3. WordPress → **Apariencia → Temas → Añadir tema → Subir tema** → `vacantespty-tema.zip` → **Reemplazar el actual con el subido**.
4. Entra a cualquier página del admin: se crea sola la página **/reto** y las tablas del reto.
5. **Ajustes → Enlaces permanentes → Guardar cambios.**
6. **LiteSpeed Cache → Purgar todo.**
7. Abre **empleoshoypanama.com/reto** en tu celular y juega un día de prueba.

## Qué cambió en el reto
- Está en **modo app**: pantalla completa, solo tu logo y el botón "Ver vacantes". Sin ventana emergente ni botón flotante.
- Usa la letra del sitio (Lexend), así que carga menos archivos.
- El anuncio propio dice **"CV Impacto · Vacantes PTY — por solo B/. 5.99"** y lleva a **/curriculum-profesional/**.
- **Premio de 7 días:** 50% de descuento en el **CV Profesional Premium**. **Premio de 14 días:** un CV Básico gratis.
- **Los códigos de premio ahora los emite tu servidor**, no el celular. Nadie puede inventarse uno.
- Aparece en el menú (**🎯 Reto diario**), como chip destacado en la portada y en el pie de página.

## Cómo verificar un premio antes de entregarlo
1. La persona te manda su código por DM, por ejemplo `VPTY-2809-K7M2QX`.
2. WordPress → **Vacantes → Códigos del Reto** → pega el código → **Verificar**.
   - ✅ **Código válido** → entrega el premio y presiona **Marcar como canjeado**.
   - ⚠️ **Ya canjeado** → no lo entregues otra vez.
   - ❌ **No válido** → no fue emitido por el sitio: es falso.

En esa misma pantalla ves cuántas personas jugaron hoy, el total de jugadores y cuántas tienen rachas de 7 días o más.

## Cambiar premios, anuncio y precios (sin programar)
**Vacantes → Ajustes**
- **Reto diario:** texto del premio de 7 días, premio de 14 días, y título y texto del anuncio propio.
- **Planes de currículum:** nombre y precio de los 3 planes. El cobro con Yappy usa estos precios.

## Código QR
- `qr/qr-reto-diario.png` (1200 px, para imprimir) y `qr/qr-reto-diario.svg` (vector, para imprenta o volantes grandes).
- Lleva a `https://empleoshoypanama.com/reto?utm_source=qr&utm_medium=impreso`, así en Google Analytics sabes cuántos llegaron escaneando.
- Imprímelo de mínimo **3 × 3 cm** y deja un borde blanco alrededor.

## Preparado para miles de visitas
El juego corre en el celular de cada persona. Tu servidor solo recibe **una petición pequeña al día por jugador** (al sellar el día), así que 10,000 jugadores diarios son unas 10,000 peticiones repartidas en el día: poco para tu plan.

Antes de imprimir los QR:
- [ ] **LiteSpeed Cache** activo (hPanel → WordPress → LiteSpeed, o el plugin LiteSpeed Cache). La página /reto se entrega desde caché, sin cargar WordPress.
- [ ] **CDN de Hostinger** activo si tu plan lo incluye (hPanel → Performance → CDN).
- [ ] Pruébala en celular con datos móviles, no solo con Wi-Fi.
- [ ] Juega 1 día completo y confirma que en **Códigos del Reto** aparece "Jugadores de hoy: 1".

## A tener en cuenta
- El progreso de cada persona vive en su celular. Si borra el historial del navegador o cambia de teléfono, empieza de cero. El reto ya incluye su opción de **respaldo del progreso**.
- Al sellar el día hace falta internet, para que el servidor registre la racha. Si no hay conexión, se reintenta solo al día siguiente.
- El comodín semanal y el rescate con fichas se respetan: el servidor tolera **un día perdido** sin romper la racha.
