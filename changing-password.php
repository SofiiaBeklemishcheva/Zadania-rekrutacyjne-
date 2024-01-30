<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Walidacja hasła
    if (strlen($new_password) < 5 || !preg_match("/[0-9]+/", $new_password) || !preg_match("/[A-Z]+/", $new_password)) {
        echo "Nowe hasło musi mieć co najmniej 5 znaków, zawierać co najmniej jedną liczbę oraz jedną dużą literę.";
        echo '<br>';
        echo '<a href="http://localhost/change-password.php">Spróbuj jeszcze raz</a>';
        exit();
    }

    $host = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $dbname = "auth";

    // Establishing connection
    $conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "SELECT * FROM login WHERE username=? AND password=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $old_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        // Updating password using prepared statement
        $update_query = "UPDATE login SET password=? WHERE username=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ss", $new_password, $username);
        if ($stmt->execute()) {
            header("Location: options.php");
            exit();
        } else {
            echo "Error updating password";
        }
    } else {
        echo "Nieprawidłowa nazwa użytkownika lub stare hasło.";
        echo '<br>';
        echo '<a href="http://localhost/change-password.php">Spróbuj jeszcze raz</a>';
    }

    $conn->close();
}
?>


