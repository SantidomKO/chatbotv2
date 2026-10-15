<?php
require_once "model/Usuario.class.php";
require_once "model/Authenticator.class.php";

class PerfilController {
    public function ver() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_data'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $usuario = Usuario::obtenerPorID($_SESSION['usuario_data']['id']);
        $nombreUsuario = $usuario ? $usuario->getNombre() : 'Usuario';
        $inicial = strtoupper(substr($nombreUsuario, 0, 1));
        $authenticator = new Authenticator();
        $twoFactorEnabled = $usuario ? $authenticator->isEnabled($usuario->getId()) : false;
        $setup = $_SESSION['pending_2fa_setup'] ?? null;
        if ($setup && ((int) $setup['user_id'] !== (int) $_SESSION['usuario_data']['id'] || (int) $setup['expires_at'] < time())) {
            unset($_SESSION['pending_2fa_setup']);
            $setup = null;
        }
        $provisioningUri = $setup ? Authenticator::provisioningUri($usuario->getEmail(), $setup['secret']) : null;

        require_once "view/perfil/ver.php";
    }

    public function iniciarConfiguracion2FA()
    {
        $userId = (int) ($_SESSION['usuario_data']['id'] ?? 0);
        if (!$this->validarCsrf() || !$userId) {
            $_SESSION['error'] = 'La solicitud expiró. Intenta nuevamente.';
            $this->redirectToProfile();
        }

        $usuario = Usuario::obtenerPorID($userId);
        $email = $usuario ? $usuario->getEmail() : '';
        if (!$usuario || !Usuario::verificarLogin($email, $_POST['password'] ?? '')) {
            $_SESSION['error'] = 'La contraseña no es correcta.';
            $this->redirectToProfile();
        }

        $authenticator = new Authenticator();
        if ($authenticator->isEnabled($userId)) {
            $_SESSION['error'] = 'La verificación en dos pasos ya está activada.';
            $this->redirectToProfile();
        }

        $_SESSION['pending_2fa_setup'] = [
            'user_id' => $userId,
            'secret' => Authenticator::generateSecret(),
            'expires_at' => time() + 600,
        ];
        $this->redirectToProfile();
    }

    public function activar2FA()
    {
        $userId = (int) ($_SESSION['usuario_data']['id'] ?? 0);
        $setup = $_SESSION['pending_2fa_setup'] ?? null;
        if (!$this->validarCsrf() || !$userId || !$setup || (int) $setup['user_id'] !== $userId || (int) $setup['expires_at'] < time()) {
            unset($_SESSION['pending_2fa_setup']);
            $_SESSION['error'] = 'La configuración expiró. Iníciala nuevamente.';
            $this->redirectToProfile();
        }

        $counter = Authenticator::verifyCode($setup['secret'], $_POST['code'] ?? '');
        if ($counter === false) {
            $_SESSION['error'] = 'El código no es válido. Verifica que la cuenta use códigos basados en tiempo (TOTP), que se haya agregado con la clave de esta pantalla y prueba el código más reciente.';
            $this->redirectToProfile();
        }

        $authenticator = new Authenticator();
        if (!$authenticator->enable($userId, $setup['secret'], $counter)) {
            $_SESSION['error'] = 'No se pudo activar la verificación en dos pasos.';
            $this->redirectToProfile();
        }

        unset($_SESSION['pending_2fa_setup']);
        $_SESSION['success'] = 'Verificación en dos pasos activada.';
        $this->redirectToProfile();
    }

    public function desactivar2FA()
    {
        $userId = (int) ($_SESSION['usuario_data']['id'] ?? 0);
        if (!$this->validarCsrf() || !$userId) {
            $_SESSION['error'] = 'La solicitud expiró. Intenta nuevamente.';
            $this->redirectToProfile();
        }

        $usuario = Usuario::obtenerPorID($userId);
        if (!$usuario || !Usuario::verificarLogin($usuario->getEmail(), $_POST['password'] ?? '')) {
            $_SESSION['error'] = 'La contraseña no es correcta.';
            $this->redirectToProfile();
        }

        $authenticator = new Authenticator();
        if (!$authenticator->verifyAndConsume($userId, $_POST['code'] ?? '')) {
            $_SESSION['error'] = 'El código del autenticador no es válido.';
            $this->redirectToProfile();
        }

        $authenticator->disable($userId);
        $_SESSION['success'] = 'Verificación en dos pasos desactivada.';
        $this->redirectToProfile();
    }

    private function validarCsrf()
    {
        return isset($_SESSION['csrf_token'], $_POST['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }

    private function redirectToProfile()
    {
        header('Location: index.php?controller=perfil&action=ver');
        exit();
    }
}
