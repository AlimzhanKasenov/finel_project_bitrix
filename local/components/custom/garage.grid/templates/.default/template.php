<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div>
    <h2>Вкладка «Мой Гараж» для сделки #<?=htmlspecialcharsbx($arParams['DEAL_ID'])?></h2>
    <? if(!empty($arResult['ITEMS'])): ?>
        <ul>
            <? foreach($arResult['ITEMS'] as $item): ?>
                <li>[<?=$item['ID']?>] <?=$item['NAME']?></li>
            <? endforeach; ?>
        </ul>
    <? else: ?>
        <p>Нет данных</p>
    <? endif; ?>
</div>
