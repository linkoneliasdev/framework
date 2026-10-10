// alerts.js

(function () {

    /*Exemplo de uso
    alerta({
        icon: 'success',
        title: 'Sucesso',
        text: 'Dados salvos com sucesso!'
    }); 
    */
    window.alerta = function ({
        icon = 'info',
        title = 'Aviso',
        text = '',
        confirmText = 'OK'
    } = {}) {

        return Swal.fire({
            icon,
            title,
            text,
            confirmButtonText: confirmText,
            confirmButtonColor: '#696cff',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false,
            didOpen: () => {
                const container = Swal.getContainer();
                if (container) {
                    container.style.zIndex = '2000';
                }
            }
        });
    };


    /*Exemplo de uso pra não esquecer 
        notify({
            type: 'success',
            title: 'Salvo com sucesso!'
        });
    */

   window.notify = function ({
        type = 'success', // success, error, warning, info, question
        title = '',
        timer = 3000,
        position = 'bottom-end'
    } = {}) {

        // 🔹 Mapeamento de cores por tipo
        const colors = {
            success: '#71dd37',
            error: '#ff3e1d',
            warning: '#ffab00',
            info: '#03c3ec',
            question: '#8592a3'
        };

        const Toast = Swal.mixin({
            toast: true,
            position: position,
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,
            iconColor: colors[type] || colors.success,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: type,
            title: title
        });
    };


    /*Exemplo de uso 
    const result = await confirmar({
        type: 'warning',
        title: 'Excluir item?',
        text: 'Essa ação não poderá ser desfeita'
    });

    if (result.isConfirmed) {
        console.log('confirmado');
    }
        
    */
    window.confirmar = function ({
        type = 'warning', // success, error, warning, info
        title = 'Tem certeza?',
        text = 'Essa ação não poderá ser desfeita.',
        confirmText = 'Sim',
        cancelText = 'Cancelar'
    } = {}) {

        const colors = {
            success: '#71dd37',
            error: '#ff3e1d',
            warning: '#ffab00',
            info: '#03c3ec'
        };

        return Swal.fire({
            title,
            text,
            icon: type,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            confirmButtonColor: colors[type] || '#696cff',
            cancelButtonColor: '#8592a3',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-secondary'
            },
            buttonsStyling: false
        });
    };

    /*Exemplo de uso 
    flash({
        type: 'success',
        title: 'Sucesso!',
        text: 'Salvo com sucesso'
    });
    
    flash({
        type: 'error',
        text: 'Erro ao processar'
    });

    */
    window.flash = function ({
        type = 'success',
        title = '',
        text = '',
        timer = 3000,
        position = 'center'
    } = {}) {

        const colors = {
            success: '#71dd37',
            error: '#ff3e1d',
            warning: '#ffab00',
            info: '#03c3ec'
        };

        return Swal.fire({
            icon: type,
            title: title,
            text: text,
            timer: timer,
            timerProgressBar: true,
            showConfirmButton: false,
            position: position,
            iconColor: colors[type] || colors.success,
            didOpen: () => {
                const container = Swal.getContainer();
                if (container) {
                    container.style.zIndex = '2000';
                }
            }
        });
    };


    /*Loader*/

    let loaderElement = null;

    function createLoader() {

        if (loaderElement) return;

        loaderElement = document.createElement('div');
        loaderElement.id = 'global-loader';

        loaderElement.innerHTML = `
            <div class="loader-backdrop"></div>
            <div class="loader-content">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-white">Carregando...</div>
            </div>
        `;

        document.body.appendChild(loaderElement);
    }

    window.initLoader = function () {

        createLoader();

        loaderElement.style.display = 'flex';

        setTimeout(() => {
            loaderElement.classList.add('show');
        }, 10);
    };

    window.stopLoader = function () {

        if (!loaderElement) return;

        loaderElement.classList.remove('show');

        setTimeout(() => {
            loaderElement.style.display = 'none';
        }, 200);
    };

})();