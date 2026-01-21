document.addEventListener('DOMContentLoaded', () => {
    if (window.Capacitor) {
        const { App } = Capacitor.Plugins;

        App.addListener('backButton', ({ canGoBack }) => {
            // Selecciona todos los modales abiertos
            const modalsAbiertos = document.querySelectorAll('.modal.open'); // Ajusta la clase si usas otro nombre
            
            if (modalsAbiertos.length > 0) {
                // Cierra todos los modales abiertos
                modalsAbiertos.forEach(modal => modal.classList.remove('open'));
            } else if (canGoBack) {
                window.history.back(); // Retrocede en la navegación si no hay modales abiertos
            } else {
                // Si no hay historial de navegación, preguntar antes de salir de la app
                if (confirm("¿Deseas salir de la aplicación?")) {
                    App.exitApp(); // Cierra la app
                }
            }
        });
    }
});

