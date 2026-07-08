# Travelpont Kezdőoldal plugin – dokumentáció

> Verzió: 1.3.0 · A Travelpont Ajánlatok / Úticélok pluginek architektúráját
> követi (`D:\travelpont.hu\_Saját_pluginek\`)
> SZABÁLY: minden módosításkor verziót emelünk a fő fájl fejlécében
> (cache-buster + követhetőség).

## Mit tud?

A jóváhagyott kezdőoldal-mockup (`Travelpont - kezdőoldal mockup.html`)
pixelpontos megvalósítása, valós adatokkal feltöltve:

- **Navigáció** – logó (CSS-ből épített boarding-pass jelvény, sárga
  repülő-ikonnal) + linkek az önálló Ajánlatok/Úticélok/Útikalauz (és ha
  léteznek, Rólunk/Kapcsolat) Oldalakra – lásd "Al-oldalak linkjei" lejjebb.
- **Hero** – statikus szöveg + kép-helyfoglaló (nincs még hero kép).
- **Kiemelt ajánlatok** – a `ajanlat` CPT-ből (Travelpont Ajánlatok plugin),
  max. 6 db, lejárt ajánlatok nélkül, kártyánként külön repjegy-ár /
  szállás-ár / Összesen sorral.
- **Úticélok** – a `uticel` CPT (Travelpont Úticélok plugin) legfelső szintű
  (ország) bejegyzéseiből 3 db, sötétedő overlay-jel.
- **Miért mi?** – statikus, 4 pontos rács (nem CPT-ből jön).
- **Útikalauz / Blog** – natív WordPress bejegyzésekből (`post`) 3 legutóbbi,
  amíg nincs önálló Blog CPT. Ha nincs egy publikált cikk sem, a szekció
  automatikusan nem jelenik meg.
- **Záró CTA** + **Footer**.

Minden kártyánál, ahol nincs feltöltött kiemelt kép, egy csíkos
placeholder-minta jelenik meg (a design nem törik el kép hiányában).

## Miért nem a téma adja az oldalakat? (site-wide oldal-keret, v1.3.0-tól)

A projekt korábbi konvenciója (lásd a Travelpont Ajánlatok/Úticélok
pluginek `single-display.php`-ja), hogy a megoldásoknak témafüggetlennek
kell lenniük (a projekt jelenleg Twenty Twenty-Five és Hello Elementor
között vált). Azok a pluginek ezt a `the_content` szűrővel érik el – de az
csak a MEGLÉVŐ téma-elrendezésbe illeszt be egy dobozt, itt viszont a
mockup SAJÁT navigációt és footert is hoz, amit nem lehet egy téma nav/
footer köré illeszteni duplikáció nélkül.

Ezért ez a plugin a `template_include` WordPress-szűrővel adja a TELJES
HTML dokumentumot, a téma header.php/footer.php-ját megkerülve – de a
`wp_head()`/`wp_footer()` hívásokkal így is minden más plugin (SEO,
statisztika stb.) rendben belekerül az oldalba. Ez ugyanúgy túléli a
témaváltást, mint egy szokásos `front-page.php`, csak nem kell a téma
mappájához nyúlni (ami frissítéskor elveszne, ha nem gyerektéma).

**v1.3.0-tól ez NEM csak a főoldalra igaz.** A felhasználónak nem volt
Elementor Pro előfizetése a Theme Builderhez, és a téma alap
fejléce/lábléce nem illett a Kezdőlap saját brandingjéhez – ezért a
`template_include` trükk minden, a plugin által "kezelt" kérésre kiterjed
(`includes/template-loader.php` → `tpk_is_managed_request()`: `is_front_page()`
VAGY `is_page()` VAGY `is_home()` VAGY `is_singular( array( 'post', 'ajanlat', 'uticel' ) )`).
Két sablon van:
- `templates/front-page.php` – csak a főoldalra, változatlan.
- `templates/page-wrapper.php` – MINDEN MÁS kezelt oldalra (Rólunk,
  Kapcsolat, Ajánlatok/Úticélok lista-Oldal, egyedi Ajánlat/Úticél/
  bejegyzés, az Útikalauz bejegyzés-index). `is_home()`-nál a standard WP
  Loopot kártyás rácsban írja ki (a főoldali Blog-szekció márkázásával),
  egyébként `the_title()` + `the_content()` – az Ajánlatok/Úticélok
  pluginek `the_content` szűrője ITT IS lefut, tehát az egyedi Ajánlat/
  Úticél doboz automatikusan megjelenik, kód nélkül.

A közös nav/footer HTML-t az `includes/chrome.php` `tpk_render_nav()` /
`tpk_render_footer()` függvényei adják, mindkét sablon ezeket hívja (DRY).

**Ha ezt a plugint aktiválod, a WordPress "Beállítások → Olvasás" alatti
kezdőlap-beállítástól FÜGGETLENÜL ez a sablon jelenik meg a főoldalon**
(`is_front_page()` alapján – működik "legutóbbi bejegyzések" ÉS "statikus
oldal" módban is).

## Fájlszerkezet

```
travelpont-kezdolap/
├── travelpont-kezdolap.php     ← fő fájl: konstansok, betűtípus + CSS/JS enqueue
├── includes/
│   ├── template-loader.php     ← template_include szűrő + tpk_is_managed_request()
│   ├── chrome.php              ← közös nav/footer (tpk_render_nav / tpk_render_footer)
│   ├── content-helpers.php     ← ⭐ adatlekérés (Ajánlatok/Úticélok/Blog) + "Miért mi?" szöveg + al-oldal URL-ek
│   └── settings.php            ← admin "Kezdőlap" menüpont – szövegek kódmentes szerkesztése
├── templates/
│   ├── front-page.php          ← a teljes HTML dokumentum a főoldalhoz (nav-tól footerig)
│   └── page-wrapper.php        ← ua. minden MÁS kezelt oldalhoz (lásd fent)
└── assets/
    ├── css/frontend.css        ← a mockup 1:1 lefordítása + reszponzív töréspontok, branding CSS-változókban
    └── js/frontend.js          ← mobil hamburger-menü nyit/zár
```

## Reszponzivitás (v1.3.0-tól)

A v1.0.0 CSS szándékosan reszponzivitás nélkül készült (a felhasználó akkori
kérésére). v1.3.0-tól a `frontend.css` deszktop-alapú, két töréspont
szűkíti lejjebb (a fájl végén, "Reszponzivitás" szakaszban):
- **≤1024px** (tablet) – 3-4 oszlopos rácsok 2 oszlopra, kisebb paddingek,
  kisebb hero-cím.
- **≤768px** (mobil) – a nav a `.tpk-nav-toggle` hamburger-gombbal
  lenyíló menüvé alakul (`assets/js/frontend.js` kapcsolja a
  `.tpk-nav-menu--open` osztályt), a hero egymás alá rendeződik, minden
  rács 1 oszloposra vált, a záró CTA és a footer is oszloposan rendeződik.
- **≤400px** – apró finomhangolás (logó-szöveg, kártya-paddingek).

Ha módosítod a mockupot vagy új szekciót adsz hozzá, MINDIG ellenőrizd a
töréspontokat is – ez könnyen kimarad, mert a fájl teteje (deszktop
stílusok) és a reszponzív blokk (fájl vége) fizikailag távol van egymástól.

## Kapcsolódó változás a Travelpont Ajánlatok pluginban (v1.2.0)

A mockup a repjegy- és szállás-árat KÜLÖN sorban mutatja, ehhez a
Travelpont Ajánlatok plugin `fields.php`-ja két új mezőt kapott:
`tpa_repjegy_ar`, `tpa_szallas_ar`. A meglévő `tpa_ar` mostantól opcionális
("Ár – összesített") – ha üresen hagyod, a `tpa_teljes_ar()` függvény a
repjegy+szállás összegét adja vissza helyette. Részletek:
`travelpont-ajanlatok/TRAVELPONT-AJANLATOK-DOCS.md`.

## Al-oldalak linkjei (nav, "Összes ajánlat →" stb.)

A weboldal végleges oldaltérképe (`_Dokumentumok\sitemap.md`) különálló
Oldalakat ír elő: `/ajanlatok/`, `/uticelok/`, `/utikalauz/` (Bejegyzések
oldal), `/rolunk/`, `/kapcsolat/`. A kezdőlap sablon ezekre linkel a nav-ban,
a hero/záró CTA gombokban és a szekciók "Összes X →" linkjeiben –
`includes/content-helpers.php` `tpk_ajanlatok_url()` / `tpk_uticelok_url()` /
`tpk_utikalauz_url()` / `tpk_rolunk_url()` / `tpk_kapcsolat_url()`
függvényein keresztül.

Ezek `get_page_by_path()`-tal keresik meg a megfelelő slug-ú Oldalt (alap
slugok: `ajanlatok`, `uticelok`, `utikalauz`, `rolunk`, `kapcsolat` – a
`tpk_utikalauz_url()` előbb a WP natív "Bejegyzések oldala" beállítást
(`page_for_posts`) nézi meg). Ha egy Oldal még nem létezik:
- Ajánlatok/Úticélok/Útikalauz esetén a régi `#offers`/`#destinations`/`#blog`
  horgony-linkre esik vissza (a plugin friss telepítésen is működik, mielőtt
  az admin létrehozná az al-oldalakat).
- Rólunk/Kapcsolat esetén egyszerűen nem jelenik meg a nav-linkjük.

A slugok kódból felülírhatók (pl. ha más néven hozod létre az Oldalt):
`tpk_ajanlatok_oldal_slug`, `tpk_uticelok_oldal_slug`,
`tpk_utikalauz_oldal_slug`, `tpk_rolunk_oldal_slug`,
`tpk_kapcsolat_oldal_slug` filterekkel.

**FONTOS**: az `/ajanlatok/`, `/uticelok/`, `/utikalauz/`, `/rolunk/`,
`/kapcsolat/` Oldalak létrehozása és a WP "Beállítások → Olvasás" alatti
Kezdőlap/Bejegyzések-oldal beállítás admin feladat, ez a plugin nem hozza
létre őket. **Soha ne állítsd az Ajánlatok (vagy bármelyik tényleges
tartalom-)Oldalt a WP "Kezdőlap"-jának** – a WP a kezdőlapként beállított
Oldal saját URL-jét automatikusan a gyökérre (`/`) irányítja át
(`redirect_canonical()`), ami ellopná az adott Oldal saját URL-jét. Ehelyett
egy külön, üres "technikai" Oldalt válassz Kezdőlapnak (a
`travelpont-kezdolap` úgyis felülírja a gyökeret, a kiválasztott Oldal
tartalma sosem látszik).

## Admin szerkesztés (kód nélkül)

A WP Admin bal menüjében megjelenő **"Kezdőlap"** menüpont alatt (capability:
`manage_options`) az `includes/settings.php` egy beállítási oldalt ad, ahol a
hero cím/alcím, a jelvény- és gombszövegek, a "Miért mi?" 4 pontja, az üres
állapot szövegei, a záró CTA és a közösségi linkek (Instagram/Facebook)
kódírás nélkül szerkeszthetők. Ez egyetlen `tpk_settings` opciót ír a
`wp_options` táblába, és ugyanazokra a `tpk_*` filterekre iratkozik fel,
amiket korábban csak snippettel lehetett felülírni – tehát az alábbi
kódszintű megoldás is működik továbbra is, csak most már nem kötelező.

**Amit az admin felület NEM tud:** elrendezés, design, CSS, szekciók
sorrendje, a lekérdezések (`tpk_*_query_args`) módosítása – ezekhez továbbra
is a plugin kódjához kell nyúlni.

## Hogyan bővítsd kódból?

A statikus szövegek (hero cím/alcím, gombfeliratok, "Miért mi?" 4 pontja,
záró CTA szövege, közösségi média linkek) mind `apply_filters()`-en keresztül
íródnak ki, tehát kódmódosítás nélkül, egy kis `functions.php`-szerű
snippetből is felülírhatók (ha az admin felületnél finomabb, feltételes
logika kell):

```php
add_filter( 'tpk_hero_cim', function() {
    return 'Új főcím itt';
} );
add_filter( 'tpk_kozossegi_linkek', function() {
    return array( 'instagram' => 'https://instagram.com/travelpont', 'facebook' => '' );
} );
```

### Egyéb hookok
- `tpk_ajanlatok_query_args`, `tpk_orszagok_query_args`, `tpk_cikkek_query_args` – a három szekció lekérdezésének módosítása
- `tpk_miert_mi_pontok` – a "Miért mi?" 4 pontjának cseréje/bővítése
- `tpk_ajanlatok_ures_szoveg`, `tpk_uticelok_ures_szoveg` – üres állapot szövegei

## Telepítés

1. A `travelpont-kezdolap` mappát felmásolni ide: `wp-content/plugins/`
2. WP admin → Bővítmények → "Travelpont Kezdőoldal" → Bekapcsolás.
3. Ellenőrizd a Travelpont Ajánlatok plugint v1.2.0-ra (a repjegy/szállás
   ár mezők miatt) és töltsd ki néhány ajánlatnál.
4. Nincs más teendő – a kezdőlap a bekapcsolás pillanatától ezt a sablont
   mutatja.
