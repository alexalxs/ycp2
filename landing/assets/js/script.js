/**
 * Gatillos Invisibles de la Atracción - BAZAAR Edition
 * Funcionalidades JavaScript para interacción y animaciones
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todas las funcionalidades
    initAnimations();
    setupFancyHeaderScroll();
    setupEmailForm();
    setupFAQs();
    setupBookHover();
    handleScroll();
    setupModal();
    
    // Configurar detección de scroll
    window.addEventListener('scroll', handleScroll);
    
    // Iniciar animación de pulsación en botones después de 2 segundos
    setTimeout(function() {
        document.querySelectorAll('.btn-primary, .btn-secondary, .submit-button, .info-button').forEach(function(button) {
            button.classList.add('pulse-animation');
        });
    }, 2000);
});

/**
 * Inicializa las animaciones de entrada para elementos de la página
 */
function initAnimations() {
    // Elementos que se animarán con retraso progresivo
    const animatedElements = [
        { selector: '.hero-content', delay: 0 },
        { selector: '.hero-image', delay: 300 },
        { selector: '.intro-section', delay: 600 },
        { selector: '.feature-quote', delay: 900 },
        { selector: '.content-grid', delay: 1200 },
        { selector: '.product-showcase', delay: 1500 },
        { selector: '.success-stories', delay: 1800 },
        { selector: '.faq-section', delay: 2100 },
        { selector: '.capture-section', delay: 2400 }
    ];

    // Aplicar animación a cada elemento con su retraso correspondiente
    animatedElements.forEach(function(item) {
        const elements = document.querySelectorAll(item.selector);
        elements.forEach(function(element) {
            setTimeout(function() {
                element.classList.add('visible');
            }, item.delay);
        });
    });
}

/**
 * Configura efectos de hover para la imagen del libro
 */
