document.getElementById('formForgot')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = document.getElementById('email').value;

    try {

        initLoader();

        await auth.sendPasswordResetEmail(email);

        stopLoader();

        alerta({
            icon: 'success',
            title: 'Enviado!',
            text: 'Verifique seu e-mail para redefinir a senha.'
        });

    } catch (error) {

        stopLoader();

        alerta({
            icon: 'error',
            title: 'Erro',
            text: getFirebaseErrorMessage(error.code)
        });
    }
});