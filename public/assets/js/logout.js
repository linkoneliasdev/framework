// logout.js

document.addEventListener('DOMContentLoaded', () => {

    const btnLogout = document.getElementById('btnLogout');

    if (!btnLogout) return;

    btnLogout.addEventListener('click', async () => {

        try {

            // 1. Logout Firebase
            await auth.signOut();

            // 2. Logout backend
            await fetch('/api/auth/logout.php', {
                method: 'POST'
            });

            // 3. Redireciona
            window.location.href = '/login';

        } catch (error) {
            console.error('Erro no logout:', error);
            alert('Erro ao sair.');
        }

    });

});