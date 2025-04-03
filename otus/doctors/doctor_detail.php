<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Детальная информация о враче");

$iblockIdDoctors = 16;
$iblockIdProcedures = 17;
$propertyCode = "PROTSEDURY";

$doctorId = intval($_GET['doctor_id']);
if (!$doctorId) {
    echo "<p>ID врача не указан.</p>";
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
    exit;
}

$doctor = \CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => $iblockIdDoctors, 'ID' => $doctorId, 'ACTIVE' => 'Y'],
    false,
    false,
    ['ID', 'NAME', 'DETAIL_TEXT']
)->Fetch();

if (!$doctor) {
    echo "<p>Врач не найден.</p>";
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
    exit;
}

// Получение всех значений свойства "Процедуры" через GetProperty
$procedures = [];
$res = \CIBlockElement::GetProperty(
    $iblockIdDoctors,
    $doctorId,
    [],
    ['CODE' => $propertyCode]
);
while ($property = $res->Fetch()) {
    if ($property['VALUE']) {
        $procedures[] = $property['VALUE'];
    }
}

// Извлечение данных привязанных процедур по их ID
$procedureNames = [];
if (!empty($procedures)) {
    $res = \CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblockIdProcedures, 'ID' => $procedures],
        false,
        false,
        ['ID', 'NAME']
    );
    while ($procedure = $res->Fetch()) {
        $procedureNames[] = $procedure['NAME'];
    }
}
?>

<div class="doctor-detail">
    <h1><?= htmlspecialchars($doctor['NAME']); ?></h1>
    <p><?= htmlspecialchars($doctor['DETAIL_TEXT']); ?></p>
    <h2>Процедуры:</h2>
    <?php if (!empty($procedureNames)): ?>
        <ul>
            <?php foreach ($procedureNames as $procedureName): ?>
                <li><?= htmlspecialchars($procedureName); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Процедуры не указаны.</p>
    <?php endif; ?>
    <br>
    <a href="index.php" class="back-button">Назад</a>
</div>

<style>
    .back-button {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    .back-button:hover {
        background-color: #0056b3;
    }
</style>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
