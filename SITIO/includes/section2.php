<section class="section-2" id="section2">
    <div class="section-gap">
        <!-- Encabezado de la sección -->
        <div class="full-section2-card1">
            <h2>¿Quienes somos?</h2>
            <br>
            <span></span> <!-- línea decorativa -->
        </div>

        <!-- Contenedor principal con texto e imágenes -->
        <div class="full-section2-card2">

            <!-- Texto descriptivo -->
            <div class="center-section2">
                <p>
                    Lorem ipsum dolor sit amet consectetur adipisicing elit...
                    <!-- Texto de presentación institucional -->
                </p>

                <!-- Carrusel horizontal con miembros -->
                <div class="wrap">
                    <div class="track" id="t">

                        <!-- Item 1 -->
                        <div class="item">
                            <img src="assets/img/img2.jpeg" alt="">
                            <span>Dilsia Lopez  Presidente</span>
                        </div>

                        <!-- Item 2 -->
                        <div class="item">
                            <img src="assets/img/img1.jpeg" alt="">
                            <span>Eleomar Paz Vocal</span>
                        </div>
                    
                        <!-- Item 3 -->
                        <div class="item">
                            <img src="assets/img/img3.jpeg" alt="">
                            <span>Victor Suniga Secretario</span>
                        </div>

                        <!-- Item 4 -->
                        <div class="item">
                            <img src="assets/img/img4.jpeg" alt="">
                            <span>Pablo Mendez Tesorero</span>
                        </div>

                        <!-- Item 5 -->
                        <div class="item">
                            <img src="assets/img/img5.jpeg" alt="">
                            <span>Mario Lorenzo Vocal</span>
                        </div>

                        <!-- Item 6 -->
                        <div class="item">
                            <img src="assets/img/img6.jpeg" alt="">
                            <span>Gabriel Linares Fiscal</span>
                        </div>

                        <!-- Item 7 -->
                        <div class="item">
                            <img src="assets/img/img7.jpeg" alt="">
                            <span>Fredi Garcia Vocal</span>
                        </div>
                    </div>

                    <!-- Botones de desplazamiento -->
                    <div class="btns"> 
                        <button onclick="document.getElementById('t').scrollBy({left: -180, behavior: 'smooth'})">
                            ← Anterior
                        </button>
                        <button onclick="document.getElementById('t').scrollBy({left: 180, behavior: 'smooth'})">
                            Siguiente →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de misión, visión y valores -->
        <div class="cards-fondo-section2">
            <div class="cards3-section2">
                <img src="./assets/img/compania.png" alt="icon">
                <h3>Mision</h3>
                <p>Texto descriptivo de la misión...</p>
            </div>

            <div class="cards3-section2">
                <img src="./assets/img/negocio.png" alt="icon">
                <h3>Vision</h3>
                <p>Texto descriptivo de la visión...</p>
            </div>

            <div class="cards3-section2">
                <img src="./assets/img/cultura.png" alt="icon">
                <h3>Valores</h3>
                <p>Texto descriptivo de los valores...</p>
            </div>
        </div>
</section>
