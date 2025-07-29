<body>
  <h1>prototipo carrito</h1>

  <div class="productos" id="contenedor-productos">
    <!-- Aquí se insertan dinámicamente -->
  </div>

  <h2>Carrito</h2>
  <ul id="carrito"></ul>
  <p>Total: $<span id="total">0</span></p>

  <script>
    let carrito = [];
    let total = 0;

    function agregarAlCarrito(nombre, precio) {
      carrito.push({ nombre, precio });
      total += precio;
      actualizarCarrito();
    }

    function actualizarCarrito() {
      const lista = document.getElementById('carrito');
      const totalElemento = document.getElementById('total');

      lista.innerHTML = '';
      carrito.forEach(item => {
        const li = document.createElement('li');
        li.textContent = `${item.nombre} - $${item.precio}`;
        lista.appendChild(li);
      });

      totalElemento.textContent = total.toFixed(2);
    }

    // 🔄 Cargar productos desde PHP
    window.onload = function () {
      fetch('../modelo/cargar_productos.php')
        .then(response => response.json())
        .then(productos => {
          const contenedor = document.getElementById('contenedor-productos');
          productos.forEach(p => {
            const div = document.createElement('div');
            div.classList.add('producto');
            div.innerHTML = `
              <h3>${p.nombre}</h3>
              <p>$${p.precio}</p>
              <button onclick="agregarAlCarrito('${p.nombre}', ${p.precio})">Agregar</button>
            `;
            contenedor.appendChild(div);
          });
        })
        .catch(err => {
          console.error("Error cargando productos:", err);
        });
    };
  </script>
</body>
