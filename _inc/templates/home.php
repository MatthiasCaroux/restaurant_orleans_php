<?php
$cssPath = "_inc/static/";
?>

<main class="home">
    <div class="search-container">
      <input type="text" placeholder="Rechercher un restaurant, un hôtel..." />
      <button>
        <img src="<?php echo $cssPath; ?>loupe.png" alt="Logo">
      </button>
    </div>
    <p class="search-info">
      Trouvez des restaurants, hôtels et bien plus encore, près de chez vous ou n'importe où dans le monde.
    </p>
    <section>
        <div class="restaurant-container">
            <div class="restaurant">
                <div class="restaurant-info">
                    <h2>Restaurant 1</h2>
                    <p>Adresse 1</p>
                    <p>Horaires 1</p>
                    <p>Note 1</p>
                </div>
            </div>
            <div class="restaurant">
                <div class="restaurant-info">
                    <h2>Restaurant 2</h2>
                    <p>Adresse 2</p>
                    <p>Horaires 2</p>
                    <p>Note 2</p>   
                </div>
            </div>
            <div class="restaurant">
                <div class="restaurant-info">
                    <h2>Restaurant 3</h2>
                    <p>Adresse 3</p>
                    <p>Horaires 3</p>
                    <p>Note 3</p>
                </div>
            </div>
        </div>
    </section>
  </main>