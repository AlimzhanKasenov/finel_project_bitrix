document.addEventListener('DOMContentLoaded', function () {
    // Функция преобразования даты в формат DD.MM.YYYY HH:mm:ss
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Месяцы начинаются с 0
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = '00'; // Секунды можно оставить всегда 00
        return `${day}.${month}.${year} ${hours}:${minutes}:${seconds}`;
    }

    // Обработчик клика на элемент
    document.querySelectorAll('.open-modal').forEach(function (element) {
        element.addEventListener('click', function (event) {
            event.preventDefault();

            const procedureId = this.getAttribute('data-id');

            // Открываем модальное окно
            const modal = document.getElementById('procedureModal');
            if (modal) {
                modal.style.display = 'block';

                // Устанавливаем ID процедуры в скрытое поле формы
                const procedureField = document.getElementById('procedureId');
                if (procedureField) {
                    procedureField.value = procedureId;
                } else {
                    console.error('Поле procedureId не найдено в форме!');
                }
            } else {
                console.error('Модальное окно с ID procedureModal не найдено!');
            }
        });
    });

    // Закрытие модального окна
    const closeModalButton = document.getElementById('closeModal');
    if (closeModalButton) {
        closeModalButton.addEventListener('click', function () {
            const modal = document.getElementById('procedureModal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Отправка формы
    const procedureForm = document.getElementById('procedureForm');
    if (procedureForm) {
        procedureForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const procedureId = document.getElementById('procedureId').value.trim();
            const name = document.getElementById('patientName').value.trim();
            let date = document.getElementById('appointmentDate').value.trim();

            if (!procedureId || !name || !date) {
                alert('Заполните все поля!');
                return;
            }

            // Преобразуем дату в нужный формат
            date = formatDate(date);

            // Проверяем занятость времени
            fetch('/local/ajax/checkTimeAvailability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ procedureId, date }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'error') {
                        alert(data.message);
                    } else {
                        // Если время доступно, отправляем данные на создание
                        fetch('/local/ajax/addProcedure.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ procedureId, name, date }),
                        })
                            .then(response => response.json())
                            .then(data => {
                                alert(data.message);
                                if (data.status === 'success') {
                                    const modal = document.getElementById('procedureModal');
                                    if (modal) {
                                        modal.style.display = 'none';
                                    }
                                }
                            })
                            .catch(error => {
                                alert('Ошибка: ' + error.message);
                            });
                    }
                })
                .catch(error => {
                    alert('Ошибка проверки времени: ' + error.message);
                });
        });
    }
});
