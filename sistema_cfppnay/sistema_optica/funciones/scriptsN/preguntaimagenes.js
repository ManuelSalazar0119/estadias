document.addEventListener("DOMContentLoaded", function () {
    // Seleccionar todos los inputs de tipo file
    const fileInputs = document.querySelectorAll('input[type="file"]');

    fileInputs.forEach(input => {
        const label = input.previousElementSibling; // El label que contiene la imagen
        const preview = document.getElementById(`preview_${input.id}`);

        label.addEventListener("click", function (event) {
            event.preventDefault(); // Evita que el input se active automáticamente

            // Mostrar la pregunta al usuario
            const opcion = confirm("¿Quieres subir una foto desde tu galería? (Si eliges 'Cancelar' se abrirá la cámara)");

            if (opcion) {
                // Si elige subir, quitar capture
                input.removeAttribute("capture");
            } else {
                // Si elige tomar, agregar capture="environment"
                input.setAttribute("capture", "environment");
            }

            // Activar el input manualmente después de la selección
            input.click();
        });

        // Mostrar la vista previa de la imagen seleccionada
        input.addEventListener("change", function () {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = "block";
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
    });
});
