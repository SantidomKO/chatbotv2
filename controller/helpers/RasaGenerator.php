<?php
require_once __DIR__ . '/../../model/Intent.class.php';

class RasaGenerator {

    // Escapa texto para YAML en bloque de ejemplos
    private static function escapeExample($text) {
        $text = str_replace("\r", "", (string)($text ?? ''));
        $lines = preg_split('/\r?\n/', $text);
        $out = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $escaped = str_replace('\n', ' ', $line);
            $out[] = "    - " . str_replace("\n", ' ', $escaped);
        }
        return implode("\n", $out);
    }

    public static function generateYamlFromDB($dataDir = 'data', $domainDir = 'domain') {
        // Obtener todos los intents con frases y respuestas
        $items = Intent::getAllWithExamplesAndResponses();

        $nlu = "version: \"2.0\"\n\nnlu:\n";
        $domain = "version: \"2.0\"\n\nintents:\n";
        $responses = "responses:\n";
        $rules = "version: \"2.0\"\n\nrules:\n";

        foreach ($items as $row) {
            /** @var Intent $intent */
            $intent = $row['intent'];
            $frases = $row['frases'];
            $respuestas = $row['respuestas'];

            $safeName = 'dashboard_' . preg_replace('/[^a-z0-9_]/i', '_', strtolower($intent->getNombreIntent()));

            // NLU
            $nlu .= "- intent: {$safeName}\n";
            $nlu .= "  examples: |\n";
            if (empty($frases)) {
                $nlu .= "    - {$intent->getNombreIntent()}\n";
            } else {
                foreach ($frases as $f) {
                    $escaped = str_replace('\n', ' ', trim($f));
                    $nlu .= "    - " . str_replace("\n", ' ', $escaped) . "\n";
                }
            }
            $nlu .= "\n";

            // Domain intents
            $domain .= "- {$safeName}\n";

            // Responses: tomar la primera activa si existe
            $utterName = 'utter_' . $safeName;
            $respText = '';
            foreach ($respuestas as $r) {
                if (!empty($r['activa'])) {
                    $respText = $r['texto_respuesta'];
                    break;
                }
            }
            if ($respText === '' && count($respuestas) > 0) {
                $respText = $respuestas[0]['texto_respuesta'];
            }
            $respText = $respText ?: "";
            $responses .= "  {$utterName}:\n    - text: \"" . str_replace('"', '\\"', $respText) . "\"\n";

            // Rule
            $rules .= "- rule: respond_{$safeName}\n  steps:\n  - intent: {$safeName}\n  - action: {$utterName}\n\n";
        }

        // Escribir archivos
        if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
        if (!is_dir($domainDir)) mkdir($domainDir, 0755, true);

        $nluPath = rtrim($dataDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dashboard_nlu.yml';
        $rulesPath = rtrim($dataDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dashboard_rules.yml';
        $domainPath = rtrim($domainDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dashboard.yml';

        file_put_contents($nluPath, $nlu);
        file_put_contents($rulesPath, $rules);
        
        // Concatenar base domain si existe (removiendo 'version:' duplicados de base.yml)
        $baseDomainPath = $domainDir . DIRECTORY_SEPARATOR . 'base.yml';
        $finalDomain = "version: \"2.0\"\n\n" . $domain . "\n" . $responses;

        if (file_exists($baseDomainPath)) {
            $baseContent = file_get_contents($baseDomainPath);
            // Eliminar cualquier declaración de 'version:' del base.yml para evitar duplicación
            $baseContentClean = preg_replace('/^version:\s*["\']?[0-9.]+["\']?\s*/mi', '', $baseContent);
            $finalDomain = "version: \"2.0\"\n\n" . trim($baseContentClean) . "\n\n" . $domain . "\n" . $responses;
        }

        file_put_contents($domainPath, $finalDomain);

        // Retornar las rutas (esto faltaba en tu código anterior)
        return [
            'nlu' => $nluPath,
            'rules' => $rulesPath,
            'domain' => $domainPath
        ];
    }

    public static function trainAndLoadModel() {
        // Wrap operations to convert warnings into exceptions and return structured errors
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try {
            // Generar archivos YAML desde la base de datos (puede lanzar warnings/exceptions si falla escritura)
            $paths = self::generateYamlFromDB();

            // Asegurar rutas absolutas
            $nluPath = realpath($paths['nlu']);
            $rulesPath = realpath($paths['rules']);
            $domainPath = realpath($paths['domain']);

            // Construir un único YAML combinado para evitar que el servidor trate el body como multipart
            $domainContent = $domainPath && file_exists($domainPath) ? file_get_contents($domainPath) : '';
            $nluContent = $nluPath && file_exists($nluPath) ? file_get_contents($nluPath) : '';
            $rulesContent = $rulesPath && file_exists($rulesPath) ? file_get_contents($rulesPath) : '';

            // Extraer intents desde domainContent (bloque entre 'intents:' y 'responses:')
            $intentsBlock = "intents:\n";
            if (preg_match('/intents:\s*\n(.*?)\n\s*responses:/ms', $domainContent, $m)) {
                $intentsBlock = "intents:\n" . trim($m[1]) . "\n";
            } elseif (preg_match('/intents:\s*\n(.*)/ms', $domainContent, $m2)) {
                $intentsBlock = "intents:\n" . trim($m2[1]) . "\n";
            }

            // Extraer responses completo
            $responsesBlock = "responses:\n";
            if (preg_match('/responses:\s*\n(.*)/ms', $domainContent, $mr)) {
                $responsesBlock = "responses:\n" . rtrim($mr[1]) . "\n";
            }

            // Extraer nlu entries (después de 'nlu:')
            $nluEntries = "nlu:\n";
            if (preg_match('/nlu:\s*\n(.*)/ms', $nluContent, $mn)) {
                $nluEntries = "nlu:\n" . rtrim($mn[1]) . "\n";
            }

            // Extraer rules entries (después de 'rules:')
            $rulesEntries = "rules:\n";
            if (preg_match('/rules:\s*\n(.*)/ms', $rulesContent, $mr2)) {
                $rulesEntries = "rules:\n" . rtrim($mr2[1]) . "\n";
            }

            // Determinar actions a partir de responses (keys 'utter_...')
            $actions = [];
            if (preg_match_all('/^\s*(utter_[A-Za-z0-9_]+):/m', $responsesBlock, $ma)) {
                foreach ($ma[1] as $a) $actions[] = $a;
            }

            $actionsBlock = "actions:\n";
            if (!empty($actions)) {
                foreach ($actions as $a) {
                    $actionsBlock .= "  - " . $a . "\n";
                }
            } else {
                $actionsBlock .= "\n";
            }

            // Extraer entities si existen en domainContent
            $entitiesBlock = "entities: []\n";
            if (preg_match('/entities:\s*\n(.*?)(?:\n\n|\z)/ms', $domainContent, $me)) {
                $entitiesBlock = "entities:\n" . rtrim($me[1]) . "\n";
            }

            // Reconstruir YAML combinado a partir de la base de datos para evitar duplicados
            $items = Intent::getAllWithExamplesAndResponses();

            // TODO: Leer dinámicamente pipeline/policies desde config.yml en lugar de hardcodear
            // Actualmente incrustamos el contenido de config.yml como strings terminadas en "\n".
            $combined = "pipeline:\n" .
                "- name: WhitespaceTokenizer\n" .
                "- name: RegexFeaturizer\n" .
                "- name: LexicalSyntacticFeaturizer\n" .
                "- name: CountVectorsFeaturizer\n" .
                "- name: CountVectorsFeaturizer\n" .
                "  analyzer: char_wb\n" .
                "  min_ngram: 1\n" .
                "  max_ngram: 4\n" .
                "- name: DIETClassifier\n" .
                "  epochs: 100\n" .
                "- name: EntitySynonymMapper\n" .
                "- name: ResponseSelector\n" .
                "  epochs: 100\n" .
                "- name: FallbackClassifier\n" .
                "  threshold: 0.75\n" .
                "  ambiguity_threshold: 0.15\n\n" .
                "policies:\n" .
                "- name: MemoizationPolicy\n" .
                "- name: RulePolicy\n" .
                "- name: UnexpecTEDIntentPolicy\n" .
                "  max_history: 5\n" .
                "  epochs: 100\n" .
                "- name: TEDPolicy\n" .
                "  max_history: 5\n" .
                "  epochs: 100\n\n";

            // Intents list
            $combined .= "intents:\n";
            foreach ($items as $row) {
                $intent = $row['intent'];
                $safeName = 'dashboard_' . preg_replace('/[^a-z0-9_]/i', '_', strtolower($intent->getNombreIntent()));
                $combined .= "  - " . $safeName . "\n";
            }
            $combined .= "\n";

            // Entities: si existen en base domain, incluirlos; si no, dejar vacío
            if (preg_match('/entities:\s*\n(.*?)(?:\n\n|\z)/ms', $domainContent, $me2)) {
                $combined .= "entities:\n" . rtrim($me2[1]) . "\n\n";
            } else {
                $combined .= "entities: []\n\n";
            }

            $combined .= "slots: {}\n\n";

            // Actions and Responses
            $combinedActions = [];
            $responsesYaml = "responses:\n";
            foreach ($items as $row) {
                $intent = $row['intent'];
                $respuestas = $row['respuestas'];
                $safeName = 'dashboard_' . preg_replace('/[^a-z0-9_]/i', '_', strtolower($intent->getNombreIntent()));
                $utterName = 'utter_' . $safeName;
                $combinedActions[] = $utterName;

                // seleccionar primera activa o primera si no hay activa
                $respText = '';
                foreach ($respuestas as $r) {
                    if (!empty($r['activa'])) { $respText = $r['texto_respuesta']; break; }
                }
                if ($respText === '' && count($respuestas) > 0) $respText = $respuestas[0]['texto_respuesta'];
                $respText = $respText ?: "";

                $responsesYaml .= "  {$utterName}:\n    - text: \"" . str_replace('"', '\\"', $respText) . "\"\n\n";
            }

            $combined .= "actions:\n";
            foreach ($combinedActions as $a) $combined .= "  - " . $a . "\n";
            $combined .= "\nforms: {}\n\ne2e_actions: []\n\n";
            $combined .= $responsesYaml;

            $combined .= "session_config:\n  session_expiration_time: 60\n  carry_over_slots_to_new_session: true\n\n";

            // NLU
            $combined .= "nlu:\n";
            foreach ($items as $row) {
                $intent = $row['intent'];
                $frases = $row['frases'];
                $safeName = 'dashboard_' . preg_replace('/[^a-z0-9_]/i', '_', strtolower($intent->getNombreIntent()));
                $combined .= "- intent: " . $safeName . "\n  examples: |\n";
                if (empty($frases)) {
                    $combined .= "    - " . $intent->getNombreIntent() . "\n";
                } else {
                    foreach ($frases as $f) {
                        $escaped = str_replace('\n', ' ', trim($f));
                        if ($escaped === '') continue;
                        $combined .= "    - " . str_replace("\n", ' ', $escaped) . "\n";
                    }
                }
                $combined .= "\n";
            }

            // Rules
            $combined .= "rules:\n\n";
            foreach ($items as $row) {
                $intent = $row['intent'];
                $safeName = 'dashboard_' . preg_replace('/[^a-z0-9_]/i', '_', strtolower($intent->getNombreIntent()));
                $utterName = 'utter_' . $safeName;
                $combined .= "- rule: respond_{$safeName}\n  steps:\n  - intent: {$safeName}\n  - action: {$utterName}\n\n";
            }

            // Stories placeholder
            $combined .= "stories:\n\n";

            // Escribir archivo combinado temporal (intentar, pero no crítico)
            $dataDir = $nluPath ? dirname($nluPath) : (dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'data');
            if (!is_dir($dataDir)) @mkdir($dataDir, 0755, true);
            $combinedPath = $dataDir . DIRECTORY_SEPARATOR . 'dashboard_combined.yml';
            // Intentamos escribir para facilitar debugging local, pero si falla continuamos usando el contenido en memoria
            try {
                @file_put_contents($combinedPath, $combined);
            } catch (Throwable $e) {
                // ignore write failures, will return diagnostics below
            }

            // Enviar como body crudo (text/yaml)
            $trainUrl = 'http://localhost:5005/model/train';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $trainUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/yaml']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $combined);

            $trainResp = curl_exec($ch);
            $trainErr = curl_error($ch);
            $trainHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            

            if ($trainResp === false) {
                return ['success' => false, 'phase' => 'train', 'error' => 'Curl error', 'curl_error' => $trainErr];
            }

            $trainData = json_decode($trainResp, true);

        // Rasa normalmente devuelve el path al archivo modelo bajo la clave 'model'
        $modelFile = null;
        if (is_array($trainData)) {
            if (!empty($trainData['filename'])) $modelFile = $trainData['filename'];
            elseif (!empty($trainData['model_file'])) $modelFile = $trainData['model_file'];
        }

        // Si no vino model, intentar buscar en la respuesta raw (caso raro)
        if (!$modelFile) {
            // buscar ruta .tar.gz en el texto
            if (preg_match('/\/(?:[\w\-\/]+)models[\w\-\/\.]*\.tar\.gz/', $trainResp, $m)) {
                $modelFile = $m[0];
            } elseif (preg_match('/\/(?:[\w\-\/]+)\/[0-9]{8,}.*\.tar\.gz/', $trainResp, $m2)) {
                $modelFile = $m2[0];
            }
        }

        // Si la respuesta del entrenamiento no contiene 'model' JSON, puede devolver el .tar.gz binario
        $savedModelPath = null;
        if (!$modelFile) {
            // Detectar si la respuesta es un tar.gz (gzip magic bytes 1f 8b) y guardarla en /models
            if (is_string($trainResp) && strlen($trainResp) > 2 && ord($trainResp[0]) === 0x1f && ord($trainResp[1]) === 0x8b) {
                $primaryDir = '/models';
                $projectModels = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'models';
                $usedDir = null;
                // Use /models only if it already exists and is writable (do not attempt to create at root)
                if (is_dir($primaryDir) && is_writable($primaryDir)) {
                    $usedDir = $primaryDir;
                } else {
                    // Fallback to workspace models directory
                    if (!is_dir($projectModels)) @mkdir($projectModels, 0755, true);
                    if (is_dir($projectModels) && is_writable($projectModels)) {
                        $usedDir = $projectModels;
                    } else {
                        // Fallback to system temp dir if project models dir is not writable
                        $tmpdir = sys_get_temp_dir();
                        if (is_dir($tmpdir) && is_writable($tmpdir)) {
                            $usedDir = $tmpdir;
                        } else {
                            return ['success' => false, 'phase' => 'train', 'error' => 'No writable models directory: tried /models, ' . $projectModels . ', and system temp dir'];
                        }
                    }
                }

                $savedModelPath = $usedDir . DIRECTORY_SEPARATOR . 'rasa_model_' . time() . '.tar.gz';
                $w = @file_put_contents($savedModelPath, $trainResp);
                if ($w !== false) {
                    $modelFile = $savedModelPath;
                } else {
                    return ['success' => false, 'phase' => 'train', 'error' => 'Failed to save trained model to ' . $usedDir];
                }
            }
        }

        // Si no hay model todavía, retornar el resultado del entrenamiento
        if (!$modelFile) {
            return ['success' => false, 'phase' => 'train', 'http_code' => $trainHttpCode, 'response' => $trainResp];
        }

        // Hacer PUT a /model para recargar en caliente
        $putUrl = 'http://localhost:5005/model';
        // Rasa expects the model_file path as returned by the training endpoint; avoid prefixing to prevent malformed path
        $payload = ['model_file' => $modelFile];

        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $putUrl);
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload));

        $putResp = curl_exec($ch2);
        $putErr = curl_error($ch2);
        $putHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);

        if ($putResp === false) {
            return ['success' => false, 'phase' => 'put', 'error' => 'Curl error on PUT', 'curl_error' => $putErr, 'trained_model' => $modelFile, 'train_response' => $trainResp];
        }

        

        $putData = json_decode($putResp, true);

        // Éxito: devolver detalles para facilitar debugging en frontend
        return [
            'success' => true,
            'model_file' => $modelFile,
            'train_http' => $trainHttpCode,
            'train_response' => $trainResp,
            'put_http' => $putHttpCode,
            'put_response' => $putResp,
            'combined_path' => $combinedPath
        ];
        
        } catch (Throwable $e) {
            // Restaurar handler antes de retornar
            restore_error_handler();
            return ['success' => false, 'phase' => 'exception', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()];
        } finally {
            restore_error_handler();
        }
    }
}
?>