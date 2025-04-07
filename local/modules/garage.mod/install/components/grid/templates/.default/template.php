<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<div>
    <h2>Гараж клиента</h2>

    <?php if (!empty($arResult['CARS'])): ?>
        <table border="1" cellpadding="5" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Модель</th>
                <th>Год</th>
                <th>Цвет</th>
                <th>Пробег</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($arResult['CARS'] as $car): ?>
                <tr class="car-row" data-id="<?= (int)$car['ID'] ?>" style="cursor: pointer;">
                    <td><?= htmlspecialcharsbx($car['ID']) ?></td>
                    <td><?= htmlspecialcharsbx($car['TITLE']) ?></td>
                    <td><?= htmlspecialcharsbx($car['MODEL']) ?></td>
                    <td><?= htmlspecialcharsbx($car['YEAR']) ?></td>
                    <td><?= htmlspecialcharsbx($car['COLOR']) ?></td>
                    <td><?= htmlspecialcharsbx($car['MILEAGE']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Автомобили не найдены.</p>
    <?php endif; ?>
</div>

<script>
    BX.ready(function () {
        // Навешиваем обработчики на каждую строку автомобиля
        document.querySelectorAll('.car-row').forEach(function (row) {
            row.addEventListener('click', function () {
                const carId = this.dataset.id;
                console.log('Клик по машине ID: ' + carId);

                // Выполняем AJAX-запрос к компоненту
                BX.ajax.runComponentAction('custom:grid', 'getDealsByCar', {
                    mode: 'class',
                    data: { carId: carId }
                }).then(function (response) {
                    const deals = response.data;
                    let html = '<h3>Сделки по авто #' + carId + '</h3>';

                    if (deals.length > 0) {
                        html += '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
                        html += '<thead><tr><th>ID</th><th>Дата</th><th>Цена</th><th>Вид работ</th></tr></thead><tbody>';

                        deals.forEach(function (deal) {
                            html += '<tr>';
                            html += '<td>' + BX.util.htmlspecialchars(deal.ID) + '</td>';
                            html += '<td>' + BX.util.htmlspecialchars(deal.DATE) + '</td>';
                            html += '<td>' + BX.util.htmlspecialchars(deal.PRICE) + '</td>';
                            html += '<td>' + BX.util.htmlspecialchars(deal.WORK_TYPE) + '</td>';
                            html += '</tr>';
                        });

                        html += '</tbody></table>';
                    } else {
                        html += '<p>Сделки не найдены</p>';
                    }

                    // Создаём и отображаем popup со сделками
                    const popup = new BX.PopupWindow('deal_popup_' + carId, null, {
                        content: html,
                        width: 600,
                        closeIcon: true,
                        titleBar: 'Последние сделки',
                        overlay: true,
                        autoHide: true,
                        buttons: [
                            new BX.PopupWindowButton({
                                text: 'OK',
                                className: 'popup-window-button-accept',
                                events: {
                                    click: function () {
                                        popup.close();
                                    }
                                }
                            })
                        ]
                    });

                    popup.show();
                }, function (error) {
                    alert('Ошибка загрузки сделок. Подробнее в консоли.');
                    console.error('AJAX error:', error);
                });
            });
        });
    });
</script>
