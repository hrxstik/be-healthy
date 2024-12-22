<?php
include __DIR__ . '/config.php';
/**
 * @global mysqli $connection
 */

$query = "SELECT full_name as 'ФИО', specialty as 'Специальность' FROM doctors";
try {
    $result = $connection->query($query);
}
catch (mysqli_sql_exception $e) {
    $error = $e->getMessage();
    echo $error;
}
if ($result->num_rows > 0) { ?>
    <table>
        <tr><th>ФИО</th>
            <th>Специальность</th>
        </tr> <?php
        while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['ФИО']) ?></td>
                <td><?= htmlspecialchars($row['Специальность']) ?></td>
            </tr>
        <?php } ?>
    </table> <?php
} else {
    ?> <p>Нет данных о врачах</p> <?php
}

$connection->close();