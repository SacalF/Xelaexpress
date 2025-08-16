describe('Prueba de Login', () => {
  it('Inicia sesión y redirige al dashboard', () => {
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/login.php');

    // Cambia 'usuario' y 'password' por los name reales de tus inputs
    cy.get('input[name="usuario"]').type('JeremyR');  
    cy.get('input[name="password"]').type('admin123');

    cy.get('form').submit();

    // Validar que después del login la URL es la del dashboard
    cy.url().should('eq', 'http://localhost/Proyectos/XelaExpress/XelaExpress/dashboard.php');
  });
});
