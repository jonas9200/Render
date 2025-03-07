<?php
require 'vendor/autoload.php';

use Bluerhinos\phpMQTT;

// Configurações do MQTT
$broker = 'broker.app.wnology.io';
$port = 1883;
$client_id = 'php-mqtt-subscriber';
$topic = 'wnology/67c9969bb3ce04c9e195bf91/state';

// Configurações do MySQL
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'senha_do_banco';
$db_name = 'iot_data';

// Função para conectar ao MySQL
function connect_to_db() {
    global $db_host, $db_user, $db_pass, $db_name;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Erro ao conectar ao MySQL: " . $conn->connect_error);
    }

    return $conn;
}

// Função para salvar dados no MySQL
function save_to_db($payload) {
    $conn = connect_to_db();

    // Decodifica o JSON recebido
    $data = json_decode($payload, true);

    if (isset($data['data'])) {
        $pino = $data['data']['pino'];
        $estado = $data['data']['estado'];

        // Prepara a query SQL
        $stmt = $conn->prepare("INSERT INTO sensor_data (pino, estado) VALUES (?, ?)");
        $stmt->bind_param("is", $pino, $estado);

        // Executa a query
        if ($stmt->execute()) {
            echo "Dados salvos no banco de dados!\n";
        } else {
            echo "Erro ao salvar dados: " . $stmt->error . "\n";
        }

        $stmt->close();
    } else {
        echo "Payload inválido!\n";
    }

    $conn->close();
}

// Função de callback para receber mensagens MQTT
function on_message($topic, $message) {
    echo "Mensagem recebida no tópico $topic: $message\n";
    save_to_db($message);
}

// Conectar ao broker MQTT
$mqtt = new phpMQTT($broker, $port, $client_id);

if ($mqtt->connect()) {
    echo "Conectado ao broker MQTT!\n";

    // Inscrever-se no tópico
    $mqtt->subscribe($topic, 0, 'on_message');

    // Manter a conexão ativa
    while ($mqtt->proc()) {
        // Loop infinito para receber mensagens
    }

    $mqtt->close();
} else {
    echo "Falha ao conectar ao broker MQTT!\n";
}
