# Vacantes PTY: guía de instalación y uso

Sitio: **empleoshoypanama.com** · Marca: **Vacantes PTY**

## Qué incluye

| Archivo | Qué es |
|---|---|
| `dist/vacantespty-core.zip` | **Plugin.** Vacantes, categorías, provincias, banners, calculadora y formato para Google for Jobs. |
| `dist/vacantespty-tema.zip` | **Tema.** Diseño con los colores de la marca (azul marino `#0A2463`, azul `#0B6BF2`, amarillo `#F7B928`). |

Instala siempre **primero el plugin** y después el tema.

---

## Paso 1: Subir el plugin
1. WordPress → **Plugins → Añadir nuevo plugin → Subir plugin**.
2. **Seleccionar archivo** → `vacantespty-core.zip` → **Instalar ahora**.
3. **Activar plugin**.

Al activarlo se crean automáticamente:
- 18 categorías de empleo y las 10 provincias.
- Las páginas **Calculadora de salario neto en Panamá** y **Publicar vacante**.
- 6 **vacantes de EJEMPLO** para ver el diseño. **Bórralas antes del lanzamiento.**
- Las páginas **Inicio** y **Blog**, si el sitio aún no tenía una portada fija.

## Paso 2: Subir el tema
1. **Apariencia → Temas → Añadir tema → Subir tema**.
2. `vacantespty-tema.zip` → **Instalar ahora** → **Activar**.

## Paso 3: Ajustes rápidos
1. **Ajustes → Enlaces permanentes** → presiona **Guardar cambios**, aunque no cambies nada. Así se activan las direcciones `/empleos/`, `/empleos-de/…` y `/empleos-en/…`.
2. **Ajustes → Lectura** → comprueba que la portada sea **Inicio** y la página de entradas sea **Blog**.
3. **Apariencia → Personalizar → Identidad del sitio** → sube el **logo horizontal** (PNG transparente).
4. **Apariencia → Personalizar → Vacantes PTY**:
   - WhatsApp para empresas: el número que recibe a las empresas que quieren publicar.
   - Enlace del canal de alertas (WhatsApp o Telegram).
   - Facebook, Instagram, TikTok y LinkedIn.
   - Título y subtítulo de la portada.
5. **Apariencia → Menús** (opcional): crea el menú y asígnalo a "Menú principal". Si no lo haces, se muestra uno automático.

---

## Publicar una vacante
**Vacantes → Añadir vacante**
- **Título**: el nombre del puesto (por ejemplo, "Cajero(a) – Supermercado en David").
- **Contenido**: funciones, requisitos y beneficios. Usa subtítulos (H2) y listas.
- **Datos de la vacante**: empresa, salario, provincia, modalidad, WhatsApp, fecha de cierre, etc.
- **Categorías** y **Provincias**: en la columna derecha.
- **Logo de la empresa**: en "Subir logo de la empresa".
- Marca **Empresa verificada** solo si comprobaste que la empresa existe.
- Marca **Vacante destacada** para las vacantes pagadas: salen primero en la portada, resaltadas en amarillo.

Cuando pasa la fecha de cierre, la vacante se oculta sola de los listados y de Google. El enlace sigue funcionando y muestra "Vacante cerrada".

## Vender y colocar un banner
**Banners → Añadir banner**
1. Título: el nombre de la marca.
2. **Imagen del banner** (columna derecha).
3. **Enlace de destino**: la web o el WhatsApp de la marca.
4. **Dónde se muestra**: marca las zonas.
5. **Mostrar desde / hasta**: las fechas del contrato. El banner se apaga solo al terminar.

| Zona | Tamaño recomendado |
|---|---|
| Inicio – debajo del buscador | 970×250 o 728×90 |
| Inicio – entre secciones | 970×250 o 728×90 |
| Barra lateral | 300×250 o 300×600 |
| Listado de vacantes | 728×90 |
| Detalle de vacante | 728×90 |
| Blog – dentro del artículo | 728×90 o 300×250 |

Si varias marcas comparten una zona, se van rotando. La columna **Clics** del listado de banners sirve para enviarle el reporte a cada marca.

Mientras estás conectado como administrador, las zonas vacías muestran un recuadro "Espacio publicitario disponible". Los visitantes no lo ven.

## Blog
**Entradas → Añadir entrada**. Ideas que atraen tráfico desde Google:
- "Cómo hacer una hoja de vida en Panamá"
- "Cuánto gana un [puesto] en Panamá"
- "Empleos sin experiencia en Panamá"
- "Cómo calcular el décimo tercer mes"
- "Preguntas de entrevista de trabajo"

## Atajos para usar en cualquier página (shortcodes)
| Shortcode | Qué muestra |
|---|---|
| `[vpty_buscador]` | El buscador de empleos |
| `[vpty_vacantes cantidad="6" categoria="tecnologia-e-informatica" provincia="chiriqui"]` | Un listado de vacantes |
| `[vpty_vacantes destacadas="1"]` | Solo las vacantes destacadas |
| `[vpty_calculadora]` | La calculadora de salario neto y del décimo |
| `[vpty_banner zona="sidebar"]` | Un banner de una zona |
| `[vpty_publicar]` | El botón "Publicar vacante por WhatsApp" |

## Antes del lanzamiento
- [ ] Borrar las 6 vacantes de EJEMPLO y la entrada "Hola mundo".
- [ ] Publicar al menos 20 vacantes reales y 3 artículos.
- [ ] **Ajustes → Lectura** → desmarcar "Disuadir a los motores de búsqueda".
- [ ] Instalar **Rank Math SEO** y enviar el sitemap a Google Search Console.
- [ ] Probar la vacante en la Prueba de resultados enriquecidos de Google: https://search.google.com/test/rich-results
