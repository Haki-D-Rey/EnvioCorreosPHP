<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Requiere la autoloader de PHPMailer
require 'vendor/autoload.php';

// Intenta conectarte a la base de datos
$conexion = @mysqli_connect('c98055.sgvps.net', 'udeq5kxktab81', 'clmjsfgcrt5m', 'db2gdg4nfxpgyk');
if (@mysqli_connect_errno()) {
    throw new Exception("Error de conexión a la base de datos: " . mysqli_connect_errno() . " : " . mysqli_connect_error());
}

// Construir la consulta SQL para buscar participantes con coincidencia casi exacta en apellidos y nombres
$query = $conexion->prepare(
    "SELECT correo
    FROM `wp_eiparticipante` tb
    WHERE tb.estaInscrito = 1 AND tb.correo <> ''AND tb.id_tipo_institucion_oficial= 13 AND tb.evento IN( 'XXI PRECONGRESO CIENTÍFICO MÉDICO', 'XXI CONGRESO CIENTÍFICO MÉDICO', 'XXI PRECONGRESO y CONGRESO CIENTÍFICO MÉDICO' ) AND tb.id_participante >= 1030"
);

// Ejecutar la consulta
$query->execute();
// Obtener resultados
$result = $query->get_result();

if ($result->num_rows > 0) {
    // Obtener los datos de la consulta y agregarlos al arreglo de datos en la respuesta
    $response['success'] = true;
    $response['message'] = 'Operación exitosa';
    while ($row = $result->fetch_assoc()) {
        $response['data'][] = $row['correo'];
    }
}

var_dump($response);

// Configuración del servidor SMTP
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.elasticemail.com';  // Configura el servidor SMTP
$mail->SMTPAuth = true;
$mail->Username = 'gti@hospitalmilitar.com.ni';   // Correo electrónico de autenticación SMTP
$mail->Password = '8DA58248225E3D6BD9D5B688400D2AC7B18F';   // Contraseña de autenticación SMTP
$mail->SMTPSecure = 'ssl';   // Habilita el cifrado TLS
$mail->Port = 465;   // Puerto SMTP
$mail->CharSet = 'UTF-8';

// 'Host' => 'smtp.elasticemail.com',
// 'SMTPAuth' => true,
// 'Username' => 'gti@hospitalmilitar.com.ni',
// 'Password' => '8DA58248225E3D6BD9D5B688400D2AC7B18F',
// 'SMTPSecure' => 'ssl',  // Cambiado a SSL según la documentación de Elastic Email
// 'Port' => 465  


$correos_validos = array();
$correos_invalidos = array();

$expresion_regular = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/i';

foreach ($response['data'] as $correo) {
    // Eliminar espacios en blanco al principio y al final
    $correo_sin_espacios = trim($correo);
    // Eliminar el punto al final si existe
    $correo_valid = rtrim($correo_sin_espacios, '.');

    // Validar el correo electrónico sin puntos y espacios en blanco
    if (preg_match($expresion_regular, $correo) && $mail->validateAddress($correo_valid, null)) {
        $correos_validos[] = $correo_valid;
    } else {
        $correos_invalidos[] = $correo_valid;
    }
}

// Agregar un valor al final del arreglo $correos_validos
$valor_extra = 'cesar.cuadra@hospitalmilitar.com.ni';

// Opción 1: Utilizando array_push()
array_push($correos_validos, $valor_extra);

// Configuración del correo
$mail->setFrom('congresomedico@hospitalmilitar.com.ni', '1er Congreso Internacional de Enfermería');
// Agregar destinatarios
foreach ($correos_validos as $toEmail) {
    $mail->addAddress($toEmail);
}

$mail->isHTML(true);   // Establece el formato de correo HTML

// Contenido de la plantilla HTML
$htmlContent = file_get_contents('plantilla-correo/template_email_body_verify_inscripcion.html');
$mail->msgHTML($htmlContent);
// Configura el asunto y el cuerpo del correo
$mail->Subject = 'Mensaje de Verificación de Inscritos';

// Intenta enviar el correo
try {
    $mail->send();
    echo 'Correo enviado correctamente';
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}

// Verifica la conexión
if ($conexion->connect_error) {
    throw new Exception("Error de conexión a la base de datos: " . $conexion->connect_error);
}


// Lista de nombres
$correos = $response['data'];
