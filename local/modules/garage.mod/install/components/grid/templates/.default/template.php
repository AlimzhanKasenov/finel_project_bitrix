<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?php
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt';
file_put_contents($logFile, "=== [template.php] Загрузка шаблона ===\n", FILE_APPEND);
file_put_contents($logFile, "[template.php] arResult: " . print_r($arResult, true) . "\n", FILE_APPEND);
?>

<div>
    <h2>Список данных из инфоблока</h2>
    <?php if (!empty($arResult['ITEMS'])): ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Процедуры</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($arResult['ITEMS'] as $id => $item): ?>
                <tr>
                    <td><?= htmlspecialcharsbx($id) ?></td>
                    <td><?= htmlspecialcharsbx($item['NAME']) ?></td>
                    <td><?= !empty($item['PROCEDURES']) ? implode(', ', array_map('htmlspecialcharsbx', $item['PROCEDURES'])) : 'Не указано' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Данные отсутствуют.</p>
    <?php endif; ?>
</div>
