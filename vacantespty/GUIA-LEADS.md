# Vacantes PTY 1.1: leads, canal de WhatsApp, currículum con Yappy y Capacítate

## Cómo actualizar (sin perder nada)
1. **Respaldo primero:** Hostinger → Sitios web → Administrar → Archivos → **Copias de seguridad** → crear copia.
2. **Plugins → Añadir nuevo plugin → Subir plugin** → `vacantespty-core.zip` → **Instalar ahora** → **Reemplazar el actual con el subido**.
3. **Apariencia → Temas → Añadir tema → Subir tema** → `vacantespty-tema.zip` → **Reemplazar el actual con el subido**.
4. Entra a cualquier página del admin: se crean solas las tablas y las páginas nuevas.
5. **Ajustes → Enlaces permanentes → Guardar cambios.**
6. Si usas LiteSpeed Cache: **LiteSpeed Cache → Panel → Purgar todo**.

Tus vacantes, entradas, banners y ajustes se mantienen.

### Páginas que se crean automáticamente
| Página | Dirección |
|---|---|
| Alertas de vacantes | `/alertas-de-vacantes/` |
| Gracias (después de registrarse) | `/gracias/` |
| Te creamos tu currículum profesional | `/curriculum-profesional/` |
| Capacítate y destácate | `/capacitate/` |
| Política de Privacidad (Ley 81 de 2019) | `/politica-de-privacidad/` |
| Divulgación de afiliados | `/divulgacion-de-afiliados/` |

---

## 1. Plugins: no hace falta instalar ninguno más

| Necesidad | Solución | Por qué |
|---|---|---|
| Formularios | Incluido en Vacantes PTY Core | Fluent Forms o WPForms añadirían entre 100 y 300 KB de scripts por página. El nuestro pesa menos de 5 KB y guarda en tu propia base de datos. |
| Ventana emergente de salida | Incluida | Popup Maker haría lo mismo con más peso. La nuestra ya respeta "1 vez cada 7 días" y "no mostrar si ya se registró". |
| Botón flotante | HTML y CSS propio | Apunta a un canal, no a un número, así que no necesita plugin. |
| Enlaces de afiliado | Campo "Enlace" en cada recurso de **Capacítate** | Cambias el enlace en un solo lugar y GA4 mide los clics por programa. ThirstyAffiliates o Pretty Links solo harán falta cuando agreguemos Amazon. |
| Antispam | Campo trampa (honeypot) + tiempo mínimo de llenado + límite por IP | Invisible para el usuario, sin reCAPTCHA que ralentice. |
| Correo marketing | **Brevo** (gratis) | 300 correos al día gratis, segmentación por atributos y API sencilla. Mailchimp gratis es más limitado en envíos y automatizaciones. |

**Opcional, recomendado:** el plugin **WP Mail SMTP** configurado con el SMTP de Brevo, para que el correo de bienvenida no caiga en spam.

---

## 2. Configuración paso a paso

### 2.1 Ajustes de Vacantes PTY
**Vacantes → Ajustes**
- **Correo de la marca:** por ejemplo `hola@empleoshoypanama.com`. Créalo gratis en Hostinger → Correos.
- **Canal de WhatsApp:** ya viene puesto.
- **Usuario de Instagram:** `vacantes_pty_`, ya viene puesto.
- **ID de Google Analytics 4:** `G-XXXXXXX`. Déjalo vacío si ya usas Site Kit.

### 2.2 Brevo
1. Crea una cuenta gratis en brevo.com.
2. **Contactos → Configuración → Atributos de contacto** → crea 4 atributos de tipo **Texto**: `WHATSAPP`, `CATEGORIAS`, `PROVINCIA`, `PUNTO`.
3. **Contactos → Listas → Crear lista** "Alertas Vacantes PTY" y anota su **ID** (el número).
4. **Tu nombre (arriba a la derecha) → SMTP y API → Claves API → Generar**.
5. Pega la clave y el ID de la lista en **Vacantes → Ajustes**.
6. **Segmentar:** Contactos → Segmentos → por ejemplo `CATEGORIAS contiene "Call Center"` y `PROVINCIA es "Chiriquí"`.

