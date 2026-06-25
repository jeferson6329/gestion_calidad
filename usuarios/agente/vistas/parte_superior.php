<?php
session_start();

if($_SESSION["s_usuario"] === null){
    header("Location: /index.php");
}
if ($_SESSION["s_rol"] !== "agente") {
    // Puedes redirigirlo o mostrar un mensaje de error
    header("Location: acceso_denegado.php"); // o simplemente: die("Acceso denegado.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>SB Admin 2 - Dashboard</title>

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="css/sb-admin-2.min.css" rel="stylesheet">

  <!--datables CSS básico-->
  <link rel="stylesheet" type="text/css" href="vendor/datatables/datatables.min.css"/>
  <!--datables estilo bootstrap 4 CSS-->  
  <link rel="stylesheet"  type="text/css" href="vendor/datatables/DataTables-1.10.18/css/dataTables.bootstrap4.min.css">      
</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-danger sidebar sidebar-dark " id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center">
        <div class="text-white text-center"><br>Gestion de calidad - SDM</div>
      </a>
      <div class="text-white text-center"><br><?php echo $_SESSION["s_nombre"] . " " . $_SESSION["s_apellido"]; ?><br></div>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item active">
        <a class="nav-link" href="index.php">
          <i class="fas fa-home"></i>

          <span>INICIO</span></a>
      </li>

      <hr class="sidebar-divider">
      <li class="nav-item active">
        <a class="nav-link" href="monitoreosagente.php">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>MONITOREOS</span></a>
      </li>
      <hr class="sidebar-divider d-none d-md-block">
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>
    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-danger topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">

            <!-- Nav Item - Search Dropdown (Visible Only XS) -->
            <li class="nav-item dropdown no-arrow d-sm-none">
              <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                  <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="button">
                        <i class="fas fa-search fa-sm"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </li>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown ">
              
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 small text-white font-weight-bold"><?php echo $_SESSION["s_usuario"]; ?></span>
                <i class="fas fa-user text-white" style="font-size: 1.5rem;"></i>
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                   
                <!-- Cambiar Contraseña abre el modal -->
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalCambiarContrasena">
                
                  Cambiar Contraseña
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/bd/logout.php" data-toggle="modal" data-target="#logoutModal">

                  Cerrar Sesión
                </a>
              </div>
            </li>
          </ul>
        </nav>
        <!-- End of Topbar -->

        <!-- Modal Cambiar Contraseña -->
        <div class="modal fade" id="modalCambiarContrasena" tabindex="-1" role="dialog" aria-labelledby="modalCambiarContrasenaLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <form id="formCambiarContrasena">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalCambiarContrasenaLabel">Cambiar Contraseña</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label for="contraseña_actual">Contraseña actual:</label>
                    <input type="password" class="form-control" id="contraseña_actual" name="contraseña_actual" required>
                  </div>
                  <div class="form-group">
                    <label for="nueva_contraseña">Nueva contraseña:</label>
                    <input type="password" class="form-control" id="nueva_contraseña" name="nueva_contraseña" required>
                  </div>
                  <div class="form-group">
                    <label for="confirmar_contraseña">Confirmar nueva contraseña:</label>
                    <input type="password" class="form-control" id="confirmar_contraseña" name="confirmar_contraseña" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </div>
              </div>
            </form>
          </div>
        </div>


