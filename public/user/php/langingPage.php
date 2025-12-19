<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- BOOTSTRAP -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

  <!-- CSS LOKAL -->
  <link rel="stylesheet" href="../css/style.css">

  <title>JogjaVerse</title>
</head>

<body>

<?php include "../php/homepage.php"; ?>
<?php include "../html/deskripsi.html"; ?>
<?php include "../html/destinasi.html"; ?>
<?php include "../html/evenAtraksi.html"; ?>
<?php include "../html/kuliner.html"; ?>
<?php include "../html/map.html"; ?>
<?php include "../html/footer.html"; ?>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });
</script>

</body>
</html>
