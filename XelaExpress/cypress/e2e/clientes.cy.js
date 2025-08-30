describe('Prueba de Gestión de Clientes', () => {
  beforeEach(() => {
    // Login
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/login.php');

    cy.get('input[name="usuario"]').type('JeremyR');
    cy.get('input[name="password"]').type('admin123');
    cy.get('form').submit();

    cy.url().should('include', '/dashboard.php');
  });

  it('Registra un nuevo cliente con todos los campos', () => {
    // Navegar a la página de gestión de clientes
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/modules/clientes/');

    // Rellenar el formulario de registro de cliente
    const nombre = 'Roberto';
    const apellido = 'Gomez';
    const telefono = '555-1234';
    const correo = `roberto.gomez_${Date.now()}@example.com`;
    const direccion = '123 Calle Falsa';

    cy.get('input[name="nombre"]').type(nombre);
    cy.get('input[name="apellido"]').type(apellido);
    cy.get('input[name="telefono"]').type(telefono);
    cy.get('input[name="correo"]').type(correo);
    cy.get('input[name="direccion"]').type(direccion);

    // Clic en el botón para registrar
    cy.get('button[name="registrar"]').click();

    // Validar el mensaje de éxito
    cy.contains('Cliente registrado correctamente.').should('be.visible');

    // Opcionalmente, validar que el nuevo cliente aparece en la tabla
    cy.get('table').contains('td', nombre).should('be.visible');
    cy.get('table').contains('td', apellido).should('be.visible');
  });
});