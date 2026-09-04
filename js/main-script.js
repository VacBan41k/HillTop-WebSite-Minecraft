document.addEventListener("DOMContentLoaded", () => {
    const burgerMenu = document.getElementById("burger-menu");
    const navMenu = document.getElementById("nav-menu");
    const loginButton = document.getElementById("login-button");
    const loginModal = document.getElementById("login-modal");
    const userAvatar = document.getElementById("user-avatar");
    const userMenu = document.getElementById("user-menu");

    if (burgerMenu && navMenu) {
        // Переключение классов для анимации и показа меню
        burgerMenu.addEventListener("click", () => {
            navMenu.classList.toggle("active");
            burgerMenu.classList.toggle("active");
        });

        // Закрытие меню при клике вне его области
        document.addEventListener("click", (event) => {
            if (!event.target.closest("#burger-menu") && !event.target.closest("#nav-menu")) {
                navMenu.classList.remove("active");
                burgerMenu.classList.remove("active");
            }
        });
    } else {
        console.warn("Элементы burger-menu или nav-menu отсутствуют на странице.");
    }

    if (loginButton && loginModal) {
        // Открытие модального окна
        loginButton.addEventListener("click", (e) => {
            e.preventDefault();
            loginModal.classList.add("show");
        });

        // Закрытие модального окна при клике вне его
        window.addEventListener("click", (e) => {
            if (e.target === loginModal) {
                loginModal.classList.remove("show");
            }
        });
    } else {
        console.warn("Элементы login-button или login-modal отсутствуют на странице.");
    }

    if (userAvatar && userMenu) {
        userAvatar.addEventListener("click", () => {
            userMenu.style.display = userMenu.style.display === "flex" ? "none" : "flex";
        });

        document.addEventListener("click", (e) => {
            if (!e.target.closest(".user-info")) {
                userMenu.style.display = "none";
            }
        });
    } else {
        console.error("Элементы user-avatar или user-menu отсутствуют в DOM.");
    }
});
