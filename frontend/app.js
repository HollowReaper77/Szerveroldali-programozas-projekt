if (typeof API !== 'undefined' && typeof API.configureBaseUrlFromLocation === 'function') {
  API.configureBaseUrlFromLocation();
}

const movieListWrappers = document.querySelectorAll(".movie-list-wrapper");

// A toggle gomb és a stílusozott elemek meghatározása
const ball = document.querySelector(".toggle-ball");
const items = document.querySelectorAll(
  ".container,.movie-list-title,.navbar-container,.sidebar,.left-menu-icon,.toggle"
);

// Nyilak logikája
movieListWrappers.forEach((wrapper) => {
  const movieList = wrapper.querySelector(".movie-list");
  if (!movieList) return;

  let currentIndex = 0;

  const getVisibleRatio = () => {
    const visibleSlots = Math.floor(wrapper.clientWidth / 270);
    return Math.max(1, Math.min(visibleSlots, 4));
  };

  const getTotalItems = () => movieList.querySelectorAll(".movie-list-item").length;

  const updatePosition = () => {
    movieList.style.transform = `translateX(${-currentIndex * 300}px)`;
  };

  const clampIndex = () => {
    const maxIndex = Math.max(0, getTotalItems() - getVisibleRatio());
    currentIndex = Math.min(currentIndex, maxIndex);
  };

  wrapper.querySelectorAll(".arrow").forEach((arrow) => {
    const direction = arrow.classList.contains("arrow-right") ? 1 : -1;
    arrow.addEventListener("click", () => {
      const maxIndex = Math.max(0, getTotalItems() - getVisibleRatio());
      currentIndex = Math.min(
        Math.max(currentIndex + direction, 0),
        maxIndex
      );
      updatePosition();
    });
  });

  window.addEventListener("resize", () => {
    clampIndex();
    updatePosition();
  });
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
      const role = user.jogosultsag || user.szerepkor || 'user';
      
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

      // Film lista (csak bejelentkezett)
      const filmsItem = document.createElement('li');
      filmsItem.className = 'menu-list-item';
      filmsItem.innerHTML = '<a href="filmek.html">Megnézett filmek</a>';
      menuList.appendChild(filmsItem);
      
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
      
      // SIDEBAR frissítése - bejelentkezett
      updateSidebarForLoggedIn(role);
      
    } else {
      // Nincs bejelentkezve - profil kép elrejtése
      if (profilePicLink) {
        profilePicLink.style.display = 'none';
      }
      
      // Alapértelmezett menü
      showGuestMenu(menuList);
      
      // SIDEBAR frissítése - vendég
      updateSidebarForGuest();
    }
  } catch (error) {
    // Hiba esetén profil kép elrejtése és alapértelmezett menü
    if (profilePicLink) {
      profilePicLink.style.display = 'none';
    }
    showGuestMenu(menuList);
    updateSidebarForGuest();
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

// SIDEBAR frissítése - bejelentkezett felhasználóknak
function updateSidebarForLoggedIn(role) {
  const sidebar = document.querySelector('.sidebar');
  if (!sidebar) return;
  
  sidebar.innerHTML = '';
  
  // Keresés ikon
  const searchIcon = document.createElement('a');
  searchIcon.href = 'kereses.html';
  searchIcon.innerHTML = '<i class="left-menu-icon fas fa-search"></i>';
  sidebar.appendChild(searchIcon);
  
  // Főoldal ikon
  const homeIcon = document.createElement('a');
  homeIcon.href = 'index.html';
  homeIcon.innerHTML = '<i class="left-menu-icon fas fa-home"></i>';
  sidebar.appendChild(homeIcon);
  
  // Profil ikon
  const profileIcon = document.createElement('a');
  profileIcon.href = 'profil.html';
  profileIcon.innerHTML = '<i class="left-menu-icon fas fa-user"></i>';
  sidebar.appendChild(profileIcon);

  // Film ikon
  const filmsIcon = document.createElement('a');
  filmsIcon.href = 'filmek.html';
  filmsIcon.innerHTML = '<i class="left-menu-icon fas fa-film"></i>';
  sidebar.appendChild(filmsIcon);
  
  // Admin ikon (csak moderátor/admin)
  if (role === 'moderator' || role === 'admin') {
    const adminIcon = document.createElement('a');
    adminIcon.href = 'admin.html';
    adminIcon.innerHTML = '<i class="left-menu-icon fas fa-cog"></i>';
    sidebar.appendChild(adminIcon);
  }
}

// SIDEBAR frissítése - vendég felhasználóknak
function updateSidebarForGuest() {
  const sidebar = document.querySelector('.sidebar');
  if (!sidebar) return;
  
  sidebar.innerHTML = '';
  
  // Keresés ikon
  const searchIcon = document.createElement('a');
  searchIcon.href = 'kereses.html';
  searchIcon.innerHTML = '<i class="left-menu-icon fas fa-search"></i>';
  sidebar.appendChild(searchIcon);
  
  // Főoldal ikon
  const homeIcon = document.createElement('a');
  homeIcon.href = 'index.html';
  homeIcon.innerHTML = '<i class="left-menu-icon fas fa-home"></i>';
  sidebar.appendChild(homeIcon);

  // Film lista ikon (vendégek is böngészhetik a listát)
  const filmsIcon = document.createElement('a');
  filmsIcon.href = 'filmek.html';
  filmsIcon.innerHTML = '<i class="left-menu-icon fas fa-film"></i>';
  sidebar.appendChild(filmsIcon);

  // Bejelentkezés ikon
  const loginIcon = document.createElement('a');
  loginIcon.href = 'bejelentkezes.html';
  loginIcon.innerHTML = '<i class="left-menu-icon fas fa-sign-in-alt"></i>';
  sidebar.appendChild(loginIcon);

  // Regisztráció ikon
  const registerIcon = document.createElement('a');
  registerIcon.href = 'regisztracio.html';
  registerIcon.innerHTML = '<i class="left-menu-icon fas fa-user-plus"></i>';
  sidebar.appendChild(registerIcon);
}


// Oldal betöltésekor frissítsd a menüt
document.addEventListener('DOMContentLoaded', updateNavigationMenu);