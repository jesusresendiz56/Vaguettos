function agregarAlCarrito(nombre, precio) {
  const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
  carrito.push({ nombre, precio });
  localStorage.setItem('carrito', JSON.stringify(carrito));
  alert(`Agregado: ${nombre}`);
}

function mostrarCarrito() {
  const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
  const contenedor = document.getElementById('carrito-contenido');
  const totalElement = document.getElementById('total');
  let total = 0;

  if (carrito.length === 0) {
    contenedor.textContent = 'El carrito está vacío.';
    totalElement.textContent = 'Total: $0';
    return;
  }

  carrito.forEach(p => {
    const item = document.createElement('div');
    item.textContent = `${p.nombre} - $${p.precio}`;
    contenedor.appendChild(item);
    total += p.precio;
  });

  totalElement.textContent = `Total: $${total}`;
}

// Mostrar carrito si estamos en carrito.html
if (window.location.pathname.includes('carrito.html')) {
  mostrarCarrito();
}
    