<html>
 <head>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="header">
    <?php
        echo '<form action="index.php" method="post">';
        echo '<input type="submit" value="Go Home">';
        echo '</form>';

        if(isset($_SESSION['admin']) && $_SESSION['admin']) {
            echo '<form action="admin.php" method="POST">';
            echo '<input type="submit" value="Admin Page">';
            echo '</form>';
        }

        if (isset($_SESSION['user_id'])) {
            echo '<form action="logout.php" method="POST">';
            echo '<input type="submit" value="Logout">';
            echo '</form>';
        } else {
            echo '<form action="register.php" method="post">';
            echo '<input type="submit" value="Register">';
            echo '</form>';
            echo '<form action="userlogin.php" method="post">';
            echo '<input type="submit" value="Login">';
            echo '</form>';
        }
    ?>
</div>
