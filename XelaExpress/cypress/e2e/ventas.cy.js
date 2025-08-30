describe('Prueba de Ventas', () => {
  beforeEach(() => {
    // Login
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/login.php');

    cy.get('input[name="usuario"]').type('JeremyR');  
    cy.get('input[name="password"]').type('admin123');
    cy.get('form').submit();

    cy.url().should('include', '/dashboard.php');
  });

  it('Registra una nueva venta con primer cliente y producto', () => {
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/modules/ventas/'); 
    // ajusta la ruta a la correcta en tu proyecto

    // Seleccionar primer cliente válido (ignora "Selecciona un cliente")
    cy.get('#cliente_id option').not('[value=""]').first().then(option => {
      cy.get('#cliente_id').select(option.val());
    });

    // Seleccionar primer producto válido
    cy.get('select[name="items[0][producto_id]"] option').not('[value=""]').first().then(option => {
      cy.get('select[name="items[0][producto_id]"]').select(option.val());
    });

    // Ingresar cantidad
    cy.get('input[name="items[0][cantidad]"]').clear().type('2');

    // Registrar venta
    cy.get('button[name="registrar_venta"]').click();

    // Validar mensaje de éxito
    cy.contains('Venta registrada correctamente.');
  });
});
