<?php
/**
 * Функция isAdmin()
 * Проверяет, является ли пользователь администратором.
 *
 * @param string $vk_id  Идентификатор пользователя (например, VK ID)
 * @param mysqli $mysqli Объект подключения к базе данных
 * @return bool Возвращает true, если пользователь найден в таблице admins, иначе false.
 */
function isAdmin($vk_id, $mysqli) {
    // Подготовка запроса для проверки наличия пользователя в таблице admins
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM admins WHERE user_id = ?");
    if (!$stmt) {
        // Если запрос не подготовлен, можно вернуть false или обработать ошибку
        return false;
    }
    $stmt->bind_param("s", $vk_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}
?>
