// pages/login.js
document.querySelectorAll('.form-password-toggle').forEach(wrapper => {

    const input = wrapper.querySelector('input');
    const toggle = wrapper.querySelector('.input-group-text i');

    if (!input || !toggle) return;

    toggle.addEventListener('click', () => {

        const isPassword = input.type === 'password';

        input.type = isPassword ? 'text' : 'password';

        // troca ícone
        toggle.classList.toggle('bx-hide');
        toggle.classList.toggle('bx-show');
    });

});

function getFirebaseErrorMessage(code) {

    const errors = {
        'auth/user-not-found': 'Usuário não encontrado.',
        'auth/wrong-password': 'Senha incorreta.',
        'auth/invalid-email': 'E-mail inválido.',
        'auth/too-many-requests': 'Muitas tentativas. Tente novamente mais tarde.',
        'auth/popup-closed-by-user': 'Login cancelado.',
        'auth/network-request-failed': 'Erro de conexão.',
    };

    return errors[code] || 'Erro ao autenticar.';
}


// 🔹 LOGIN EMAIL/SENHA
document.getElementById('formAuthentication')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!deviceFingerprint) {
        alerta({
            icon: 'warning',
            text: 'Aguarde inicialização da segurança...'
        });
        return;
    }

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {

        initLoader();

        const result = await auth.signInWithEmailAndPassword(email, password);

        await sendToBackend(result.user);

    } catch (error) {

        stopLoader();

        alerta({
            icon: 'error',
            title: 'Erro',
            text: getFirebaseErrorMessage(error.code)
        });
    }
});


// 🔹 LOGIN GOOGLE
document.querySelector('.btn-google')?.addEventListener('click', async () => {

    if (!deviceFingerprint) {
        alerta({
            icon: 'warning',
            text: 'Aguarde inicialização da segurança...'
        });
        return;
    }

    const provider = new firebase.auth.GoogleAuthProvider();

    try {

        initLoader();

        const result = await auth.signInWithPopup(provider);

        await sendToBackend(result.user);

    } catch (error) {

        stopLoader();

        if (error.code === 'auth/popup-closed-by-user') {
            notify({
                type: 'info',
                title: 'Login cancelado'
            });
            return;
        }

        alerta({
            icon: 'error',
            title: 'Erro',
            text: getFirebaseErrorMessage(error.code)
        });
    }
});