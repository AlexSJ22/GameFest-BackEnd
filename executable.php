<?php
require_once 'functions.php';

$conn = conectarBD();

// Vamos a ponerle contraseña "admin" al usuario admin
$passAdmin = password_hash('admin', PASSWORD_BCRYPT);

// Vamos a ponerle contraseña "alumno" al usuario alumno
$passAlumno = password_hash('alumno', PASSWORD_BCRYPT);

// Actualizamos ADMIN
$sql1 = "UPDATE users SET password_hash = ? WHERE username = 'admin'";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("s", $passAdmin);
if ($stmt1->execute()) {
    echo "✅ Usuario 'admin' actualizado. Nueva contraseña: 'admin'<br>";
} else {
    echo "❌ Error actualizando admin<br>";
}

// Actualizamos ALUMNO
$sql2 = "UPDATE users SET password_hash = ? WHERE username = 'alumno'";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $passAlumno);
if ($stmt2->execute()) {
    echo "✅ Usuario 'alumno' actualizado. Nueva contraseña: 'alumno'<br>";
} else {
    echo "❌ Error actualizando alumno<br>";
}

$conn->close();
?>