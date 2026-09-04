document.addEventListener("DOMContentLoaded", () => {
    const questions = [
        "Ваш ник в игре (укажите точное написание, включая регистр букв):", "Ваш возраст (напишите полный возраст, например: \"мне 18 лет\"):",
        "Опыт игры в Minecraft (как давно играете, какие версии предпочитаете, какие режимы пробовали?):",
        "Играл ли ты раньше на подобных серверах? (Если да, то на каких?):",
        "Чем вам понравился наш сервер HillTop?:",
        "Какие аспекты игры вам интересны больше всего? (строительство, PvP, исследование, крафтинг и т.д.):",
        "Сколько времени вы планируете проводить на сервере в неделю?:",
        "Есть ли у вас какие-либо предпочтения или ожидания от нашего сообщества?:",
        "Готовы ли вы соблюдать правила сервера и участвовать в его жизни?:",
        "Были ли у вас конфликты с другими игроками на предыдущих серверах? (Если да, опишите ситуацию):",
        "Есть ли у вас опыт в организации или участии в ивентах на сервере?:",
        "Какие механики или моды вам нравятся в игре?:",
        "Есть ли у вас какие-либо идеи или предложения по улучшению нашего сервера?:",
        "Как вы узнали о нашем сервере HillTop?:",
        "Ваши контакты в социальных сетях (VK, Discord, etc.):"
    ];

    const formContainer = document.getElementById("question-container");
    const prevBtn = document.getElementById("prev-btn");
    const nextBtn = document.getElementById("next-btn");
    const submitBtn = document.getElementById("submit-btn");
    const notificationBox = document.createElement("div");

    notificationBox.id = "notification-box";
    document.body.appendChild(notificationBox);

    let currentQuestion = 0;
    const answers = {};

    function renderQuestion() {
        formContainer.innerHTML = `
            <label for="answer">${questions[currentQuestion]}</label>
            <textarea id="answer" name="question${currentQuestion + 1}" placeholder="Введите текст здесь..." required></textarea>
        `;

        if (answers[`question${currentQuestion + 1}`]) {
            document.getElementById("answer").value = answers[`question${currentQuestion + 1}`];
        }

        prevBtn.disabled = currentQuestion === 0;
        nextBtn.disabled = currentQuestion === questions.length - 1;
        submitBtn.style.display = currentQuestion === questions.length - 1 ? "block" : "none";
    }

    function showNotification(message, type) {
        notificationBox.innerHTML = `<p class="notification ${type}">${message}</p>`;
        notificationBox.style.display = "block";

        setTimeout(() => {
            notificationBox.style.opacity = "0";
            setTimeout(() => {
                notificationBox.style.display = "none";
                notificationBox.innerHTML = "";
                notificationBox.style.opacity = "1";
            }, 500);
        }, 5000);
    }

    prevBtn.addEventListener("click", () => {
        answers[`question${currentQuestion + 1}`] = document.getElementById("answer").value;
        currentQuestion--;
        renderQuestion();
    });

    nextBtn.addEventListener("click", () => {
        const answer = document.getElementById("answer").value.trim();

        if (!answer) {
            showNotification("⚠️ Пожалуйста, заполните это поле перед продолжением.", "error");
            return;
        }

        answers[`question${currentQuestion + 1}`] = answer;
        currentQuestion++;
        renderQuestion();
    });

    submitBtn.addEventListener("click", async () => {
        const answer = document.getElementById("answer").value.trim();
        if (!answer) {
            showNotification("⚠️ Пожалуйста, заполните это поле перед отправкой.", "error");
            return;
        }

        answers[`question${currentQuestion + 1}`] = answer;

        for (let i = 1; i <= questions.length; i++) {
            if (!answers[`question${i}`] || answers[`question${i}`].trim() === "") {
                showNotification(`⚠️ Пожалуйста, ответьте на все вопросы перед отправкой.`, "error");
                return;
            }
        }

        try {
            const response = await fetch("actions/submit_request.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(answers)
            });

            const result = await response.json();

            if (result.success) {
                showNotification("✅ Заявка успешно отправлена! Ожидайте ответа.", "success");
                document.getElementById("request-form").reset();
            } else {
                showNotification(`❌ ${result.error}`, "error");
            }
        } catch (error) {
            showNotification(`❌ Ошибка отправки данных: ${error.message}`, "error");
        }
    });

    renderQuestion();
});
