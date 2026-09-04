document.addEventListener("DOMContentLoaded", function() {
    // Получаем элемент с ID "server-ip"
    const serverIpElement = document.getElementById("server-ip");
    if (!serverIpElement) {
        console.error("Элемент с ID 'server-ip' не найден.");
        return;
    }

    // Сохраняем исходный текст (и IP) при загрузке страницы
    const originalText = serverIpElement.textContent.trim();
    const ip = originalText.replace(/^IP:\s*/, ""); // удаляем префикс "IP:" и возможные пробелы

    serverIpElement.addEventListener("click", function() {
        // Всегда копируем сохранённый IP, а не текущее содержимое элемента
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(ip)
                .then(() => {
                    showCopiedMessage();
                })
                .catch((err) => {
                    console.error("Ошибка копирования: ", err);
                });
        } else {
            // Альтернативный способ для старых браузеров
            const tempInput = document.createElement("input");
            tempInput.value = ip;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            showCopiedMessage();
        }
    });

    // Функция для отображения сообщения "Скопировано!" на 2 секунды
    function showCopiedMessage() {
        serverIpElement.textContent = "Скопировано!";
        serverIpElement.style.transition = "color 0.3s, transform 0.3s";
        serverIpElement.style.color = "#28a745";
        serverIpElement.style.transform = "scale(1.1)";

        setTimeout(() => {
            serverIpElement.textContent = originalText;
            serverIpElement.style.color = "";
            serverIpElement.style.transform = "scale(1)";
        }, 2000);
    }
});
