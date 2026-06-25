$('#formLogin').submit(function(e){
    e.preventDefault();
    var usuario = $.trim($("#usuario").val());    
    var password = $.trim($("#password").val());    
    
    if(usuario.length == "" || password == ""){ Swal.fire({ type:'warning', title:'Debe digitar el usuario y la contraseña',
            confirmButtonText: 'Aceptar'
        });
        return false; 
    } else {
        $.ajax({
            url: "bd/login.php",
            type: "POST",
            datatype: "json",
            data: {correo: usuario, contraseña: password},
            success: function(data){
                let response = data;
                if (typeof data === "string") {
                    try {
                        response = JSON.parse(data);
                    } catch (e) {
                        response = {};
                    }
                }

                if(!response.success){ Swal.fire({ type:'error', title:'Usuario o contraseña incorrecta',
                    });
                } else { Swal.fire({ type:'success', title:'¡Credenciales Correctas!',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        if(result.value){
                            // 1️⃣ Si el servidor dice que hay que cambiar la clave
                            if(response.forzar_cambio){
                                window.location.href = "/cambiar_clave.php?forzado=1";
                                return;
                            }

                            // 2️⃣ Si no, ir según el rol
                            if(response.rol === "administrador"){
                                window.location.href = "/usuarios/admin/index.php";
                            } else if(response.rol === "lider de calidad"){
                                window.location.href = "/usuarios/lider_calidad/index.php";
                            } else if(response.rol === "agente"){
                                window.location.href = "/usuarios/agente/index.php";
                            } else if(response.rol === "supervisor"){
                                window.location.href = "/usuarios/supervisor/index.php";
                            }
                        }
                    })
                }
            }
        });
    }
});
