príprava
1. načítať config
2. vyčistiť tabulku data od starých záznamov
4. získať rozsah dát ktorý bude vstupovať do štatistík - dátum, čas alebo ID posledného záznamu
3. truncate tabuliek graph a statistics

generovanie
5. Pripraviť dotaz na kombinácie (12x union)
6. Získať zoznam všetkých možných kombinácií záznamov z dotazu v bode 4
7. prejsť všetky kombinácie z výsledku dotazu v riadku 5

8. generovať dáta pre každý riadok tabuľky traffic a spracovať sumárne čísla
9. vygenerovať dodatočné sumárne dáta do tabulky statistics
