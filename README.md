# üK295 LB1: Produkt- und Kategorien-API

## Projektübersicht

Dieses Projekt entstand im üK295 «Backend für Applikationen realisieren». Die API ermöglicht das Erstellen, Abrufen, Ändern und Löschen von Produkten und Kategorien eines Online-Shops.

Die Anwendung verwendet PHP und Slim 4. Geschützte Endpoints erfordern ein gültiges **JWT**. Die Schnittstellen sind mit OpenAPI dokumentiert und über Swagger UI einsehbar.

Autorin: Stefanie Gerber

## Technologien

* PHP 8
* Slim Framework 4
* MariaDB mit MySQLi
* Composer
* ReallySimpleJWT
* swagger-php und Swagger UI
* Bruno für API-Tests

## Projektstruktur

| Pfad                     | Inhalt                                           |
|--------------------------|--------------------------------------------------|
| `config/`                | Lokale Konfiguration                             |
| `database/uek295.sql`    | Datenbankdump mit Tabellenstruktur und Daten     |
| `public/index.php`       | Einstiegspunkt der API                           |
| `public/api/`            | Routendefinitionen                               |
| `public/swagger.php`     | Generierung der OpenAPI-Dokumentation als YAML   |
| `public/swagger-ui/`     | Anzeige der API-Dokumentation                    |
| `src/Controller/`        | Verarbeitung der API-Anfragen                    |
| `src/authentication.php` | JWT-Erstellung und Authentifizierungs-Middleware |
| `src/database.php`       | Datenbankverbindung                              |
| `src/validation.php`     | Gemeinsame Eingabeprüfungen                      |
| `vendor/`                | Composer-Abhängigkeiten                          |

## Lokale Einrichtung

1. Das Projekt im `htdocs`-Verzeichnis von XAMPP bereitstellen.
2. Apache und MySQL starten.
3. Die Datenbank `uek295` erstellen und `database/uek295.sql` importieren.
4. Im Projektverzeichnis die Abhängigkeiten installieren:

   ```bash
   composer install
   ```

5. Die Datei `config/config.json` erstellen und die lokalen Einstellungen eintragen.
6. Die API mit der Bruno-Collection prüfen.

Die folgenden URLs setzen voraus, dass das Projekt direkt im Webroot liegt und die Rewrite-Regeln der `.htaccess` verarbeitet werden.

## Konfiguration

Die Datei `config/config.json` wird nicht ins Git-Repository hochgeladen. Der Dozent erhält die vollständige Konfiguration separat mit der technischen Dokumentation.

Die Konfiguration enthält folgende Abschnitte:

| Abschnitt  | Einstellungen                          |
|------------|----------------------------------------|
| `database` | `host`, `username`, `password`, `name` |
| `jwt`      | `secret`, `issuer`, `lifetime`         |
| `auth`     | `username`, `password`                 |

Passwörter und JWT-Schlüssel werden nicht in dieser README veröffentlicht.

## API-Endpoints

Basis-URL: `http://localhost/api/v1`

| Methode | Pfad                      | Funktion                                                |
|---------|----------------------–----|---------------------------------------------------------|
| POST    | `/authenticate`           | Anmelden und JWT erhalten                               |
| GET     | `/categories`             | Alle Kategorien abrufen                                 |
| GET     | `/category/{category_id}` | Eine Kategorie abrufen                                  |
| DELETE  | `/category/{category_id}` | Eine Kategorie löschen                                  |
| PATCH   | `/category/{category_id}` | Eine Kategorie teilweise ändern                         |
| POST    | `/category`               | Eine Kategorie erstellen                                |
| GET     | `/products`               | Alle Produkte abrufen                                   |
| GET     | `/product/{product_id}`   | Ein Produkt abrufen                                     |
| DELETE  | `/product/{product_id}`   | Ein Produkt löschen                                     |
| POST    | `/product`                | Ein Produkt erstellen                                   |
| PUT     | `/product/{sku}`          | Ein Produkt über seine SKU erstellen oder aktualisieren |

Beim PUT-Endpoint stammt die SKU aus dem URL-Pfad. Existiert sie bereits, wird das Produkt aktualisiert. Andernfalls wird ein neues Produkt erstellt.

## Authentifizierung

Die Anmeldung erfolgt über `POST /api/v1/authenticate` mit `username` und `password` im JSON-Body.

Bei erfolgreicher Anmeldung wird das JWT im JSON-Feld `token` zurückgegeben und als Cookie `token` gesetzt. Dieses Cookie muss bei weiteren API-Anfragen mitgesendet werden.

Alle Produkt- und Kategorie-Endpoints sind geschützt. Ohne gültiges JWT antwortet die API mit `401 Unauthorized`.

## Datenmodell

Die Datenbank besteht aus den Tabellen `product` und `category`.

Ein Produkt kann einer Kategorie zugeordnet sein. Die Zuordnung erfolgt über `product.id_category`. Wird eine Kategorie gelöscht, bleiben die Produkte erhalten und ihre Kategoriezuordnung wird auf `NULL` gesetzt.

## API-Dokumentation

Swagger UI:

`http://localhost/public/swagger-ui/`

OpenAPI-Dokumentation im YAML-Format:

`http://localhost/public/swagger.php`

Die YAML-Ausgabe wird aus den OpenAPI-Attributen der Controller erzeugt.

## Tests

Die API wurde mit der vorgegebenen Bruno-Collection getestet. Die technische Dokumentation beschreibt die Endpoints, Statuscodes und Antwortstrukturen.

## Dokumentation und Abgabe

Die technische Dokumentation und die lokale Konfiguration werden dem Dozenten separat übergeben. Der Datenbankdump befindet sich unter `database/uek295.sql`.
