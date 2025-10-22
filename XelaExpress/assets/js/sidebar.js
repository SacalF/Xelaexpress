/**
 * Sidebar JavaScript para XelaExpress
 * Maneja la funcionalidad del menú lateral
 */

class SidebarManager {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.sidebarToggleMobile = document.getElementById('sidebarToggleMobile');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.mainContent = document.getElementById('mainContent');
        this.pageTitle = document.getElementById('pageTitle');
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setActiveMenuItem();
        this.updatePageTitle();
        this.handleResize();
    }
    
    bindEvents() {
        // Toggle sidebar en móviles
        if (this.sidebarToggleMobile) {
            this.sidebarToggleMobile.addEventListener('click', () => {
                this.toggleSidebar();
            });
        }
        
        // Cerrar sidebar en móviles
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', () => {
                this.closeSidebar();
            });
        }
        
        // Overlay click para cerrar sidebar
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => {
                this.closeSidebar();
            });
        }
        
        // Manejar resize de ventana
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        // Prevenir scroll del body cuando sidebar está abierto en móviles
        this.preventBodyScroll();
        
        // Agregar efectos de carga a los enlaces
        this.addLoadingEffects();
    }
    
    toggleSidebar() {
        if (window.innerWidth <= 991.98) {
            this.sidebar.classList.toggle('show');
            this.sidebarOverlay.classList.toggle('show');
            this.toggleBodyScroll();
        }
    }
    
    closeSidebar() {
        this.sidebar.classList.remove('show');
        this.sidebarOverlay.classList.remove('show');
        this.enableBodyScroll();
    }
    
    openSidebar() {
        if (window.innerWidth <= 991.98) {
            this.sidebar.classList.add('show');
            this.sidebarOverlay.classList.add('show');
            this.disableBodyScroll();
        }
    }
    
    handleResize() {
        if (window.innerWidth > 991.98) {
            this.closeSidebar();
            this.enableBodyScroll();
        }
    }
    
    preventBodyScroll() {
        // Prevenir scroll del body cuando sidebar está abierto
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    if (this.sidebar.classList.contains('show')) {
                        this.disableBodyScroll();
                    } else {
                        this.enableBodyScroll();
                    }
                }
            });
        });
        
        observer.observe(this.sidebar, { attributes: true });
    }
    
    disableBodyScroll() {
        document.body.style.overflow = 'hidden';
    }
    
    enableBodyScroll() {
        document.body.style.overflow = '';
    }
    
    toggleBodyScroll() {
        if (this.sidebar.classList.contains('show')) {
            this.disableBodyScroll();
        } else {
            this.enableBodyScroll();
        }
    }
    
    setActiveMenuItem() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            
            if (href && currentPath.includes(href.replace(/\/$/, ''))) {
                link.classList.add('active');
            }
        });
    }
    
    updatePageTitle() {
        // El título ya se establece desde PHP, no necesitamos cambiarlo
        // Esta función se mantiene para compatibilidad futura
        return;
    }
    
    addLoadingEffects() {
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Solo agregar efecto de carga si no es el enlace activo
                if (!link.classList.contains('active')) {
                    link.classList.add('loading');
                    
                    // Remover efecto después de un tiempo
                    setTimeout(() => {
                        link.classList.remove('loading');
                    }, 1000);
                }
            });
        });
    }
    
    // Método para agregar notificaciones a elementos del menú
    addNotification(menuItem, count) {
        const link = document.querySelector(`.sidebar-nav .nav-link[href*="${menuItem}"]`);
        if (link) {
            // Remover notificación existente
            const existingBadge = link.querySelector('.notification-badge');
            if (existingBadge) {
                existingBadge.remove();
            }
            
            // Agregar nueva notificación
            if (count > 0) {
                const badge = document.createElement('span');
                badge.className = 'notification-badge';
                badge.textContent = count > 99 ? '99+' : count.toString();
                link.appendChild(badge);
            }
        }
    }
    
    // Método para remover notificaciones
    removeNotification(menuItem) {
        const link = document.querySelector(`.sidebar-nav .nav-link[href*="${menuItem}"]`);
        if (link) {
            const badge = link.querySelector('.notification-badge');
            if (badge) {
                badge.remove();
            }
        }
    }
    
    // Método para actualizar información del usuario
    updateUserInfo(userName, userRole) {
        const userNameElement = document.querySelector('.user-name');
        const userRoleElement = document.querySelector('.user-role');
        
        if (userNameElement) userNameElement.textContent = userName;
        if (userRoleElement) userRoleElement.textContent = userRole;
    }
    
    // Método para colapsar/expandir sidebar en desktop
    toggleCollapse() {
        if (window.innerWidth > 991.98) {
            this.sidebar.classList.toggle('collapsed');
            this.mainContent.classList.toggle('sidebar-collapsed');
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.sidebarManager = new SidebarManager();
    
    // Agregar funcionalidad adicional
    initializeSidebarFeatures();
});

function initializeSidebarFeatures() {
    // Agregar tooltips a elementos del sidebar
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    navLinks.forEach(link => {
        const span = link.querySelector('span');
        if (span) {
            link.setAttribute('title', span.textContent);
        }
    });
    
    // Manejar teclas de acceso rápido
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + B para toggle sidebar
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            window.sidebarManager.toggleSidebar();
        }
        
        // Escape para cerrar sidebar en móviles
        if (e.key === 'Escape' && window.innerWidth <= 991.98) {
            window.sidebarManager.closeSidebar();
        }
    });
    
    // Agregar animaciones suaves a los enlaces
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        link.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateX(0)';
            }
        });
    });
    
    // Lazy loading para iconos
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const icon = entry.target.querySelector('i');
                if (icon) {
                    icon.style.opacity = '1';
                    icon.style.transform = 'scale(1)';
                }
            }
        });
    });
    
    navLinks.forEach(link => {
        observer.observe(link);
    });
}

// Funciones utilitarias globales
window.SidebarUtils = {
    addNotification: (menuItem, count) => {
        if (window.sidebarManager) {
            window.sidebarManager.addNotification(menuItem, count);
        }
    },
    
    removeNotification: (menuItem) => {
        if (window.sidebarManager) {
            window.sidebarManager.removeNotification(menuItem);
        }
    },
    
    updateUserInfo: (userName, userRole) => {
        if (window.sidebarManager) {
            window.sidebarManager.updateUserInfo(userName, userRole);
        }
    },
    
    toggleSidebar: () => {
        if (window.sidebarManager) {
            window.sidebarManager.toggleSidebar();
        }
    }
};
