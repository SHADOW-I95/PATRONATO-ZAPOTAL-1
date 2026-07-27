const modalVer = document.getElementById("modal_ver");

const cerrarVer = document.getElementById("cerrar_ver");

const contenidoVer = document.getElementById("contenido_ver");

document.querySelectorAll(".btn_ver").forEach(boton=>{

    boton.addEventListener("click",()=>{

        const id = boton.dataset.id;
        fetch("./modulos/usuario/ver.php?id="+id)

        .then(res=>res.text())

        .then(html=>{

            contenidoVer.innerHTML = html;

            modalVer.style.display="flex";

        });

    });

});

cerrarVer.addEventListener("click",()=>{

    modalVer.style.display="none";

    contenidoVer.innerHTML="";

});