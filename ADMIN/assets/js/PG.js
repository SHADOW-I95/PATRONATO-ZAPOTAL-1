  document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function (e) {
          e.preventDefault();
          const page = this.dataset.page;
          if (!page) return; // secciones que aún no tienen ruta (Pagos, agua, etc.)
          cargarPagina(page);
        });
      });

      function cargarPagina(pageUrl) {
        fetch(pageUrl)
          .then(res => res.text())
          .then(html => {
            document.getElementById('contenido').innerHTML = html;
          })
          .catch(err => console.error('Error cargando sección:', err));
      }

      // Cargar el dashboard por defecto al abrir la página
      cargarPagina('./dashboard/dasboard.php')