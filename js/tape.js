document.addEventListener('DOMContentLoaded', () => {
    // Обработка кнопок лайков и дизлайков
    document.querySelectorAll('.like-button, .dislike-button').forEach(button => {
        button.addEventListener('click', function () {
            const postId = this.getAttribute('data-post-id');
            const action = this.classList.contains('like-button') ? 'like' : 'dislike';

            fetch('actions/update_likes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `post_id=${postId}&action=${action}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const postCard = this.closest('.post-card');
                        const likeButton = postCard.querySelector('.like-button');
                        const dislikeButton = postCard.querySelector('.dislike-button');

                        likeButton.innerHTML = `▲ <span class="vote-count">${data.likes > 0 ? data.likes : ''}</span>`;
                        dislikeButton.innerHTML = `▼ <span class="vote-count">${data.dislikes > 0 ? data.dislikes : ''}</span>`;
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Ошибка:', error));
        });
    });

    // Обработка кнопок удаления
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function () {
            const postId = this.getAttribute('data-post-id');
            if (confirm("Вы действительно хотите удалить этот пост?")) {
                // Используем GET-запрос, как работает в профиле
                fetch(`actions/delete_post.php?id=${postId}`, { method: 'GET' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            const postCard = this.closest('.post-card');
                            if (postCard) {
                                postCard.remove();
                            }
                        } else {
                            alert(data.message || "Ошибка при удалении поста.");
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
            }
        });
    });
});
