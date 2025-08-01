// JavaScript para mejorar la interactividad del login de Filament 3
document.addEventListener('DOMContentLoaded', function() {
    
    // Función para agregar efectos de partículas
    function createParticles() {
        const particlesContainer = document.createElement('div');
        particlesContainer.className = 'particles-container';
        particlesContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        `;
        
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.cssText = `
                position: absolute;
                width: 2px;
                height: 2px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                animation: float ${Math.random() * 3 + 2}s ease-in-out infinite;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                animation-delay: ${Math.random() * 2}s;
            `;
            particlesContainer.appendChild(particle);
        }
        
        document.body.appendChild(particlesContainer);
    }
    
    // Función para mejorar los efectos de los campos de entrada
    function enhanceInputFields() {
        const inputs = document.querySelectorAll('.fi-input');
        
        inputs.forEach(input => {
            // Agregar efectos de focus
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
                this.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
                this.style.transform = 'translateY(0)';
            });
            
            // Agregar validación visual en tiempo real
            input.addEventListener('input', function() {
                const wrapper = this.closest('.fi-fo-field-wrp');
                
                if (this.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailRegex.test(this.value)) {
                        wrapper.classList.add('success');
                        wrapper.classList.remove('error');
                    } else if (this.value.length > 0) {
                        wrapper.classList.add('error');
                        wrapper.classList.remove('success');
                    } else {
                        wrapper.classList.remove('success', 'error');
                    }
                }
                
                if (this.type === 'password') {
                    if (this.value.length >= 8) {
                        wrapper.classList.add('success');
                        wrapper.classList.remove('error');
                    } else if (this.value.length > 0) {
                        wrapper.classList.add('error');
                        wrapper.classList.remove('success');
                    } else {
                        wrapper.classList.remove('success', 'error');
                    }
                }
            });
        });
    }
    
    // Función para mejorar el botón de login
    function enhanceLoginButton() {
        const loginButton = document.querySelector('.fi-btn-primary');
        
        if (loginButton) {
            loginButton.addEventListener('click', function(e) {
                // Agregar efecto de carga
                this.classList.add('loading');
                this.style.pointerEvents = 'none';
                
                // Simular tiempo de carga (se removería cuando el formulario se envíe realmente)
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.style.pointerEvents = 'auto';
                }, 2000);
            });
            
            // Efecto de ripple
            loginButton.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        }
    }
    
    // Función para agregar animaciones de entrada
    function addEntryAnimations() {
        const elements = document.querySelectorAll('.fi-fo-field-wrp, .fi-btn-primary');
        
        elements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100 + 300);
        });
    }
    
    // Función para mejorar la experiencia de escritura
    function enhanceTypingExperience() {
        const inputs = document.querySelectorAll('.fi-input');
        
        inputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                // Agregar efecto de tecla presionada
                this.style.transform = 'scale(0.98)';
                
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            });
        });
    }
    
    // Función para agregar efectos de hover mejorados
    function enhanceHoverEffects() {
        const interactiveElements = document.querySelectorAll('.fi-input, .fi-btn-primary, .fi-checkbox');
        
        interactiveElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            });
        });
    }
    
    // Función para manejar el tema oscuro/claro
    function handleThemeToggle() {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
        
        function updateTheme(e) {
            if (e.matches) {
                document.body.classList.add('dark-theme');
            } else {
                document.body.classList.remove('dark-theme');
            }
        }
        
        prefersDark.addListener(updateTheme);
        updateTheme(prefersDark);
    }
    
    // Inicializar todas las mejoras
    function initializeEnhancements() {
        createParticles();
        enhanceInputFields();
        enhanceLoginButton();
        addEntryAnimations();
        enhanceTypingExperience();
        enhanceHoverEffects();
        handleThemeToggle();
    }
    
    // Ejecutar cuando el DOM esté listo
    initializeEnhancements();
    
    // Agregar estilos CSS adicionales para las animaciones
    const additionalStyles = document.createElement('style');
    additionalStyles.textContent = `
        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }
        
        .focused {
            transform: translateY(-2px);
        }
        
        .particle {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
                opacity: 0.3;
            }
            50% {
                transform: translateY(-20px);
                opacity: 0.8;
            }
        }
        
        .dark-theme .fi-simple-main {
            background: rgba(17, 24, 39, 0.95) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        
        .dark-theme .fi-input {
            background: rgba(31, 41, 55, 0.8) !important;
            border-color: #374151 !important;
            color: #f9fafb !important;
        }
        
        .dark-theme .fi-fo-field-wrp-label {
            color: #f9fafb !important;
        }
    `;
    
    document.head.appendChild(additionalStyles);
});

// Función para precargar recursos
function preloadResources() {
    const resources = [
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'
    ];
    
    resources.forEach(resource => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.href = resource;
        link.as = 'style';
        document.head.appendChild(link);
    });
}

// Ejecutar precarga de recursos
preloadResources();