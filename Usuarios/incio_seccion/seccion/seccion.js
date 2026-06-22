function mostrarPassword() {

    let password = document.getElementById("password");

    if(password.type === "password"){
        password.type = "text";
    }else{
        password.type = "password";
    }

}

const INGRESAR  = document.getElementById("ingresar");

INGRESAR.addEventListener("click", () => {
    window.location.href = "../Pagina_inicio/index.php";
});
