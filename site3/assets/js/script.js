/**
 * Gatillos Invisibles de la Atracción - Vogue Edition
 * JavaScript principal para funcionalidades interactivas
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar animaciones
    initializeAnimations();
    
    // Configurar interactividad FAQ
    setupFAQAccordion();
    
    // Configurar efectos de portada 3D
    setup3DBookCover();
    
    // Configurar formulario de email
    setupEmailForm();
    
    // Configurar comportamiento de scroll
    setupScrollBehavior();
    
    // Iniciar pulsación en botones CTA después de 2 segundos
    setTimeout(function() {
        const ctaButtons = document.querySelectorAll('.cta-button');
        ctaButtons.forEach(button => {
            button.classList.add('pulse');
        });
    }, 2000);
});

/**
 * Inicializa las animaciones de entrada para elementos de la página
 */
function initializeAnimations() {
    // Elementos que se animarán con fade-in secuencial
    const animatedElements = [
        '.article-intro',
        '.author-section',
        '.content-section',
        '.product-section',
        '.success-stories',
        '.faq-section',
        '.bonus-section',
        '.guarantee-section',
        '.email-section'
    ];
    
    // Aplicar las animaciones con delay incremental
    animatedElements.forEach((selector, index) => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            
            setTimeout(() => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, 300 + (index * 150));
        });
    });
}

/**
 * Configura el comportamiento de acordeón para las FAQs
 */
function setupFAQAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        
        // Establecer altura inicial a 0 para todas las respuestas
        answer.style.height = '0';
        answer.style.overflow = 'hidden';
        answer.style.transition = 'height 0.3s ease';
        
        question.addEventListener('click', () => {
            // Si este ítem ya está activo, cerrarlo
            if (item.classList.contains('active')) {
                item.classList.remove('active');
                answer.style.height = '0';
            } else {
                // Cerrar todos los otros ítems
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                        const otherAnswer = otherItem.querySelector('.faq-answer');
                        otherAnswer.style.height = '0';
                    }
                });
                
                // Abrir este ítem
                item.classList.add('active');
                answer.style.height = answer.scrollHeight + 'px';
            }
        });
    });
}

/**
 * Configura el efecto 3D para la portada del libro
 */
function setup3DBookCover() {
    const bookImage = document.querySelector('.product-image img');
    if (!bookImage) return;
    
    const container = document.querySelector('.product-image');
    
    container.addEventListener('mousemove', e => {
        const { left, top, width, height } = container.getBoundingClientRect();
        const x = (e.clientX - left) / width - 0.5;
        const y = (e.clientY - top) / height - 0.5;
        
        // Calcular la rotación basada en la posición del mouse
        const rotateY = x * 15; // máximo 15 grados
        const rotateX = -y * 15; // máximo 15 grados
        const shadow = Math.max(Math.abs(x), Math.abs(y)) * 20;
        
        // Aplicar transformación 3D
        bookImage.style.transform = `
            perspective(1000px)
            rotateY(${rotateY}deg)
            rotateX(${rotateX}deg)
            scale(1.05)
        `;
        bookImage.style.boxShadow = `
            0 ${5 + shadow}px ${10 + shadow}px rgba(0,0,0,0.2),
            0 ${2 + shadow}px ${5 + shadow}px rgba(0,0,0,0.1)
        `;
    });
    
    container.addEventListener('mouseleave', () => {
        // Restaurar posición original con transición suave
        bookImage.style.transform = 'perspective(1000px) rotateY(0) rotateX(0) scale(1)';
        bookImage.style.boxShadow = '0 5px 10px rgba(0,0,0,0.2), 0 2px 5px rgba(0,0,0,0.1)';
    });
}

/**
 * Configura el comportamiento del formulario de email
 * con validación y envío a API
 */
function setupEmailForm() {
    const form = document.getElementById('email-form');
    const emailInput = document.getElementById('email');
    const submitButton = document.querySelector('.submit-button');
    const originalButtonText = submitButton.innerHTML;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar el email
        const email = emailInput.value.trim();
        if (!validateEmail(email)) {
            showNotification('Por favor, introduce un email válido', 'error');
            return;
        }
        
        // Mostrar indicador de carga
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        submitButton.disabled = true;
        
        // Enviar email a la API
        sendEmailToAutonami(email)
            .then(response => {
                if (response.success) {
                    showNotification('¡Email registrado con éxito!', 'success');
                    
                    // Redireccionar tras breve delay
                    setTimeout(() => {
                        window.location.href = 'https://dekoola.com/ch/hack/';
                    }, 1500);
                } else {
                    throw new Error(response.message || 'Error en el servidor');
                }
            })
            .catch(error => {
                console.error('Error al enviar email:', error);
                
                // En caso de error en API, mostrar mensaje pero seguir con redirección
                showNotification('Procesando tu solicitud...', 'info');
                setTimeout(() => {
                    window.location.href = 'https://dekoola.com/ch/hack/';
                }, 2000);
            });
    });
}

/**
 * Valida el formato de email
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Envía el email capturado a la API de Autonami
 */
function sendEmailToAutonami(email) {
    const apiUrl = 'https://dekoola.com/wp-json/autonami/v1/webhook/?bwfan_autonami_webhook_id=10&bwfan_autonami_webhook_key=92c39df617252d128219dba772cff29a';
    
    return fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email: email,
            source: 'exemplo3_vogue_landing',
            date: new Date().toISOString()
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta de la API');
        }
        return response.json();
    })
    .then(data => {
        // Simulamos respuesta exitosa para propósitos de demostración
        return { success: true, data };
    });
}

/**
 * Muestra notificaciones al usuario
 */
function showNotification(message, type) {
    // Eliminar notificaciones existentes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(n => n.remove());
    
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Añadir al documento
    document.body.appendChild(notification);
    
    // Mostrar con animación
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Auto-ocultar después de unos segundos
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Configura comportamientos basados en scroll
 */
function setupScrollBehavior() {
    window.addEventListener('scroll', () => {
        const scrollPos = window.scrollY;
        const header = document.querySelector('.site-header');
        
        // Header compacto al scrollear
        if (scrollPos > 100) {
            header.classList.add('compact');
        } else {
            header.classList.remove('compact');
        }
        
        // Animación de elementos al entrar en viewport
        const elementsToAnimate = document.querySelectorAll('.animate-on-scroll:not(.animated)');
        elementsToAnimate.forEach(el => {
            const elementTop = el.getBoundingClientRect().top;
            const elementVisible = 150;
            
            if (elementTop < window.innerHeight - elementVisible) {
                el.classList.add('animated');
            }
        });
    });
} 