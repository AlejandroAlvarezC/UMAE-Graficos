

    function anterior(){
        window.history.back();
    }
    
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.altKey && event.key === 'a') {
            window.location.href = 'loginadmin.php';
        }
    });

    function validarFormulario2() {
        let nom = document.getElementById("names").value;
        let email = document.getElementById("email").value;
        let password = document.getElementById("password").value;
        
        let esValido = true;
        
       
        if (nom.length < 3) {
            document.getElementById("errorNames").textContent = "El nombre debe tener al menos 3 caracteres.";
            esValido = false;
        } else {
            document.getElementById("errorNames").textContent = "";
        }
        
       
        if (!email.includes("@") || !email.includes(".")) {
            document.getElementById("errorEmai").textContent = "Ingresa un correo electrónico válido.";
            esValido = false;
        } else {
            document.getElementById("errorEmai").textContent = "";
        }
        
      
        if (password.length < 6) {
            document.getElementById("errorPasword").textContent = "La contraseña debe tener al menos 6 caracteres.";
            esValido = false;
        } else {
            document.getElementById("errorPasword").textContent = "";
        }
        
        return esValido;
    }
