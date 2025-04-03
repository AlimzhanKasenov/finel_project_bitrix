<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="currency-selector">
    <h3>Курс валюты</h3>
    <p><strong>Валюта:</strong> <?= htmlspecialchars($arResult['CURRENCY_CODE']) ?></p>
    <p><strong>Курс:</strong> <?= number_format($arResult['CURRENCY_RATE'], 2) ?> (по отношению к <?= CCurrency::GetBaseCurrency() ?>)</p>
</div>
