async function sendToBackend(user, extraData = {}) {

    const idToken = await user.getIdToken();

    const formData = new FormData();
    formData.append('idToken', idToken);
    formData.append('nome', extraData.nome || user.displayName);
    formData.append('phone', extraData.phone || "");
    formData.append('device_hash', deviceFingerprint);

    try {

        const res = await fetch('/api/auth/auth_bridge.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.success) {

            window.location.href = "/";

        } else {

            stopLoader();

            await auth.signOut();

            alerta({
                icon: 'error',
                title: 'Erro',
                text: data.message || 'Erro ao autenticar'
            });
        }

    } catch (err) {

        stopLoader();

        alerta({
            icon: 'error',
            title: 'Erro',
            text: 'Erro de conexão com servidor'
        });
    }
}