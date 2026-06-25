<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Acceso Denegado</title>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fc;
    }
    .access-denied-container {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }
    .access-denied-container h1 {
      font-size: 5rem;
      color: #e74a3b;
    }
    .access-denied-container p {
      font-size: 1.25rem;
    }
  </style>
</head>
<body>

  <div class="access-denied-container text-center">
    <h1><i class="fas fa-ban"></i></h1>
    <h2 class="text-danger">Acceso Denegado</h2>
    <p>No tienes permisos para acceder a esta página.</p>
    <a href="/index.php" class="btn btn-primary mt-3"><i class="fas fa-home"></i> Volver al inicio</a>
  </div>

</body>
</html>
