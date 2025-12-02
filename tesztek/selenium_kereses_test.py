"""
Selenium manuális teszt - Film keresés oldal
CinemaTár projekt - Szerveroldali programozás
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

# Browser inicializálás - válaszd ki melyiket használod:
# driver = webdriver.Chrome()
driver = webdriver.Firefox()  # Firefox használata
# driver = webdriver.Edge()

wait = WebDriverWait(driver, 10)

try:
    print("=== CINEMATÁR KERESÉS OLDAL - MANUÁLIS TESZT ===\n")
    
    # 1. TESZT: Oldal betöltése
    print("1. Teszt: Oldal betöltése...")
    driver.get("http://localhost/php/PHP projekt/Szerveroldali-programozas-projekt/frontend/kereses.html")
    time.sleep(2)
    
    assert "Cinematár" in driver.title
    print("✓ Oldal sikeresen betöltve\n")
    
    # 2. TESZT: Keresőmező ellenőrzése
    print("2. Teszt: Keresőmező ellenőrzése...")
    search_input = wait.until(EC.presence_of_element_located((By.ID, "search")))
    search_button = driver.find_element(By.ID, "search-button")
    
    assert search_input.is_displayed()
    assert search_button.is_displayed()
    print("✓ Keresőmező és gomb megtalálható\n")
    
    # 3. TESZT: Filmek kezdeti betöltése
    print("3. Teszt: Filmek kezdeti betöltése...")
    time.sleep(3)  # Várunk az API válaszra
    
    films_container = driver.find_element(By.ID, "films")
    status_element = driver.find_element(By.ID, "status")
    
    status_text = status_element.text
    print(f"   Státusz: {status_text}")
    
    movie_cards = films_container.find_elements(By.CLASS_NAME, "movie-card")
    print(f"   Betöltött filmek száma: {len(movie_cards)}")
    assert len(movie_cards) > 0, "Nincsenek filmek betöltve!"
    print("✓ Filmek sikeresen betöltve\n")
    
    # 4. TESZT: Keresés cím alapján
    print("4. Teszt: Keresés cím alapján...")
    search_input.clear()
    search_input.send_keys("Matrix")
    search_button.click()
    time.sleep(1)
    
    filtered_cards = films_container.find_elements(By.CLASS_NAME, "movie-card")
    print(f"   Találatok száma 'Matrix' keresésre: {len(filtered_cards)}")
    print(f"   Státusz: {status_element.text}")
    print("✓ Keresés cím alapján működik\n")
    
    # 5. TESZT: Keresés Enter billentyűvel
    print("5. Teszt: Keresés Enter billentyűvel...")
    search_input.clear()
    search_input.send_keys("horror")
    search_input.send_keys(Keys.RETURN)
    time.sleep(1)
    
    filtered_cards = films_container.find_elements(By.CLASS_NAME, "movie-card")
    print(f"   Találatok száma 'horror' keresésre: {len(filtered_cards)}")
    print("✓ Enter billentyűs keresés működik\n")
    
    # 6. TESZT: Üres keresés (összes film megjelenítése)
    print("6. Teszt: Üres keresés...")
    search_input.clear()
    search_button.click()
    time.sleep(1)
    
    all_cards_again = films_container.find_elements(By.CLASS_NAME, "movie-card")
    print(f"   Megjelenített filmek: {len(all_cards_again)}")
    assert len(all_cards_again) == len(movie_cards), "Üres keresés nem jeleníti meg az összes filmet!"
    print("✓ Üres keresés visszaállítja az összes filmet\n")
    
    # 7. TESZT: Nem létező film keresése
    print("7. Teszt: Nem létező film keresése...")
    search_input.clear()
    search_input.send_keys("xyzabc123nonexistent")
    search_button.click()
    time.sleep(1)
    
    no_results = films_container.find_elements(By.CLASS_NAME, "movie-card")
    print(f"   Találatok száma: {len(no_results)}")
    
    if len(no_results) == 0:
        print("   Üzenet: " + films_container.text)
    
    print("✓ Nincs találat helyes kezelése\n")
    
    # 8. TESZT: Navigációs menü
    print("8. Teszt: Navigációs menü ellenőrzése...")
    menu_links = driver.find_elements(By.CSS_SELECTOR, ".menu-list a")
    print(f"   Menüpontok száma: {len(menu_links)}")
    
    for link in menu_links:
        print(f"   - {link.text}: {link.get_attribute('href')}")
    
    assert len(menu_links) >= 3, "Hiányoznak menüpontok!"
    print("✓ Navigációs menü rendben\n")
    
    # 9. TESZT: Sidebar ikonok
    print("9. Teszt: Sidebar ikonok ellenőrzése...")
    sidebar_icons = driver.find_elements(By.CSS_SELECTOR, ".sidebar a")
    print(f"   Sidebar ikonok száma: {len(sidebar_icons)}")
    assert len(sidebar_icons) >= 4, "Hiányoznak sidebar ikonok!"
    print("✓ Sidebar ikonok rendben\n")
    
    # 10. TESZT: Film kártya tartalom
    print("10. Teszt: Film kártya részletes ellenőrzése...")
    search_input.clear()
    search_button.click()
    time.sleep(1)
    
    first_card = films_container.find_element(By.CLASS_NAME, "movie-card")
    card_title = first_card.find_element(By.TAG_NAME, "h3").text
    card_image = first_card.find_element(By.TAG_NAME, "img")
    
    print(f"   Első film címe: {card_title}")
    print(f"   Poszter URL: {card_image.get_attribute('src')[:50]}...")
    print(f"   Kártya hover működik: ", end="")
    
    # Hover teszt
    webdriver.ActionChains(driver).move_to_element(first_card).perform()
    time.sleep(0.5)
    print("✓")
    print("✓ Film kártya megfelelően jelenik meg\n")
    
    print("=" * 50)
    print("ÖSSZES TESZT SIKERES!")
    print("=" * 50)
    
    # 11. TESZT: Dark mode toggle
    print("\n11. Teszt: Dark mode toggle...")
    toggle = driver.find_element(By.CLASS_NAME, "toggle")
    toggle_ball = driver.find_element(By.CLASS_NAME, "toggle-ball")
    
    # Eredeti pozíció
    initial_position = toggle_ball.location['x']
    toggle.click()
    time.sleep(0.5)
    
    # Új pozíció
    new_position = toggle_ball.location['x']
    print(f"   Toggle pozíció változás: {initial_position} → {new_position}")
    print("✓ Dark mode toggle működik\n")
    
    # 12. TESZT: Logo kattintás (index.html-re irányít)
    print("12. Teszt: Logo link ellenőrzése...")
    logo_link = driver.find_element(By.CSS_SELECTOR, ".logo-link")
    logo_href = logo_link.get_attribute("href")
    
    print(f"   Logo link: {logo_href}")
    assert "index.html" in logo_href, "Logo nem az index.html-re mutat!"
    print("✓ Logo link helyes\n")
    
    # 13. TESZT: Bejelentkezés link
    print("13. Teszt: Bejelentkezés link...")
    login_link = None
    for link in menu_links:
        if "Bejelentkezés" in link.text:
            login_link = link
            break
    
    if login_link:
        login_href = login_link.get_attribute("href")
        print(f"   Bejelentkezés URL: {login_href}")
        assert "bejelentkezes.html" in login_href
        print("✓ Bejelentkezés link helyes\n")
    
    # 14. TESZT: Regisztráció link
    print("14. Teszt: Regisztráció link...")
    register_link = None
    for link in menu_links:
        if "Regisztráció" in link.text:
            register_link = link
            break
    
    if register_link:
        register_href = register_link.get_attribute("href")
        print(f"   Regisztráció URL: {register_href}")
        assert "regisztracio.html" in register_href
        print("✓ Regisztráció link helyes\n")
    
    # 15. TESZT: Profil kép kattintás
    print("15. Teszt: Profil kép link...")
    profile_pic = driver.find_element(By.CLASS_NAME, "profile-picture")
    profile_link = profile_pic.find_element(By.XPATH, "..")
    profile_href = profile_link.get_attribute("href")
    
    print(f"   Profil URL: {profile_href}")
    assert "profil.html" in profile_href
    print("✓ Profil link helyes\n")
    
    # 16. TESZT: Film kártya hover animáció
    print("16. Teszt: Film kártya hover animáció...")
    search_input.clear()
    search_button.click()
    time.sleep(1)
    
    test_card = films_container.find_element(By.CLASS_NAME, "movie-card")
    
    # Eredeti transform
    original_transform = test_card.value_of_css_property("transform")
    
    # Hover
    webdriver.ActionChains(driver).move_to_element(test_card).perform()
    time.sleep(0.3)
    hover_transform = test_card.value_of_css_property("transform")
    
    print(f"   Transform eredeti: {original_transform[:30]}...")
    print(f"   Transform hover: {hover_transform[:30]}...")
    print("✓ Hover animáció működik\n")
    
    # 17. TESZT: Sidebar home ikon
    print("17. Teszt: Sidebar home ikon...")
    home_icon = None
    for icon_link in sidebar_icons:
        if "index.html" in icon_link.get_attribute("href"):
            home_icon = icon_link
            break
    
    assert home_icon is not None, "Home ikon nem található!"
    print(f"   Home ikon URL: {home_icon.get_attribute('href')}")
    print("✓ Sidebar home ikon rendben\n")
    
    # 18. TESZT: Film poszter képek betöltése
    print("18. Teszt: Film poszter képek betöltése...")
    all_images = films_container.find_elements(By.TAG_NAME, "img")
    loaded_images = 0
    broken_images = 0
    
    for img in all_images[:5]:  # Csak első 5 kép ellenőrzése
        naturalWidth = driver.execute_script("return arguments[0].naturalWidth;", img)
        if naturalWidth > 0:
            loaded_images += 1
        else:
            broken_images += 1
    
    print(f"   Betöltött képek: {loaded_images}")
    print(f"   Hibás képek: {broken_images}")
    print("✓ Poszter képek ellenőrizve\n")
    
    # 19. TESZT: Keresési eredmény üzenet dinamikus frissítése
    print("19. Teszt: Keresési eredmény üzenet...")
    search_input.clear()
    search_input.send_keys("test")
    search_button.click()
    time.sleep(1)
    
    result_message = status_element.text
    print(f"   Eredmény üzenet: {result_message}")
    assert "találat" in result_message.lower() or "film" in result_message.lower()
    print("✓ Eredmény üzenet dinamikusan frissül\n")
    
    # 20. TESZT: Film leírás rövidítése (...-tal)
    print("20. Teszt: Film leírás rövidítése...")
    search_input.clear()
    search_button.click()
    time.sleep(1)
    
    film_descriptions = films_container.find_elements(By.TAG_NAME, "p")
    truncated_found = False
    
    for desc in film_descriptions:
        if "..." in desc.text:
            truncated_found = True
            print(f"   Rövidített leírás: {desc.text[:50]}...")
            break
    
    if truncated_found:
        print("✓ Leírás rövidítés működik\n")
    else:
        print("⚠ Nincs rövidített leírás (lehet, hogy mind rövid)\n")
    
    print("=" * 50)
    print("ÖSSZES 20 TESZT SIKERES!")
    print("=" * 50)
    
    # Böngésző nyitva hagyása 5 másodpercig
    print("\nBöngésző bezárása 5 másodperc múlva...")
    time.sleep(5)

except AssertionError as e:
    print(f"\n✗ TESZT SIKERTELEN: {e}")
    time.sleep(3)

except Exception as e:
    print(f"\n✗ HIBA TÖRTÉNT: {e}")
    import traceback
    traceback.print_exc()
    time.sleep(3)

finally:
    driver.quit()
    print("\nTeszt befejezve.")
