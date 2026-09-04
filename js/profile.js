document.addEventListener('DOMContentLoaded', () => {
    // Переключение вкладок
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Функция для выполнения AJAX POST-запроса
    function ajaxPost(url, formData, onSuccess) {
        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(text => {
                // Если запрос к like_post.php – сервер возвращает plain text "OK"
                if (url.includes("actions/like_post.php")) {
                    if (text.trim() === "OK") {
                        location.reload();
                    } else {
                        console.error("Unexpected response from actions/like_post.php:", text);
                        alert("Ошибка при выполнении запроса.");
                    }
                } else {
                    try {
                        const data = JSON.parse(text);
                        onSuccess(data);
                    } catch (e) {
                        console.error('Ошибка при разборе ответа:', text);
                        alert("Ошибка при выполнении запроса.");
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка AJAX:', error);
                alert("Ошибка при выполнении запроса.");
            });
    }

    // Обработка отправки формы комментария без перезагрузки страницы
    const commentForm = document.querySelector('form[action="actions/add_comment.php"]');
    if (commentForm) {
        commentForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(commentForm);
            ajaxPost('actions/add_comment.php', formData, (data) => {
                if (data.success) {
                    const commentsContainer = document.querySelector('.profile-comments');
                    const newComment = document.createElement('div');
                    newComment.className = 'comment';
                    newComment.id = "comment-" + data.comment.id;
                    newComment.innerHTML =
                        '<p><strong>' + data.comment.author_vk + ':</strong> ' + data.comment.comment + '</p>' +
                        '<small>' + data.comment.created_at + '</small>';
                    if (data.comment.can_delete) {
                        const delBtn = document.createElement('button');
                        delBtn.className = 'ajax-delete-comment delete-button';
                        delBtn.dataset.commentId = data.comment.id;
                        delBtn.textContent = 'Удалить';
                        delBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            deleteComment(data.comment.id);
                        });
                        newComment.appendChild(delBtn);
                    }
                    commentsContainer.prepend(newComment);
                    commentForm.reset();
                } else {
                    alert(data.message || "Ошибка при отправке комментария");
                }
            });
        });
    }

    // Функция для удаления комментария через AJAX
    function deleteComment(commentId) {
        if (confirm("Вы уверены, что хотите удалить этот комментарий?")) {
            const formData = new FormData();
            formData.append('comment_id', commentId);
            ajaxPost('actions/delete_comment.php', formData, (data) => {
                if (data.status === 'success' || data.success) {
                    const commentElem = document.getElementById("comment-" + commentId);
                    if (commentElem) commentElem.remove();
                } else {
                    alert(data.message || "Ошибка при удалении комментария");
                }
            });
        }
    }

    // Привязка обработчиков удаления комментариев для уже существующих элементов
    document.querySelectorAll('.ajax-delete-comment').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const commentId = button.dataset.commentId;
            deleteComment(commentId);
        });
    });

    // Делегирование события для удаления поста через AJAX
    document.addEventListener('click', function(e) {
        const deleteLink = e.target.closest('.ajax-delete');
        if (deleteLink) {
            e.preventDefault();
            const url = deleteLink.href;
            ajaxPost(url, new FormData(), (data) => {
                if (data.status === 'success') {
                    const postId = deleteLink.dataset.postId;
                    const postElem = document.getElementById("post-" + postId);
                    if (postElem) {
                        postElem.remove();
                    }
                } else {
                    alert(data.message);
                }
            });
        }
    });

    // Обработка кликов по кнопкам лайк/дизлайк без перезагрузки страницы
    document.querySelectorAll('.ajax-like, .ajax-dislike').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const url = link.href;
            ajaxPost(url, new FormData(), (data) => {
                if (data.success) {
                    const postId = link.dataset.postId;
                    const likeSpan = document.querySelector('.ajax-like[data-post-id="' + postId + '"] .vote-count');
                    const dislikeSpan = document.querySelector('.ajax-dislike[data-post-id="' + postId + '"] .vote-count');
                    if (likeSpan) {
                        likeSpan.textContent = data.likes > 0 ? data.likes : '';
                    }
                    if (dislikeSpan) {
                        dislikeSpan.textContent = data.dislikes > 0 ? data.dislikes : '';
                    }
                } else {
                    alert(data.message);
                }
            });
        });
    });

    // Обработка кликов по ссылкам закрепления поста через AJAX
    const pinLinks = document.querySelectorAll('.ajax-pin');
    pinLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault(); // Отменяем переход по ссылке
            const url = link.getAttribute('href');
            ajaxPost(url, new FormData(), function(data) {
                if (data.status === 'success') {
                    // Если сервер вернул pinned == 1, меняем текст ссылки на "Открепить", иначе – "Закрепить"
                    const newAction = data.pinned == 1 ? 'unpin' : 'pin';
                    link.textContent = data.pinned == 1 ? 'Открепить' : 'Закрепить';
                    // Обновляем URL для последующих запросов, меняя параметр action
                    try {
                        let urlObj = new URL(url, window.location.href);
                        urlObj.searchParams.set('action', newAction);
                        link.setAttribute('href', urlObj.href);
                    } catch (err) {
                        // Если конструктор URL не поддерживается, обновляем вручную
                        const parts = url.split('&action=');
                        if (parts.length > 1) {
                            link.setAttribute('href', parts[0] + '&action=' + newAction);
                        }
                    }
                } else {
                    alert(data.message || "Ошибка при обновлении закрепления поста");
                }
            });
        });
    });
});