function setupBookHover() {
    const bookCover = document.querySelector('.book-cover');
    if (!bookCover) return;

    bookCover.addEventListener('mousemove', function(e) {
        const bounds = this.getBoundingClientRect();
        const mouseX = e.clientX - bounds.left;
        const mouseY = e.clientY - bounds.top;
        
        // Calcular la posición relativa del ratón (valores de -1 a 1)
        const xPercent = (mouseX / bounds.width) * 2 - 1;
        const yPercent = (mouseY / bounds.height) * 2 - 1;
        
        // Aplicar rotación basada en la posición del ratón (máximo 10 grados)
        const maxRotation = 10;
        const rotateX = -yPercent * maxRotation;
        const rotateY = xPercent * maxRotation;
        
        // Aplicar transformación
        this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px)`;
    });

    bookCover.addEventListener('mouseleave', function() {
        // Restablecer transformación al salir
        this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
    });
}

/**
 * Configura el comportamiento del header al hacer scroll
 */
function setupFancyHeaderScroll() {
    const header = document.querySelector('.site-header');
    if (!header) return;

    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
}

/**
 * Configura la funcionalidad del formulario de captura de email
 */
function setupEmailForm() {
    const emailForm = document.querySelector('.email-form');
    if (!emailForm) return;

    emailForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obtener el email del formulario
        const emailInput = this.querySelector('input[type="email"]');
        const email = emailInput.value.trim();
        
        // Validar el email
        if (!isValidEmail(email)) {
            showMessage('Por favor, introduce un email válido.', 'error');
            return;
        }
        
        // Enviar el email a la API
        sendEmailToAutonami(email);
    });
}

/**
 * Valida el formato de email usando expresión regular
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Envía el email a la API de Autonami para procesamiento
 */
function sendEmailToAutonami(email) {
    // Mostrar estado de carga
    showLoading(true);
    
    // Preparar los datos para enviar a la API
    const formData = new FormData();
    formData.append('email', email);
    
    // Realizar la solicitud a la API de Autonami
    fetch('https://dekoola.com/wp-json/autonami/v1/webhook/?bwfan_autonami_webhook_id=10&bwfan_autonami_webhook_key=92c39df617252d128219dba772cff29a', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Verificar si la respuesta es exitosa
        if (response.ok) {
            return response.json(); // Convertir respuesta a JSON
        } else {
            throw new Error('Error en la respuesta del servidor');
        }
    })
    .then(data => {
        // Procesar respuesta exitosa
        showLoading(false);
        showMessage('¡Gracias por tu interés! Te estamos redirigiendo a la página de compra.', 'success');
        
        // Redireccionar a la página de compra después de 3 segundos
        setTimeout(function() {
            window.location.href = 'https://dekoola.com/ch/hack/';
        }, 3000);
    })
    .catch(error => {
        // Manejar errores
        console.error('Error:', error);
        showLoading(false);
        
        // En caso de error, intentar redireccionar de todos modos
        showMessage('Estamos procesando tu solicitud. Te redirigiremos en unos momentos...', 'success');
        setTimeout(function() {
            window.location.href = 'https://dekoola.com/ch/hack/';
        }, 3000);
    });
}

/**
 * Muestra/oculta el indicador de carga en el botón de envío
 */
function showLoading(isLoading) {
    const submitButton = document.querySelector('.submit-button');
    if (!submitButton) return;
    
    if (isLoading) {
        submitButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Procesando...';
        submitButton.disabled = true;
        submitButton.classList.add('loading');
    } else {
        submitButton.innerHTML = 'OBTENER AHORA';
        submitButton.disabled = false;
        submitButton.classList.remove('loading');
    }
}

/**
 * Muestra un mensaje al usuario
 */
function showMessage(message, type) {
    // Buscar o crear el contenedor de mensajes
    let messageContainer = document.querySelector('.form-message');
    
    if (!messageContainer) {
        messageContainer = document.createElement('div');
        messageContainer.className = 'form-message';
        const form = document.querySelector('.email-form');
        if (form) {
            form.appendChild(messageContainer);
        }
    }
    
    // Actualizar el contenido y clase del mensaje
    messageContainer.textContent = message;
    messageContainer.className = 'form-message ' + type;
    
    // Hacer visible el mensaje
    messageContainer.style.display = 'block';
    
    // Ocultar el mensaje después de 5 segundos para mensajes de éxito
    if (type === 'success') {
        setTimeout(function() {
            messageContainer.style.display = 'none';
        }, 5000);
    }
}

/**
 * Configura el comportamiento de los acordeones de FAQ
 */
function setupFAQs() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(function(item) {
        const question = item.querySelector('.faq-question');
        
        if (question) {
            question.addEventListener('click', function() {
                // Cerrar todas las otras preguntas
                faqItems.forEach(function(otherItem) {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Alternar estado activo de la pregunta actual
                item.classList.toggle('active');
            });
        }
    });
}

/**
 * Maneja los efectos visuales basados en la posición de scroll
 */
function handleScroll() {
    const scrollY = window.scrollY;
    
    // Animar elementos cuando entren en el viewport
    const elementsToAnimate = document.querySelectorAll('.animate-on-scroll:not(.animated)');
    
    elementsToAnimate.forEach(function(element) {
        const elementTop = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementTop < windowHeight * 0.8) {
            element.classList.add('animated');
        }
    });
    
    // Ajustar el estilo del header basado en la posición de scroll
    const header = document.querySelector('.site-header');
    
    if (header) {
        if (scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
    
    // Efectos de parallax para ciertos elementos
    const parallaxElements = document.querySelectorAll('.parallax');
    
    parallaxElements.forEach(function(element) {
        const speed = element.getAttribute('data-parallax-speed') || 0.2;
        element.style.transform = `translateY(${scrollY * speed}px)`;
    });
}

/**
 * Configura el comportamiento del modal y su formulario
 */
function setupModal() {
    const modal = document.getElementById('contact-modal');
    const obtenerBtn = document.getElementById('obtener-btn');
    const closeBtn = document.querySelector('.close-modal');
    const form = document.getElementById('contact-form');
    const loadingIndicator = document.querySelector('.loading-indicator');
    
    // Verificar si los elementos existen
    if (!modal || !closeBtn || !form || !loadingIndicator) {
        console.error('Elementos del modal no encontrados');
        return;
    }
    
    // Abrir modal al hacer clic en los botones
    obtenerBtn.addEventListener('click', function() {
        openModal();
    });
    
    // Cerrar modal con el botón X
    closeBtn.addEventListener('click', function() {
        closeModal();
    });
    
    // Cerrar modal al hacer clic fuera de su contenido
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            closeModal();
        }
    });
    
    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obtener valores del formulario
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('modal-email');
        const name = nameInput.value.trim();
        const email = emailInput.value.trim();
        
        // Validar campos
        if (name === '') {
            showModalMessage('Por favor, introduce tu nombre.', 'error');
            return;
        }
        
        if (!isValidEmail(email)) {
            showModalMessage('Por favor, introduce un email válido.', 'error');
            return;
        }
        
        // Mostrar indicador de carga - CORREÇÃO
        const submitBtn = form.querySelector('.modal-submit-btn');
        if (submitBtn) {
            submitBtn.style.display = 'none';
        }
        loadingIndicator.style.display = 'flex';
        
        // Obtener datos del formulario para enviar
        const formData = new FormData(form);
        
        // Enviar datos a través de fetch
        fetch('form-processor.php', {
            method: 'POST',
            body: formData,
            // Adicionando timeout para evitar espera muito longa
            signal: AbortSignal.timeout(10000) // 10 segundos de timeout
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta del servidor:', data);
            
            // Ocultar indicador de carga
            loadingIndicator.style.display = 'none';
            if (submitBtn) {
                submitBtn.style.display = 'block';
            }
            
            // Mostrar mensaje de éxito
            showModalMessage('¡Tu solicitud ha sido procesada con éxito! Serás redirigido en breve.', 'success');
            
            // Limpiar formulario
            form.reset();
            
            // Redirigir después de un breve delay - reduzido para 1 segundo
            setTimeout(function() {
                window.location.href = 'https://dekoola.com/ch/hack/';
            }, 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Ocultar indicador de carga
            loadingIndicator.style.display = 'none';
            if (submitBtn) {
                submitBtn.style.display = 'block';
            }
            
            // Guardar dados do formulário no localStorage como backup
            try {
                localStorage.setItem('form_name', nameInput.value);
                localStorage.setItem('form_email', emailInput.value);
            } catch (e) {
                console.error('Error guardando datos:', e);
            }
            
            // Mostrar mensaje positivo de continuidad sin mencionar error
            showModalMessage('Estamos procesando tu información. Serás redirigido a la siguiente página.', 'info');
            
            // Redirecionar mais rápido
            setTimeout(function() {
                window.location.href = 'https://dekoola.com/ch/hack/';
            }, 2000);
        });
    });
    
    /**
     * Abre el modal
     */
    function openModal() {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevenir scroll en el fondo
        setTimeout(() => {
            modal.querySelector('.modal-content').style.transform = 'translateY(0)';
        }, 10);
    }
    
    /**
     * Cierra el modal
     */
    function closeModal() {
        modal.querySelector('.modal-content').style.transform = 'translateY(-20px)';
        setTimeout(() => {
            modal.classList.remove('show');
            document.body.style.overflow = ''; // Restaurar scroll
        }, 300);
    }
    
    /**
     * Muestra mensajes en el modal
     */
    function showModalMessage(message, type) {
        // Eliminar mensaje anterior si existe
        const oldMessage = form.querySelector('.modal-message');
        if (oldMessage) {
            oldMessage.remove();
        }
        
        // Crear nuevo mensaje
        const messageElement = document.createElement('div');
        messageElement.className = `modal-message ${type}`;
        messageElement.textContent = message;
        
        // Insertar después del botón de envío
        const submitContainer = form.querySelector('.form-submit');
        form.insertBefore(messageElement, submitContainer.nextSibling);
        
        // Auto-ocultar mensaje después de 5 segundos para mensajes de éxito
        if (type === 'success') {
            setTimeout(function() {
                messageElement.remove();
            }, 5000);
        }
    }
}

// Agregar CSS personalizado para mensajes y animaciones
function addCustomCSS() {
    const style = document.createElement('style');
    style.textContent = `
        .form-message {
            padding: 15px;
            margin-top: 15px;
            border-radius: 4px;
            display: none;
            font-weight: 600;
        }
        
        .form-message.error {
            background-color: #fdf2f2;
            border: 1px solid #fed7d7;
            color: #b94a48;
        }
        
        .form-message.success {
            background-color: #f0fff4;
            border: 1px solid #c6f6d5;
            color: #5cb85c;
        }
        
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        
        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        .site-header.scrolled {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: rgba(255, 255, 255, 0.95);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .pulse-animation {
            animation: pulseEffect 2s infinite;
        }
        
        .submit-button.loading {
            background-color: var(--color-accent);
            cursor: wait;
        }
        
        .submit-button i {
            margin-right: 5px;
        }
        
        @keyframes pulseEffect {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
    `;
    
    document.head.appendChild(style);
}

// Ejecutar la función para agregar CSS personalizado
addCustomCSS(); 