document.querySelectorAll('.menu-item').forEach(item => {
      item.addEventListener('click', function (e) {
        e.preventDefault();
        cargarPagina(this.dataset.page);
      });
    });

    function cargarPagina(page) {
      fetch(`secciones/${page}.php`)
        .then(res => res.text())
        .then(html => {
          document.getElementById('contenido').innerHTML = html;
        });
    }

    cargarPagina('dashboard'); // se carga al abrir