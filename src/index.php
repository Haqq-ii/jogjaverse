<?php
$title = "Jogjaverse";
$year = date("Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="pisang.jpg">
</head>
<body>
    <header>
        <h1>Welcome to <?php echo $title; ?></h1>
    </header>

    <main>
        <p>This is a simple HTML page with linked CSS for styling.</p>
    </main>

    <footer>
        <p>&copy; <?php echo $year; ?> Sample Page</p>
    </footer>
</body>
</html>
