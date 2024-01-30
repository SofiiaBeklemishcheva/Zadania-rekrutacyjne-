<?php
$host = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "klient";

$connection = new mysqli($host, $dbusername, $dbpassword, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Dodawanie klienta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['imie']) && isset($_POST['nazwisko'])) {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];

    // Sprawdź unikalność danych przed dodaniem do bazy danych
    $check_query = "SELECT * FROM dates WHERE name = ? AND surname = ?";
    $stmt_check = $connection->prepare($check_query);
    $stmt_check->bind_param("ss", $imie, $nazwisko);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "Klient o podanych danych już istnieje";
        echo '<a href="http://localhost/Zadanie2/managing-database.php">Wyświetl bazę danych</a>';
    } else {
        $insert_query = "INSERT INTO dates (name, surname) VALUES (?, ?)";
        $stmt_insert = $connection->prepare($insert_query);
        $stmt_insert->bind_param("ss", $imie, $nazwisko);

        if ($stmt_insert->execute()) {
            echo "Nowy klient został dodany";
            echo '<a href="http://localhost/Zadanie2/managing-database.php">Wyświetl zmodyfikowaną bazę danych</a>';
        } else {
            echo "Błąd: " . $insert_query . "<br>" . $connection->error;
        }
        $stmt_insert->close();
    }
}

// Usuwanie klienta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['usunKlient'])) {
    $idKlientaDoUsuniecia = $_POST['usunKlient'];


    $delete_query = "DELETE FROM dates WHERE name = ? AND surname = ?";
    $stmt_delete = $connection->prepare($delete_query);
    $stmt_delete->bind_param("ss", $imie, $nazwisko);


    if ($stmt_delete->execute()) {
        echo "Klient został usunięty";
        echo '<a href="http://localhost/Zadanie2/managing-database.php">Wyświetl zmodyfikowaną bazę danych</a>';
    } else {
        echo "Błąd podczas usuwania klienta: " . $stmt_delete->error;
        echo '<a href="http://localhost/Zadanie2/managing-database.php">Spróbuj ponownie</a>';
    }
    $stmt_delete->close();
}

$connection->close();
?>

