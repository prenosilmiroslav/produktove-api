Informace o projektu
=================

Projekt je zapozdřený do docker kontejnerů. Využívá se:

- PHP 8.2
- Nette 4
- PostgreSQL
- Apache
- Adminer

Instalace, nastavení a spuštění
=================

1. Přejmenovat soubor `config/example.local.neon` na `config/local.neon`
2. V konzoli spustit v rootu projektu `docker-compose build`
3. Po dokončení buildu spustit v konzoli v rootu projektu `docker-compose up`
4. Po nastartování kontejnerů budou k dispozici:

   - Databázový server PostgreSQL na adrese `db-container` a portu `5432`. Přihlašovací jméno je `postgres` a heslo `passwdApi`
   - Prohlížet databázi můžeme na adrese `http://localhost:8080` (zde je nainstalovaný adminer)
   - Samotné API běží na `http://localhost:8000/api/v1`

Adresy API k testování
=================

API přijímá pouze JSON RAW body (v případě vložení a editace produktu). U každé operace (kromě DELETE) se vrací aktuální hodnoty daného produktu.

V configu `local.neon` je v sekci `parameters` - `auth` - `secretToken`, který je nutný zasílat v hlavičce `Authorization: Bearer <secretToken>`.

- Vložení nového produktu: `[POST] http://localhost:8000/api/v1/insert`
- Editace produktu: `[PUT] http://localhost:8000/api/v1/update/<id>`
- Smazání produktu: `[DELTE] http://localhost:8000/api/v1/delete/<id>`
- Načtení konkrétního produktu: `[GET] http://localhost:8000/api/v1/get/<id>`
- Načtení seznamu produktů: `[GET] http://localhost:8000/api/v1/get`

U načítání seznamu produktů je možné provést řazení, filtraci a stránkovat mezi záznamy a to přidáním GET parametru do URL.

- Parametr `limit` - Počet záznamů na stránku (výchozí 10)
- Parametr `page` - Stránka, kterou chceme zobrazit (výchozí 1)
- Parametr `name` - Filtrace podle názvu (LIKE)
- Parametr `priceFrom` - Filtrace podle ceny od
- Parametr `priceTo` - Filtrace podle ceny do
- Parametr `createdFrom` - Filtrace podle Datumu vytvoření od (formát Y-m-d)
- Parametr `createdTo` - Filtrace podle Datumu vytvoření do (formát Y-m-d)
- Parametr `updatedFrom` - Filtrace podle Datumu aktualizace od (formát Y-m-d)
- Parametr `updatedTo` - Filtrace podle Datumu aktualizace do (formát Y-m-d)
- Parametr `order [name, price, createdAt, updatedAt]` - Možnost řazení podle těchto sloupců (Výchozí je ID)
- Parametr `by [ASC, DESC]` - Typ řazení (Výchozí je ASC)

Možnosti rozšíření aplikace
=================

Zabezpečení
-----------

V aplikaci je použitá jednoduchá autorizace pomocí Bearer tokenu, který se předává v hlavičce Authorization. 

Jedná se o základní zabezpečení, které lze jednoduše "prolomit" ukradením daného tokenu. Pro lepší zabezpečení by bylo lepší použít JWT token, který má časové razítko a tak dochází po určitém časovém intervalu k jeho neplatnosti.

Možnosti dokumentace
-----------

Pro generování dokumentace se dá využít například nástroj Swagger. Osobně mám zkušenosti se službou Apiary.io, která nabízí možnost vytvořit si Mock server a zároveň API testovat. Je zde možnost zautomatizovat generování dokumentace například při CI/CD. 

Návrh verzování API
-----------

V aplikaci jsem načrtnul jak bych řešil více verzí v rámci jednoho kódu. Jednotlivé verze lze rozdělit do Modulů (viz `ApiV1Module`) a přidáním routeru lze takto mít více verzí API.

`$router->addRoute('api/v2/<action>[/<id>]', 'ApiV2:Api:default');`

Filtrace záznamů a stránkování
-----------

Filtraci a stránkování jsem popsal v sekci **Adresy API k testování** kdy tedy stačí přidat GET parametr (lze je i kombinovat).

