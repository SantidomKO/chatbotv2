# Auditoría CSS legacy

Este documento sirve como registro de limpieza para no perder el estado del diseño actual antes de seguir con más cambios.

## Estado de revisión

- [x] :root del diseño anterior removido de los archivos activos
- [x] bloque legacy de "Sifón de Soda" eliminado del CSS activo
- [x] clases de layout viejo no usadas quitadas (`app-layout`, `sidebar-image-container`, `info-btn`, `popover-box`)
- [x] estilos duplicados de login/formularios revisados y dejados solo en lo que usa la app actual
- [x] archivo `main.css` identificado como legado y eliminado de la carga activa
- [x] estilos de glitch actuales conservados en `style.css`
- [x] `form_style.css` revisado y dejado con lo que usan las vistas actuales
- [x] `sidebar.css` revisado y mantenido solo para la navegación vigente
- [x] `style.css` limpiado para no mezclar estilos legacy con glitch
- [x] variables globales del sidebar movidas al contexto local del componente para evitar la raíz legacy

## Principio aplicado

Se mantiene solo lo que la estructura actual usa en las vistas y se elimina lo que quedó como remanente de un diseño previo.

## Archivos relevantes

- assets/css/style.css
- assets/css/form_style.css
- assets/css/sidebar.css
- view/layout/header.php
- view/layout/sidebar.php
