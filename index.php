<!doctype html>
<html>
    <head>
        <link rel="shortcut icon" href="#" />
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Gestion de calidad - iniciar sesion</title>
        <link rel="stylesheet" href="estilos.css">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="stylesheet" href="plugins/sweetalert2/sweetalert2.min.css">        
    </head>
    
    <body>
     
      <div class="container-login">
        <div class="wrap-login">
            <form  id="formLogin" action="" method="post">
                <span class="login-form-title">Gestion de calidad</span>
                <div>
                    <br><br>
                </div>
                
                <div class="wrap-input100" data-validate = "Usuario incorrecto">
                    <input class="input100" type="text" id="usuario" name="usuario" placeholder="Digite su Usuario">
                </div>
                
                <div class="wrap-input100" data-validate="Password incorrecto">
                    <input class="input100" type="password" id="password" name="password" placeholder="Digite su contraseña">
                </div>
                
                
                    <div class="wrap-login-form-btn">
                        <div class="login-form-bgbtn"></div>
                        <button type="submit" name="submit" class="login-form-btn">iniciar sesion</button>
                    </div>
                
            </form>
        </div>
    </div>     
        
        
     <script src="jquery/jquery-3.3.1.min.js"></script>       
     <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>    
     <script src="codigo.js"></script>    
    </body>
</html>