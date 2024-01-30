<?php
$host = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "rekrutacja";

// Utwórz połączenie z bazą danych
$connection = new mysqli($host, $dbusername, $dbpassword, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Zapytanie SQL dla unikalnych dat
$sql = "SELECT DISTINCT data FROM zamowienia";
$result = $connection->query($sql);

if (!$result) {
    die("Invalid query: " . $connection->error);
}

// Pobierz daty do wyboru
$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['data'];
}

// Sprawdź, czy formularz został wysłany
if (isset($_POST['submit'])) {
    // Pobierz daty "Od" i "Do" z formularza
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    // Zapytanie SQL z warunkiem zakresu dat
    $sql = "SELECT zamowienia.data, grupy_produktow.nazwa AS kategoria,
                    SUM(zamowienia.ilosc * produkty.cena_netto) AS suma_ceny_netto,   
                    SUM(zamowienia.ilosc * produkty.cena_netto + zamowienia.ilosc * produkty.cena_netto * produkty.vat / 100) AS suma_ceny_brutto
                    FROM zamowienia
                    JOIN produkty ON zamowienia.id_produkt = produkty.id
                    JOIN grupy_produktow ON produkty.id_grupa = grupy_produktow.id
                    WHERE zamowienia.data BETWEEN '$from_date' AND '$to_date'
                    GROUP BY zamowienia.data, grupy_produktow.nazwa
                    ORDER BY zamowienia.data ASC, grupy_produktow.nazwa ASC";

    // Wykonaj zapytanie SQL
    $result = $connection->query($sql);

    if (!$result) {
        die("Invalid query: " . $connection->error);
    }

    // Przygotuj dane do wyświetlenia
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    // Oblicz sumy kwot netto i brutto
    $suma_ceny_netto_total = 0;
    $suma_ceny_brutto_total = 0;

    foreach ($rows as $row) {
        $suma_ceny_netto_total += $row["suma_ceny_netto"];
        $suma_ceny_brutto_total += $row["suma_ceny_brutto"];
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wykres słupkowy</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<header>
    <?php include 'generate-navigation.php'; ?>

    <?php generateNavigation(); ?>
</header>
<h2>Proszę wybrać zakres dat</h2>

<form method="post" action="">
    <label for="from-date">Od</label>
    <select name="from_date" id="from-date" required>
        <?php foreach ($dates as $date) : ?>
            <option value="<?php echo $date; ?>"><?php echo $date; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="to-date">Do</label>
    <select name="to_date" id="to-date" required>
        <?php foreach ($dates as $date) : ?>
            <option value="<?php echo $date; ?>"><?php echo $date; ?></option>
        <?php endforeach; ?>
    </select>

    <input type="submit" name="submit" value="Wyświetl">
</form>

<?php if (isset($rows)) : ?>
    <table>
        <thead>
        <tr>
            <th>Grupa</th>
            <th>Dzień</th>
            <th>Kwota netto</th>
            <th>Kwota brutto</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row) : ?>
            <tr>
                <td><?php echo $row["kategoria"]; ?></td>
                <td><?php echo $row["data"]; ?></td>
                <td><?php echo number_format($row["suma_ceny_netto"], 2); ?> zł</td>
                <td><?php echo number_format($row["suma_ceny_brutto"], 2); ?> zł</td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td><strong>Suma:</strong></td>
            <td></td>
            <td><strong><?php echo number_format($suma_ceny_netto_total, 2); ?> zł</strong></td>
            <td><strong><?php echo number_format($suma_ceny_brutto_total, 2); ?> zł</strong></td>
        </tr>
        </tbody>
    </table>

    <!-- Wykres słupkowy -->
    <canvas id="myChart" width="400" height="400"></canvas>

    <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($rows, 'kategoria')); ?>,
                datasets: [{
                    label: 'Suma netto',
                    data: <?php echo json_encode(array_column($rows, 'suma_ceny_netto')); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                    {
                        label: 'Suma brutto',
                        data: <?php echo json_encode(array_column($rows, 'suma_ceny_brutto')); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    </script>
<?php endif; ?>

</body>

</html>

<?php
// Zamknij połączenie z bazą danych
$connection->close();
?>
