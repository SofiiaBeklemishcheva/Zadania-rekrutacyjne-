<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sales Statement Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
    <?php include 'generate-navigation.php'; ?>

    <?php generateNavigation(); ?>
</header>
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

$sql = "SELECT YEAR(zamowienia.data) AS rok, 
       grupy_produktow.nazwa AS kategoria,
       SUM(zamowienia.ilosc * produkty.cena_netto) AS suma_ceny_netto,   
       SUM(zamowienia.ilosc * produkty.cena_netto + zamowienia.ilosc * produkty.cena_netto * produkty.vat / 100) AS suma_ceny_brutto
FROM zamowienia
JOIN produkty ON zamowienia.id_produkt = produkty.id
JOIN grupy_produktow ON produkty.id_grupa = grupy_produktow.id
WHERE zamowienia.data IS NOT NULL
GROUP BY YEAR(zamowienia.data), grupy_produktow.nazwa
ORDER BY YEAR(zamowienia.data) ASC, grupy_produktow.nazwa ASC;
";

$result = $connection->query($sql);

if (!$result) {
    die("Invalid query: " . $connection->error);
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
?>

<table border="1">
    <?php
    $unique_years = []; // Tablica przechowująca unikalne lata
    foreach ($rows as $row) {
        $unique_years[$row['rok']] = true; // Dodanie roku do tablicy unikalnych lat
    }
    $unique_years = array_keys($unique_years); // Pobranie kluczy (unikalnych lat)

    // Generowanie nagłówków kolumn
    echo "<tr>";
    echo "<th rowspan='2'>Grupa</th>"; // Nagłówek dla kategorii

    // Nagłówki kolumn dla lat
    foreach ($unique_years as $year) {
        echo "<th colspan='2'>$year</th>";
    }
    echo "</tr>";

    echo "<tr>";
    // Nagłówki kolumn dla netto i brutto
    for ($i = 0; $i < count($unique_years); $i++) {
        echo "<td>Netto</td>";
        echo "<td>Brutto</td>";
    }
    echo "</tr>";

    // Przygotowanie danych grupując je według kategorii
    $categories = [];
    foreach ($rows as $row) {
        $netto = number_format($row["suma_ceny_netto"], 2) . ' zł';
        $brutto = number_format($row["suma_ceny_brutto"], 2) . ' zł';

        $categories[$row['kategoria']][$row['rok']] = array(
            "netto" => $netto,
            "brutto" => $brutto
        );
    }

    // Wyświetlanie danych
    foreach ($categories as $category => $years) {
        echo "<tr>";
        echo "<td>$category</td>"; // Nazwa kategorii

        // Wyświetlanie danych dla każdego roku
        foreach ($unique_years as $year) {
            if (isset($years[$year])) {
                echo "<td>{$years[$year]['netto']}</td>";
                echo "<td>{$years[$year]['brutto']}</td>";
            } else {
                echo "<td></td><td></td>"; // Puste komórki, jeśli brak danych dla roku
            }
        }
        echo "</tr>";
    }
    ?>
</table>

<?php
$connection->close();
?>

<!-- Kontener, gdzie będzie wyświetlony wykres -->
<canvas id="myChart" width="300" height="200"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var data = {
        labels: <?php echo json_encode($unique_years); ?>,
        datasets: [
            <?php
            // Przygotowanie danych
            $categories = [];
            foreach ($rows as $row) {
                $category = $row['kategoria'];
                $year = $row['rok'];
                $netto = $row['suma_ceny_netto'];

                if (!isset($categories[$category])) {
                    $categories[$category] = array_fill(0, count($unique_years), null);
                }

                $index = array_search($year, $unique_years);
                $categories[$category][$index] = $netto;
            }

            // Konfiguracja kategorii dla wykresu
            foreach ($categories as $category => $values) {
            ?>
            {
                label: '<?php echo $category; ?>',
                data: <?php echo json_encode($values); ?>,
                fill: false,
                borderColor: getRandomColor(),
                borderWidth: 2
            },
            <?php } ?>
        ]
    };

    // Konfiguracja wykresu
    var options = {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }],
            xAxes: [{
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 20
                }
            }]
        }
    };


    // Utworzenie nowego wykresu
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: data,
        options: options
    });

    // Funkcja generująca losowy kolor
    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

</script>

</body>
</html>
