document.addEventListener('DOMContentLoaded', function () {
    // Escucha los mensajes del iframe
    window.addEventListener('message', function (event) {
        if (event.data.action === 'openModal') {
            openModalInBody(event.data.url);
        }
    });
});


function openModalInBody(url) {
    // Abre el modal en el body del documento principal
    var modalContainer = document.createElement('div');
    modalContainer.className = 'bootbox-body';
    modalContainer.innerHTML = '<div class="text-center"><iframe id="inlineFrameExample" width="100%" height="700px" src="' + url + '"></iframe></div>';

    // Agrega el modal al body del documento principal
    document.body.appendChild(modalContainer);

    // Abre el modal utilizando Bootbox.js
    bootbox.dialog({
        message: modalContainer,
        closeButton: true,
        callback: function () {
            // Elimina el modal del DOM después de cerrarlo
            document.body.removeChild(modalContainer);
        }
    });
}