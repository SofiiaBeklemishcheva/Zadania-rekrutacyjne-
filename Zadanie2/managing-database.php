<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zarządzanie bazą danych</title>
</head>
<body>
<h2>Formularz do zarządzania bazą danych klientów</h2>

<form id="klientForm" action="data-base-connection.php" method="post">
    <label for="imie">Imię:</label><br>
    <input type="text" id="imie" name="imie"><br><br>
    <label for="nazwisko">Nazwisko:</label><br>
    <input type="text" id="nazwisko" name="nazwisko"><br><br>
    <button type="submit">Dodaj klienta</button>
</form>


<h2>Twoja baza danych</h2>
<table id="dates">
    <thead>
    <tr>
        <th>Imię</th>
        <th>Nazwisko</th>
    </tr>
    </thead>
    <tbody id="klienciBody">
    <?php
    $host = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $dbname = "klient";

    $connection = new mysqli($host, $dbusername, $dbpassword, $dbname);
    $result = $connection->query("SELECT * FROM dates");

    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row['name']."</td><td>".$row['surname']."</td></tr>";
    }

    // Zamknij połączenie z bazą danych
    $connection->close();
    ?>
    </tbody>
</table>

<br><br>

<h2>Usuwanie danych klienta</h2>
<form id="deleteForm" action="data-base-connection.php" method="post">
    <label for="usunKlient">Wybierz klienta, czyje dane chciałbyś usunąć:</label>
    <select id="usunKlient" name="usunKlient">
        <?php
        // Ponownie łączymy się z bazą danych, aby pobrać klientów do usunięcia
        $connection = new mysqli($host, $dbusername, $dbpassword, $dbname);
        $result = $connection->query("SELECT * FROM dates");

        while ($row = $result->fetch_assoc()) {
            echo "<option value='".$row['id']."'>".$row['name']." ".$row['surname']."</option>";
        }

        // Zamknij połączenie z bazą danych
        $connection->close();
        ?>
    </select>
    <button type="submit">Usuń</button>
</form>

</body>
</html>