### 2.3 Yappy (Botón de Pago)
1. En **Yappy Comercial**, registra el dominio `https://empleoshoypanama.com` en la configuración del Botón de Pago.
2. En **Vacantes → Ajustes**, pega el **ID del comercio** y la **clave secreta**. La clave se guarda oculta y nunca aparece en la web.
3. Copia la **URL de notificación (IPN)** que aparece al pie de esa página. Si Yappy Comercial la pide, pégala allí.
4. **Haz un pago real de prueba** con el plan de B/. 4.99 y revisa **Vacantes → Pedidos CV (Yappy)**: el estado debe pasar a **pagado**.
5. Cuando todo funcione, **genera una clave secreta nueva** en Yappy Comercial y actualízala en Ajustes, porque la anterior se compartió por chat.

Si Yappy falla o no está configurado, cada plan muestra **"Pedir por WhatsApp"** hacia el 6226-0829, así que la venta nunca se pierde.

### 2.4 Google Analytics 4
Eventos que envía el sitio:

| Evento | Cuándo | Parámetros |
|---|---|---|
| `generate_lead` | Registro exitoso | `punto` (vacante / popup / alertas), `variante` (a / b) |
| `whatsapp_channel_click` | Clic al canal | `punto` (flotante / vacante / popup / gracias / inicio / listado / footer / blog) |
| `apply_click` | Clic en "Aplicar" | `punto` (url / email / whatsapp / sticky) |
| `cv_plan_click` | Clic en un plan de currículum | `punto` (basico / profesional / premium / capacitate / blog…) |
| `cv_purchase` | Pago Yappy exitoso | `plan`, `value` |
| `affiliate_click` / `resource_click` | Clic en un curso | `programa` (hotmart / coursera / udemy / gratis) |
| `share`, `social_click`, `popup_view` | Compartir, redes, ventana emergente mostrada | `punto` |

En GA4: **Administrar → Eventos → marcar como evento clave** `generate_lead`, `whatsapp_channel_click` y `cv_purchase`. En **Definiciones personalizadas**, crea las dimensiones `punto`, `variante` y `programa`.

También puedes ver cuántos registros trae cada punto y cada variante en **Vacantes → Leads (alertas)**, arriba de la tabla.

---

## 3. Textos de cada punto de captura (A/B)
El sitio asigna a cada visitante la variante A o B al azar y la recuerda. Cada registro guarda su variante.

| Punto | Variante A | Variante B |
|---|---|---|
| Bloque en vacante | 🔔 Recibe vacantes como esta en tu WhatsApp o correo | 🔔 ¿Quieres que te avisemos de más vacantes así? |
| Ventana de salida, título | ¿Te vas sin tu próxima vacante? | Espera, no te pierdas la próxima vacante |
| Ventana de salida, texto | Te avisamos cuando salga una de tu área. Gratis, sin spam. | Déjanos tu correo y WhatsApp y te llegan primero las de tu área. |
| Botón flotante | Síguenos: vacantes diarias en WhatsApp | (fijo) |
| Comunidad (inicio, listado y pie) | Únete a la comunidad Vacantes PTY | (fijo) |

Para cambiar un texto, dime cuál y lo actualizo.

## 4. Dónde está cada cosa
- **Bloque en la vacante:** debajo del botón "Aplicar", plegado para no competir con él. La categoría viene prellenada según la vacante.
- **"Mejora tu perfil para esta vacante":** debajo de la descripción. Muestra de 1 a 3 cursos según la categoría de la vacante. Se configura en cada curso (**Capacítate → Editar → "Mostrar en las vacantes de estas categorías"**), no vacante por vacante.
- **Compartir:** en cada vacante y en cada artículo del blog: WhatsApp, Facebook, X, LinkedIn, copiar enlace, y **"Instagram y más"**, que en el celular abre el menú de compartir del teléfono.
- **Redes con íconos:** Instagram, YouTube y TikTok en el pie de página y en la página de gracias. Se cambian en **Apariencia → Personalizar → Vacantes PTY**.
- **Leads:** **Vacantes → Leads (alertas)**, con filtro por área y el botón **Exportar a Excel (CSV)**.
- **Pedidos de CV:** **Vacantes → Pedidos CV (Yappy)**.
- **Capacítate:** menú **Capacítate** en el admin. Trae 13 cursos iniciales con enlaces oficiales. Cuando tengas tus enlaces de afiliado de Hotmart, Coursera o Udemy, **reemplaza el enlace** en cada ficha.

## 5. Correo de bienvenida (automático)
**Asunto:** `{Nombre}, ya estás en las alertas de Vacantes PTY 🇵🇦`

