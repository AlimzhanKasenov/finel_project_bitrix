<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Список врачей");

// ID инфоблока врачей и свойство с привязкой к процедурам
$iblockIdDoctors = 16; // Укажите ваш ID инфоблока
$propertyProcedures = "PROC_IDS_MULTI"; // Код свойства с привязкой к процедурам

// Получение данных о врачах
$doctors = [];
$res = \CIBlockElement::GetList(
    ['NAME' => 'ASC'], // Сортировка
    ['IBLOCK_ID' => $iblockIdDoctors, 'ACTIVE' => 'Y'], // Фильтр
    false,
    false,
    ['ID', 'NAME', "PROPERTY_{$propertyProcedures}"]
);
while ($doctor = $res->GetNext()) {
    // Получение названий процедур
    $procedures = [];
    if (!empty($doctor["PROPERTY_{$propertyProcedures}_VALUE"])) {
        $procedureRes = \CIBlockElement::GetList(
            [],
            ['ID' => $doctor["PROPERTY_{$propertyProcedures}_VALUE"]],
            false,
            false,
            ['ID', 'NAME']
        );
        while ($procedure = $procedureRes->GetNext()) {
            $procedures[] = $procedure['NAME'];
        }
    }

    $doctors[] = [
        'ID' => $doctor['ID'],
        'NAME' => $doctor['NAME'],
        'PROCEDURES' => $procedures,
    ];
}
?>

<div class="doctors-list">
    <?php foreach ($doctors as $doctor): ?>
        <div class="doctor-card">
            <h3><?= $doctor['NAME']; ?></h3>
            <p>ID врача: <?= $doctor['ID']; ?></p>
            <a href="doctor_detail.php?doctor_id=<?= $doctor['ID']; ?>">Подробнее</a>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .doctors-list {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .doctor-card {
        border: 1px solid #ccc;
        padding: 15px;
        width: 300px;
    }
</style>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
