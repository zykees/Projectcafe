<?php
function formatPrice($price) {
    return number_format($price, 2) . ' บาท';
}

function getProfilePicture($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT picture_url FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['picture_url'] ?? 'assets/images/default-profile.png';
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function flashMessage($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return sprintf(
            '<div class="alert alert-%s">%s</div>',
            htmlspecialchars($flash['type']),
            htmlspecialchars($flash['message'])
        );
    }
    return '';
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirectTo($path) {
    header("Location: $path");
    exit();
}