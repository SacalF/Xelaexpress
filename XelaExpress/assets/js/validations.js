/**
 * Sistema de validaciones comunes para XelaExpress
 * Incluye validaciones en tiempo real y utilidades para formularios
 */

// Configuración de validaciones
const ValidationConfig = {
    patterns: {
        nombre: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/,
        telefono: /^[0-9+\-\s()]+$/,
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        usuario: /^[a-zA-Z0-9_]+$/,
        precio: /^\d+(\.\d{1,2})?$/,
        cantidad: /^\d+$/
    },
    limits: {
        nombre: { min: 2, max: 50 },
        apellido: { min: 2, max: 50 },
        telefono: { min: 8, max: 20 },
        email: { min: 5, max: 100 },
        usuario: { min: 3, max: 50 },
        password: { min: 6, max: 100 },
        direccion: { min: 5, max: 200 },
        descripcion: { min: 5, max: 500 },
        precio: { min: 0.01, max: 999999.99 },
        cantidad: { min: 1, max: 9999 }
    }
};

/**
 * Inicializa las validaciones de Bootstrap
 */
function initBootstrapValidation() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
}

/**
 * Valida un campo de texto con patrón y límites
 * @param {HTMLInputElement} input - Campo a validar
 * @param {RegExp} pattern - Patrón de validación
 * @param {Object} limits - Límites {min, max}
 * @param {string} customMessage - Mensaje personalizado
 */
function validateTextField(input, pattern, limits, customMessage = null) {
    const value = input.value.trim();
    const fieldName = input.name || input.id || 'campo';
    
    if (value.length === 0) {
        input.classList.remove('is-valid', 'is-invalid');
        return true;
    }
    
    let isValid = true;
    let message = '';
    
    // Validar longitud mínima
    if (value.length < limits.min) {
        isValid = false;
        message = customMessage || `${fieldName} debe tener al menos ${limits.min} caracteres`;
    }
    // Validar longitud máxima
    else if (value.length > limits.max) {
        isValid = false;
        message = customMessage || `${fieldName} debe tener máximo ${limits.max} caracteres`;
    }
    // Validar patrón
    else if (!pattern.test(value)) {
        isValid = false;
        message = customMessage || `${fieldName} contiene caracteres no válidos`;
    }
    
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        input.setCustomValidity('');
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        input.setCustomValidity(message);
    }
    
    return isValid;
}

/**
 * Valida un campo numérico
 * @param {HTMLInputElement} input - Campo a validar
 * @param {Object} limits - Límites {min, max}
 * @param {string} fieldName - Nombre del campo para mensajes
 */
function validateNumberField(input, limits, fieldName = 'campo') {
    const value = parseFloat(input.value) || 0;
    
    if (input.value.trim() === '') {
        input.classList.remove('is-valid', 'is-invalid');
        return true;
    }
    
    let isValid = true;
    let message = '';
    
    if (value < limits.min) {
        isValid = false;
        message = `${fieldName} debe ser mayor o igual a ${limits.min}`;
    } else if (value > limits.max) {
        isValid = false;
        message = `${fieldName} debe ser menor o igual a ${limits.max}`;
    }
    
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        input.setCustomValidity('');
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        input.setCustomValidity(message);
    }
    
    return isValid;
}

/**
 * Valida un campo de email
 * @param {HTMLInputElement} input - Campo de email
 */
function validateEmailField(input) {
    const value = input.value.trim();
    
    if (value.length === 0) {
        input.classList.remove('is-valid', 'is-invalid');
        return true;
    }
    
    const isValid = ValidationConfig.patterns.email.test(value) && 
                   value.length >= ValidationConfig.limits.email.min && 
                   value.length <= ValidationConfig.limits.email.max;
    
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        input.setCustomValidity('');
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        input.setCustomValidity('Ingrese un correo electrónico válido');
    }
    
    return isValid;
}

/**
 * Valida un campo de teléfono
 * @param {HTMLInputElement} input - Campo de teléfono
 */
