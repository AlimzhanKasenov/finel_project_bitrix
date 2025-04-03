<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Установка приложения</title>
    <script src="//api.bitrix24.com/api/v1/"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; text-align: center; }
        #status { margin: 20px; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
<h2>🚀 Установка приложения «Последняя коммуникация»</h2>
<p>Приложение обрабатывает событие <b>OnCrmActivityAdd</b> и записывает время в поле <b>UF_CRM_1738909589</b> у Контакта.</p>
<div id="status">⏳ Готово к работе...</div>

<script>
    BX24.init(function() {
        document.getElementById('status').textContent = "✅ Приложение установлено!";
        // Можно вызвать BX24.installFinish(); если это коробочное приложение
        BX24.installFinish();
    });
</script>
</body>
</html>
