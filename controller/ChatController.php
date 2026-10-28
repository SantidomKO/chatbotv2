<?php

require_once __DIR__ . '/../app_config.php';

class ChatController
{
    public function index()
    {

        require_once 'view/chat/index.php';
    }

    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Accept JSON payload or traditional form POST
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (!is_array($input)) {
                $input = $_POST;
            }
            $message = isset($input['message']) ? trim(strip_tags((string)$input['message'])) : '';

            if ($message === '') {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Mensaje vacío']);
                return;
            }

            // Limit message length
            if (mb_strlen($message) > 2000) {
                $message = mb_substr($message, 0, 2000);
            }
            // Primero obtener el parse (intent + confidence) usando /model/parse
            $rasaUrl = rtrim(app_config('RASA_URL', 'http://localhost:5005'), '/');
            $parse_url = $rasaUrl . '/model/parse';
            $parsePayload = json_encode(['text' => $message]);
            $chParse = curl_init($parse_url);
            curl_setopt($chParse, CURLOPT_POST, true);
            curl_setopt($chParse, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($chParse, CURLOPT_POSTFIELDS, $parsePayload);
            curl_setopt($chParse, CURLOPT_RETURNTRANSFER, true);
            $parseResult = curl_exec($chParse);
            $parseErr = curl_error($chParse);
            curl_close($chParse);

            // Luego llamar al webhook para obtener la(s) respuesta(s) del bot
            $rasa_webhook_url = $rasaUrl . '/webhooks/rest/webhook';
            $payload = json_encode([
                'sender' => 'test_user',
                'message' => $message,
            ]);
            $ch = curl_init($rasa_webhook_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($result === false || $err || $parseResult === false || $parseErr) {
                http_response_code(502);
                $response = ['error' => $err ?: $parseErr ?: 'No response from Rasa'];
            } else {
                $data = json_decode($result, true);
                $parsed = json_decode($parseResult, true);
                // parsed contiene 'intent' con name/confidence
                $intentName = null;
                $confidence = null;
                if (is_array($parsed) && !empty($parsed['intent'])) {
                    $intentName = $parsed['intent']['name'] ?? null;
                    $confidence = isset($parsed['intent']['confidence']) ? $parsed['intent']['confidence'] : null;
                }

                // Registrar usando umbral por defecto (0.7)
                require_once 'model/Consulta.class.php';
                $usuarioId = null;
                $consultaObj = Consulta::registrarDesdeRasa($message, $confidence, $usuarioId, $intentName);

                $response = ['rasa' => $data, 'parse' => $parsed, 'consulta' => $consultaObj ? 'ok' : 'error'];
            }

            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }
}

?>