function validatePhoneField(input) {
    const value = input.value.trim();
    
    if (value.length === 0) {
        input.classList.remove('is-valid', 'is-invalid');
        return true;
    }
    
    const isValid = ValidationConfig.patterns.telefono.test(value) && 
                   value.length >= ValidationConfig.limits.telefono.min && 
                   value.length <= ValidationConfig.limits.telefono.max;
    
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        input.setCustomValidity('');
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        input.setCustomValidity('Ingrese un teléfono válido');
    }
    
    return isValid;
}

/**
 * Configura validaciones automáticas para campos comunes
 */
function setupCommonValidations() {
    // Validación para nombres y apellidos
    document.querySelectorAll('input[name="nombre"], input[name="apellido"]').forEach(input => {
        input.addEventListener('input', function() {
            validateTextField(this, ValidationConfig.patterns.nombre, ValidationConfig.limits.nombre);
        });
    });

    // Validación para teléfonos
    document.querySelectorAll('input[name="telefono"], input[type="tel"]').forEach(input => {
        input.addEventListener('input', function() {
            validatePhoneField(this);
        });
    });

    // Validación para emails
    document.querySelectorAll('input[name="correo"], input[type="email"]').forEach(input => {
        input.addEventListener('input', function() {
            validateEmailField(this);
        });
    });

    // Validación para usuarios
    document.querySelectorAll('input[name="usuario"]').forEach(input => {
        input.addEventListener('input', function() {
            validateTextField(this, ValidationConfig.patterns.usuario, ValidationConfig.limits.usuario);
        });
    });

    // Validación para contraseñas
    document.querySelectorAll('input[name="password"], input[type="password"]').forEach(input => {
        input.addEventListener('input', function() {
            const value = this.value;
            if (value.length >= ValidationConfig.limits.password.min && 
                value.length <= ValidationConfig.limits.password.max) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                this.setCustomValidity('');
            } else if (value.length > 0) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                this.setCustomValidity(`La contraseña debe tener entre ${ValidationConfig.limits.password.min} y ${ValidationConfig.limits.password.max} caracteres`);
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    });

    // Validación para precios
    document.querySelectorAll('input[name="precio"]').forEach(input => {
        input.addEventListener('input', function() {
            validateNumberField(this, ValidationConfig.limits.precio, 'El precio');
        });
    });

    // Validación para cantidades
    document.querySelectorAll('input[name="cantidad"], input[name="stock"]').forEach(input => {
        input.addEventListener('input', function() {
            validateNumberField(this, ValidationConfig.limits.cantidad, 'La cantidad');
        });
    });
}

/**
 * Auto-dismiss alerts después de 5 segundos
 */
function autoDismissAlerts() {
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (alert.classList.contains('alert-dismissible')) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
}

/**
 * Formatea números como moneda
 * @param {number} amount - Cantidad a formatear
 * @param {string} currency - Moneda (default: 'Q')
 */
function formatCurrency(amount, currency = 'Q') {
    return `${currency} ${parseFloat(amount).toFixed(2)}`;
}

/**
 * Valida que al menos un checkbox esté seleccionado
 * @param {string} checkboxName - Nombre de los checkboxes
 * @param {string} errorMessage - Mensaje de error
 */
function validateCheckboxGroup(checkboxName, errorMessage = 'Seleccione al menos una opción') {
    const checkboxes = document.querySelectorAll(`input[name="${checkboxName}"]`);
    const isChecked = Array.from(checkboxes).some(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        if (isChecked) {
            cb.setCustomValidity('');
            cb.classList.remove('is-invalid');
        } else {
            cb.setCustomValidity(errorMessage);
            cb.classList.add('is-invalid');
        }
    });
    
    return isChecked;
}

/**
 * Inicializa todas las validaciones cuando el DOM está listo
 */
document.addEventListener('DOMContentLoaded', function() {
    initBootstrapValidation();
    setupCommonValidations();
    autoDismissAlerts();
});

// Exportar funciones para uso global
window.ValidationUtils = {
    validateTextField,
    validateNumberField,
    validateEmailField,
    validatePhoneField,
    validateCheckboxGroup,
    formatCurrency,
    ValidationConfig
};
