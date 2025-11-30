const arrows = document.querySelectorAll(".arrow");
const movieLists = document.querySelectorAll(".movie-list");

// A toggle gomb és a stílusozott elemek meghatározása
const ball = document.querySelector(".toggle-ball");
const items = document.querySelectorAll(
  ".container,.movie-list-title,.navbar-container,.sidebar,.left-menu-icon,.toggle"
);

// Nyilak logikája (nem változott)
arrows.forEach((arrow, i) => {
  const itemNumber = movieLists[i].querySelectorAll("img").length;
  let clickCounter = 0;
  arrow.addEventListener("click", () => {
    const ratio = Math.floor(window.innerWidth / 270);
    clickCounter++;
    if (itemNumber - (4 + clickCounter) + (4 - ratio) >= 0) {
      movieLists[i].style.transform = `translateX(${
        movieLists[i].computedStyleMap().get("transform")[0].x.value - 300
      }px)`;
    } else {
      movieLists[i].style.transform = "translateX(0)";
      clickCounter = 0;
    }
  });

  console.log(Math.floor(window.innerWidth / 270));
});


// ------------------------------------------
// ÚJ: Téma állapotának betöltése az oldal betöltésekor
// ------------------------------------------
function loadTheme() {
  const savedTheme = localStorage.getItem('theme');
  
  // Ha a mentett állapot 'light' (világos), alkalmazzuk az 'active' osztályokat
  if (savedTheme === 'light') {
    items.forEach((item) => {
      item.classList.add("active");
    });
    ball.classList.add("active");
  }
}

// ------------------------------------------
// TOGGLE FUNKCIÓ (Mentéssel)
// ------------------------------------------
ball.addEventListener("click", () => {
  items.forEach((item) => {
    item.classList.toggle("active");
  });
  ball.classList.toggle("active");

  // ÚJ: Állapot mentése a Local Storage-be
  if (ball.classList.contains("active")) {
    localStorage.setItem('theme', 'light');
  } else {
    localStorage.setItem('theme', 'dark');
  }
});


// Az állapot betöltése az oldal indításakor
loadTheme();