> ¡Hola, {Nombre}! 👋
> Ya estás registrado(a) para recibir vacantes de **{áreas}**. Te escribiremos cuando salgan oportunidades de tu área.
> **¿Quieres enterarte primero?** En nuestro canal de WhatsApp publicamos vacantes nuevas todos los días. Síguelo y activa la campanita 🔔: **[Seguir el canal de WhatsApp]**
> **¿Tu currículum está listo para pasar los filtros de las empresas?** Te lo hacemos profesional desde B/. 4.99 → **[Ver planes de currículum]**
> Recuerda: en Vacantes PTY nunca te cobraremos por aplicar a una vacante.
> ¡Éxitos en tu búsqueda! Equipo Vacantes PTY
> *Darme de baja · Política de Privacidad*

## 6. Plan de los primeros 10 artículos del blog
Todos terminan con los botones para compartir, el canal de WhatsApp y los planes de CV (ya viene en la plantilla).

| # | Título SEO | Intención | Monetización |
|---|---|---|---|
| 1 | Cursos gratis con certificado para conseguir trabajo en Panamá (2026) | Informativa | INADEH, Carlos Slim, HubSpot, Coursera |
| 2 | Cómo hacer un currículum que pase los filtros ATS de las empresas | Informativa | **Planes de CV** |
| 3 | Modelo de currículum para Panamá: ejemplos por profesión | Informativa / plantilla | **Planes de CV** |
| 4 | 15 preguntas de entrevista de trabajo y cómo responderlas | Informativa | Planes de CV (Marca Personal) |
| 5 | Trabajos remotos desde Panamá: qué necesitas para empezar | Informativa | Certificados de Google, inglés EF SET |
| 6 | Cómo conseguir trabajo en call center en Panamá sin experiencia | Transaccional | HubSpot, curso de atención al cliente, EF SET |
| 7 | Excel para entrevistas: lo que te van a preguntar | Informativa | Excel (Udemy y GCFGlobal) |
| 8 | Cuánto gana un [puesto] en Panamá: salarios 2026 | Informativa | Calculadora de salario + planes de CV |
| 9 | Certificados Profesionales de Google: ¿valen la pena en Panamá? | Comercial | Coursera (afiliado) |
| 10 | Cómo calcular tu décimo tercer mes en Panamá | Informativa | Calculadora + canal de WhatsApp |

---

## 7. Checklist antes de lanzar
- [ ] Respaldo hecho en Hostinger.
- [ ] Plugin y tema actualizados; enlaces permanentes guardados; caché purgada.
- [ ] Las 6 páginas nuevas abren (Alertas, Gracias, Currículum, Capacítate, Privacidad, Afiliados).
- [ ] **Vacantes → Ajustes:** correo de la marca, Brevo y Yappy configurados.
- [ ] Registro de prueba desde una vacante → lleva a /gracias/ → llega el correo de bienvenida (revisa spam) → aparece en **Leads** con punto "vacante" y ✔ en Brevo.
- [ ] El enlace "Darme de baja" del correo funciona y el lead pasa a "Dados de baja".
- [ ] Ventana de salida: en computadora sale al llevar el mouse arriba; en celular a los 25 segundos o al bajar; no vuelve a salir después de registrarte (pruébalo en una ventana de incógnito).
- [ ] Botón flotante verde → abre el canal con `utm_content=flotante`; en celular no tapa el botón "Aplicar".
- [ ] Compartir por WhatsApp y "Copiar enlace" funcionan; en celular aparece "Instagram y más".
- [ ] Íconos de Instagram, YouTube y TikTok abren los perfiles correctos.
- [ ] Pago Yappy de prueba (B/. 4.99): se aprueba en la app, el pedido queda **pagado** y te llega el correo de aviso.
- [ ] GA4 → Tiempo real: ves `generate_lead` y `whatsapp_channel_click` al probar.
- [ ] Prueba de resultados enriquecidos de Google en una vacante: https://search.google.com/test/rich-results
- [ ] Revisar la Política de Privacidad y ajustar lo que haga falta.
- [ ] Borrar las vacantes de EJEMPLO.

## Pendiente para la siguiente fase
- Amazon Associates (equipo para trabajo remoto y call center, y libros) con su aviso obligatorio.
- Envío automático de alertas por categoría. Por ahora se envían desde Brevo con los segmentos; se puede automatizar con n8n.
- Ebook (cuando tenga nombre y precio).
