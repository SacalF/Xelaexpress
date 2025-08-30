describe('Prueba de Inventario de Productos', () => {
  beforeEach(() => {
    // Login
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/login.php');

    cy.get('input[name="usuario"]').type('JeremyR');
    cy.get('input[name="password"]').type('admin123');
    cy.get('form').submit();

    cy.url().should('include', '/dashboard.php');
  });

  it('Registra un nuevo producto en el inventario', () => {
    // Navegar a la página de gestión de productos
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/modules/productos/');

    // Rellenar el formulario de registro de producto
    cy.get('input[name="nombre"]').type('Shampoo');
    cy.get('input[name="descripcion"]').type('Botella de 1 Litro para damas');
    cy.get('input[name="precio"]').type('30.00');
    cy.get('input[name="stock"]').type('100');

    // Clic en el botón para registrar
    // El selector 'button[name="registrar"]' es específico para tu formulario de registro
    cy.get('button[name="registrar"]').click();

    // Validar el mensaje de éxito
    cy.contains('Producto registrado correctamente.').should('be.visible');

    // Opcionalmente, puedes validar que el producto aparece en la tabla
    cy.get('table').contains('td', 'Nuevo Producto de Prueba').should('be.visible');
  });
});