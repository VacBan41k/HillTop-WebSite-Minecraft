document.addEventListener("DOMContentLoaded", () => {
    const requestButton = document.getElementById("request-button");
    const loginModal = document.getElementById("login-modal");

    if (requestButton && loginModal) {
        requestButton.addEventListener("click", (e) => {
            const isAuth = requestButton.getAttribute("data-auth") === "true";

            if (!isAuth) {
                e.preventDefault();
                loginModal.classList.add("show");
            }
        });

        // Закрытие модального окна при клике вне его
        window.addEventListener("click", (e) => {
            if (e.target === loginModal) {
                loginModal.classList.remove("show");
            }
        });
    }
});