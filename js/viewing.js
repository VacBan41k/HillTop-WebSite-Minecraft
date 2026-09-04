document.addEventListener("DOMContentLoaded", function () {
    // Функция для построения строки таблицы на основе объекта заявки
    function buildRow(app) {
        let row = `<tr id="app-${app.id}">`;
        columns.forEach(function(col) {
            let value = (app[col] !== undefined && app[col] !== null) ? app[col] : "";
            row += `<td>${value}</td>`;
        });
        // Добавляем ячейку с кнопками действий
        row += `<td>
                    <button class="accept-btn" data-id="${app.id}">Принять</button>
                    <button class="reject-btn" data-id="${app.id}">Отклонить</button>
                </td></tr>`;
        return row;
    }

    // Функция загрузки активных заявок
    function loadApplications() {
        fetch("actions/get_applications.php?" + new Date().getTime())
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById("applications-body");
                if (!tableBody) return;
                tableBody.innerHTML = "";
                if (!Array.isArray(data) || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="${columns.length + 1}">Нет активных заявок</td></tr>`;
                    return;
                }
                data.forEach(app => {
                    tableBody.innerHTML += buildRow(app);
                });
                addActionListeners();
            })
            .catch(error => console.error("Ошибка загрузки заявок:", error));
    }

    // Функция загрузки истории заявок
    function loadHistory() {
        fetch("actions/get_history.php?" + new Date().getTime())
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById("history-body");
                if (!tableBody) return;
                tableBody.innerHTML = "";
                if (!Array.isArray(data) || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="${columns.length}">История пуста</td></tr>`;
                    return;
                }
                data.forEach(app => {
                    let row = `<tr class="${app.status}">`;
                    columns.forEach(function(col) {
                        let value = (app[col] !== undefined && app[col] !== null) ? app[col] : "";
                        row += `<td>${value}</td>`;
                    });
                    row += `</tr>`;
                    tableBody.innerHTML += row;
                });
            })
            .catch(error => console.error("Ошибка загрузки истории:", error));
    }

    // Функция отправки запроса на изменение статуса заявки
    function processApplication(id, status) {
        let body = "id=" + encodeURIComponent(id) + "&status=" + encodeURIComponent(status);
        if (status === "rejected") {
            let comment = prompt("Введите причину отказа:");
            if (comment === null) return;
            body += "&comment=" + encodeURIComponent(comment);
        }
        fetch("actions/process_application.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: body
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadApplications();
                    loadHistory();
                } else {
                    alert("Ошибка: " + data.error);
                }
            })
            .catch(error => console.error("Ошибка обработки заявки:", error));
    }

    // Привязка обработчиков для кнопок действий в таблице активных заявок
    function addActionListeners() {
        document.querySelectorAll(".accept-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                processApplication(this.dataset.id, "accepted");
            });
        });
        document.querySelectorAll(".reject-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                processApplication(this.dataset.id, "rejected");
            });
        });
    }

    // Функция фильтрации истории заявок по статусу
    window.filterHistory = function() {
        const filterValue = document.getElementById("filter").value;
        const rows = document.querySelectorAll("#history-body tr");
        rows.forEach(row => {
            if (filterValue === "all") {
                row.style.display = "";
            } else {
                // Ожидается, что строка имеет класс, равный значению status
                if (row.classList.contains(filterValue)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        });
    }

    // Функция переключения вкладок
    window.switchTab = function(tabName) {
        document.querySelectorAll(".tab-content").forEach(function(content) {
            content.style.display = "none";
        });
        document.querySelectorAll(".tab-button").forEach(function(btn) {
            btn.classList.remove("active");
        });
        document.getElementById(tabName).style.display = "block";
        document.querySelector(".tab-button[data-tab='" + tabName + "']").classList.add("active");
        if (tabName === "applications") {
            loadApplications();
        } else if (tabName === "history") {
            loadHistory();
        }
    }

    // Первая загрузка активных заявок
    loadApplications();
});
