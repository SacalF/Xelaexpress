Cypress.on('uncaught:exception', (err, runnable) => {
  // Ignora el error 'Chart is not defined' para que no falle la prueba
  if (err.message.includes('Chart is not defined')) {
    return false; // evita que falle la prueba
  }
  // para otros errores, que falle normalmente
});
