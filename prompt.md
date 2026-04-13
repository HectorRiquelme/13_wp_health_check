# Prompt 1 - Diagnostico y fix de carrusel roto en WordPress

**Stack:** PHP, WordPress, JavaScript, CSS, jQuery

---

Crea un plugin de WordPress llamado "WP Health Check" que diagnostica problemas comunes automaticamente. PHP + JavaScript. El plugin: 1) detecta conflictos de jQuery (multiples versiones cargadas), 2) detecta plugins que cargan scripts bloqueantes en el header, 3) verifica que las imagenes del slider tengan alt text y esten optimizadas (tamano < 500KB), 4) verifica compatibilidad responsive (viewport meta tag presente), 5) genera un reporte en el admin de WordPress con semaforo (rojo/amarillo/verde) por cada check, 6) boton "Aplicar fix automatico" para los problemas que tienen solucion segura. Seguir estandares de plugin WordPress: hooks, nonces, sanitization, i18n. Testeable en cualquier instalacion WordPress real.

---

**IMPORTANTE:** No incluyas referencias a Claude, Claude Code, Anthropic ni ningun asistente IA en commits, README, package.json, comentarios de codigo ni en ningun archivo del proyecto. El unico autor debe ser "Hector Riquelme" con el email y usuario de GitHub "HectorRiquelme".
