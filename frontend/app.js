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
      // Kompatibilis transform lekérés
      const computedStyle = window.getComputedStyle(movieLists[i]);
      const matrix = new DOMMatrix(computedStyle.transform);
      const currentX = matrix.m41; // translateX értéke
      
      movieLists[i].style.transform = `translateX(${currentX - 300}px)`;
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

// ------------------------------------------
// MENÜ FRISSÍTÉSE BEJELENTKEZÉSI ÁLLAPOT ALAPJÁN
// ------------------------------------------
async function updateNavigationMenu() {
  const menuList = document.querySelector('.menu-list');
  if (!menuList) return;
  
  // Profil kép kezelése
  const profilePicLink = document.querySelector('.profile-container a');
  
  try {
    const result = await API.getProfile();
    
    if (result.success && result.data.user) {
      const user = result.data.user;
      const role = user.szerepkor;
      
      // Profil kép megjelenítése bejelentkezett felhasználónak
      if (profilePicLink) {
        profilePicLink.style.display = 'block';
      }
      
      // Töröld a meglévő dinamikus menüpontokat
      menuList.innerHTML = '';
      
      // Keresés (mindig látható)
      const searchItem = document.createElement('li');
      searchItem.className = 'menu-list-item';
      searchItem.innerHTML = '<a href="kereses.html">Keresés</a>';
      menuList.appendChild(searchItem);
      
      // Admin (csak moderátor/admin)
      if (role === 'moderator' || role === 'admin') {
        const adminItem = document.createElement('li');
        adminItem.className = 'menu-list-item';
        adminItem.innerHTML = '<a href="admin.html">Admin</a>';
        menuList.appendChild(adminItem);
      }
      
      // Profil (bejelentkezett)
      const profileItem = document.createElement('li');
      profileItem.className = 'menu-list-item';
      profileItem.innerHTML = '<a href="profil.html">Profil</a>';
      menuList.appendChild(profileItem);
      
      // Kijelentkezés (bejelentkezett)
      const logoutItem = document.createElement('li');
      logoutItem.className = 'menu-list-item';
      logoutItem.innerHTML = '<a href="#" id="logout-link">Kijelentkezés</a>';
      menuList.appendChild(logoutItem);
      
      // Kijelentkezés eseménykezelő
      document.getElementById('logout-link').addEventListener('click', async (e) => {
        e.preventDefault();
        await API.logout();
        window.location.href = 'index.html';
      });
      
    } else {
      // Nincs bejelentkezve - profil kép elrejtése
      if (profilePicLink) {
        profilePicLink.style.display = 'none';
      }
      
      // Alapértelmezett menü
      showGuestMenu(menuList);
    }
  } catch (error) {
    // Hiba esetén profil kép elrejtése és alapértelmezett menü
    if (profilePicLink) {
      profilePicLink.style.display = 'none';
    }
    showGuestMenu(menuList);
  }
}

// Vendég (nem bejelentkezett) menü megjelenítése
function showGuestMenu(menuList) {
  menuList.innerHTML = '';
  
  // Keresés
  const searchItem = document.createElement('li');
  searchItem.className = 'menu-list-item';
  searchItem.innerHTML = '<a href="kereses.html">Keresés</a>';
  menuList.appendChild(searchItem);
  
  // Bejelentkezés
  const loginItem = document.createElement('li');
  loginItem.className = 'menu-list-item';
  loginItem.innerHTML = '<a href="bejelentkezes.html">Bejelentkezés</a>';
  menuList.appendChild(loginItem);
  
  // Regisztráció
  const registerItem = document.createElement('li');
  registerItem.className = 'menu-list-item';
  registerItem.innerHTML = '<a href="regisztracio.html">Regisztráció</a>';
  menuList.appendChild(registerItem);
}

// Oldal betöltésekor frissítsd a menüt
document.addEventListener('DOMContentLoaded', updateNavigationMenu);