describe('Prueba de Gestión de Usuarios (Admin)', () => {
  beforeEach(() => {
    // Login como administrador
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/login.php');

    cy.get('input[name="usuario"]').type('JeremyR');
    cy.get('input[name="password"]').type('admin123');
    cy.get('form').submit();

    cy.url().should('include', '/dashboard.php');
  });

  it('Registra un nuevo usuario con rol de "usuario"', () => {
    // Navegar a la página de gestión de usuarios
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/modules/usuarios/');

    // Generar un nombre de usuario único para evitar conflictos
    const nuevoUsuario = `testuser_${Date.now()}`;
    const nuevaContrasena = 'password123';

    // Rellenar el formulario de registro de usuario
    cy.get('input[name="usuario"]').type(nuevoUsuario);
    cy.get('input[name="password"]').type(nuevaContrasena);
    cy.get('select[name="rol"]').select('usuario'); // Selecciona el rol 'usuario'

    // Clic en el botón para registrar
    cy.get('button[name="registrar"]').click();

    // Validar el mensaje de éxito
    cy.contains('Usuario registrado correctamente.').should('be.visible');

    // Opcionalmente, validar que el nuevo usuario aparece en la tabla
    cy.get('table').contains('td', nuevoUsuario).should('be.visible');
    cy.get('table').contains('tr', nuevoUsuario).within(() => {
      cy.get('td').eq(2).should('contain', 'usuario');
    });
  });

  it('Registra un nuevo usuario con rol de "administrador"', () => {
    // Navegar a la página de gestión de usuarios
    cy.visit('http://localhost/Proyectos/XelaExpress/XelaExpress/modules/usuarios/');

    // Generar un nombre de usuario único
    const nuevoAdmin = `testadmin_${Date.now()}`;
    const nuevaContrasenaAdmin = 'adminpass456';

    // Rellenar el formulario de registro de usuario
    cy.get('input[name="usuario"]').type(nuevoAdmin);
    cy.get('input[name="password"]').type(nuevaContrasenaAdmin);
    cy.get('select[name="rol"]').select('admin'); // Selecciona el rol 'admin'

    // Clic en el botón para registrar
    cy.get('button[name="registrar"]').click();

    // Validar el mensaje de éxito
    cy.contains('Usuario registrado correctamente.').should('be.visible');

    // Validar que el nuevo administrador aparece en la tabla con el rol correcto
    cy.get('table').contains('td', nuevoAdmin).should('be.visible');
    cy.get('table').contains('tr', nuevoAdmin).within(() => {
      cy.get('td').eq(2).should('contain', 'admin');
    });
  });
});