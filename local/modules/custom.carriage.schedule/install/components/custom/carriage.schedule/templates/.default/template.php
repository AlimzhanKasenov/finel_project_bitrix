<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<table class="carriage-schedule">
    <thead>
        <tr>
            <th>Номер</th>
            <th>Тип</th>
            <th>Станция отправления</th>
            <th>Станция прибытия</th>
            <th>Время отправления</th>
            <th>Время прибытия</th>
            <th>Статус</th>
        </tr>
    </thead>
    <tbody id="carriage-table-body">
        <?php foreach ($arResult['CARRIAGES'] as $carriage): ?>
            <tr>
                <td><?= htmlspecialchars($carriage['NUMBER']) ?></td>
                <td><?= htmlspecialchars($carriage['TYPE']) ?></td>
                <td><?= htmlspecialchars($carriage['DEPARTURE_STATION']) ?></td>
                <td><?= htmlspecialchars($carriage['ARRIVAL_STATION']) ?></td>
                <td><?= htmlspecialchars($carriage['DEPARTURE_TIME']) ?></td>
                <td><?= htmlspecialchars($carriage['ARRIVAL_TIME']) ?></td>
                <td class="status"><?= htmlspecialchars($carriage['STATUS']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
		console.log("DOM полностью загружен!");
    setInterval(function () {
        console.log("Запрос отправлен...");

        fetch('/local/components/custom/carriage.schedule/ajax_update.php')
            .then(response => {
                console.log("Ответ получен:", response);
                return response.text();
            })
            .then(data => {
                console.log("Получены данные:", data);
                document.getElementById('carriage-table-body').innerHTML = data;
            })
            .catch(error => console.error('Ошибка обновления таблицы:', error));
    }, 15000);
</script>