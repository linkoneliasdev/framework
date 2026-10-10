// register.js

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
        'auth/email-already-in-use': 'E-mail já cadastrado.',
        'auth/invalid-email': 'E-mail inválido.',
        'auth/weak-password': 'Senha muito fraca (mínimo 6 caracteres).',
        'auth/network-request-failed': 'Erro de conexão.',
    };

    return errors[code] || 'Erro ao cadastrar.';
}

document.getElementById('formRegister')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!deviceFingerprint) {
        alerta({
            icon: 'warning',
            text: 'Aguarde inicialização da segurança...'
        });
        return;
    }

    const nome = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const phone = document.getElementById('phone')?.value || '';

    try {

        initLoader();

        //  Cria usuário no Firebase
        const result = await auth.createUserWithEmailAndPassword(email, password);

        //  Atualiza nome no Firebase
        await result.user.updateProfile({
            displayName: nome
        });

        await sendToBackend(result.user, {
            nome,
            phone
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

// REGISTRO COM GOOGLE
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

        const user = result.user;

        await sendToBackend(user, {
            nome: user.displayName,
            phone: user.phoneNumber || ''
        });

    } catch (error) {

        stopLoader();

        // usuário fechou popup
        if (error.code === 'auth/popup-closed-by-user') {
            notify({
                type: 'info',
                title: 'Cadastro cancelado'
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