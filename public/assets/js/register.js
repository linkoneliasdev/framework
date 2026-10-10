// pages/register.js

document.getElementById('formAuthentication')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!deviceFingerprint) {
        alert("Aguarde segurança...");
        return;
    }

    showLoader();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const nome = document.getElementById('username').value;
    const phone = document.getElementById('phone').value;

    try {
        const result = await auth.createUserWithEmailAndPassword(email, password);

        await result.user.updateProfile({ displayName: nome });

        await sendToBackend(result.user, { nome, phone });

    } catch (error) {
        hideLoader();
        alert(error.message);
    }
});