# Travelpont Kezdőoldal plugin – dokumentáció

> Verzió: 1.0.0 · A Travelpont Ajánlatok / Úticélok pluginek architektúráját
> követi (`D:\travelpont.hu\_Saját_pluginek\`)
> SZABÁLY: minden módosításkor verziót emelünk a fő fájl fejlécében
> (cache-buster + követhetőség).

## Mit tud?

A jóváhagyott kezdőoldal-mockup (`Travelpont - kezdőoldal mockup.html`)
pixelpontos megvalósítása, valós adatokkal feltöltve:

- **Navigáció** – logó (CSS-ből épített boarding-pass jelvény, sárga
  repülő-ikonnal) + `#offers` / `#destinations` / `#blog` horgony-linkek.
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

## Miért nem `front-page.php` a témában?

A projekt korábbi konvenciója (lásd a Travelpont Ajánlatok/Úticélok
pluginek `single-display.php`-ja), hogy a megoldásoknak témafüggetlennek
kell lenniük (a projekt jelenleg Twenty Twenty-Five és Hello Elementor
között vált). Azok a pluginek ezt a `the_content` szűrővel érik el – de az
csak a MEGLÉVŐ téma-elrendezésbe illeszt be egy dobozt, itt viszont a
mockup SAJÁT navigációt és footert is hoz, amit nem lehet egy téma nav/
footer köré illeszteni duplikáció nélkül.

Ezért ez a plugin a `template_include` WordPress-szűrővel adja a TELJES
HTML dokumentumot (`includes/template-loader.php` → `templates/front-page.php`),
a téma header.php/footer.php-ját megkerülve – de a `wp_head()`/`wp_footer()`
hívásokkal így is minden más plugin (SEO, statisztika stb.) rendben
belekerül az oldalba. Ez ugyanúgy túléli a témaváltást, mint egy szokásos
`front-page.php`, csak nem kell a téma mappájához nyúlni (ami frissítéskor
elveszne, ha nem gyerektéma).

**Ha ezt a plugint aktiválod, a WordPress "Beállítások → Olvasás" alatti
kezdőlap-beállítástól FÜGGETLENÜL ez a sablon jelenik meg a főoldalon**
(`is_front_page()` alapján – működik "legutóbbi bejegyzések" ÉS "statikus
oldal" módban is).

## Fájlszerkezet

```
travelpont-kezdolap/
├── travelpont-kezdolap.php     ← fő fájl: konstansok, betűtípus + CSS enqueue
├── includes/
│   ├── template-loader.php     ← template_include szűrő
│   └── content-helpers.php     ← ⭐ adatlekérés (Ajánlatok/Úticélok/Blog) + "Miért mi?" szöveg
├── templates/
│   └── front-page.php          ← a teljes HTML dokumentum (nav-tól footerig)
└── assets/css/
    └── frontend.css            ← a mockup 1:1 lefordítása, branding CSS-változókban
```

## Kapcsolódó változás a Travelpont Ajánlatok pluginban (v1.2.0)

A mockup a repjegy- és szállás-árat KÜLÖN sorban mutatja, ehhez a
Travelpont Ajánlatok plugin `fields.php`-ja két új mezőt kapott:
`tpa_repjegy_ar`, `tpa_szallas_ar`. A meglévő `tpa_ar` mostantól opcionális
("Ár – összesített") – ha üresen hagyod, a `tpa_teljes_ar()` függvény a
repjegy+szállás összegét adja vissza helyette. Részletek:
`travelpont-ajanlatok/TRAVELPONT-AJANLATOK-DOCS.md`.

## Hogyan bővítsd?

A statikus szövegek (hero cím/alcím, gombfeliratok, "Miért mi?" 4 pontja,
záró CTA szövege, közösségi média linkek) mind `apply_filters()`-en keresztül
íródnak ki, tehát kódmódosítás nélkül, egy kis `functions.php`-szerű
snippetből felülírhatók:

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
