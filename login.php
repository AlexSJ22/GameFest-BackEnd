<?PHP
session_start();

$user = loginUsuario($_POST['email'], $_POST['password']);

if ($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role']; // ADMIN o USER

    header("Location: index.php");
    exit;
} else {
    echo "Credenciales incorrectas";
}

?>