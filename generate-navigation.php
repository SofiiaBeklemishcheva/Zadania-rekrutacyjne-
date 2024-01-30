<?php
function generateNavigation() {
    echo '<nav>';
    echo '<a href="http://localhost/change-password.php" class="application-function-option">';
    echo '<button>Zmiana hasła użytkownika</button>';
    echo '</a>';
    echo '<a href="http://localhost/sales-report.php" class="application-function-option">';
    echo '<button>Raport sprzedaży według produktów</button>';
    echo '</a>';
    echo '<a href="http://localhost/sales-statement.php" class="application-function-option">';
    echo '<button>Roczne zestawienie sprzedaży</button>';
    echo '</a>';
    echo '</nav>';
}
?>
