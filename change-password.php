<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>
<body>
<header>
    <?php include 'generate-navigation.php'; ?>

    <?php generateNavigation(); ?>
</header>
<h2>Change Password</h2>
<form action="changing-password.php" method="POST">
    <label for="username">Username:</label><br>
    <input type="text" id="username" name="username" required><br>
    <label for="old_password">Old Password:</label><br>
    <input type="password" id="old_password" name="old_password" required><br>
    <label for="new_password">New Password:</label><br>
    <input type="password" id="new_password" name="new_password" required><br><br>
    <button type="submit">Zmień hasło</button>
</form>
</body>
</html>
