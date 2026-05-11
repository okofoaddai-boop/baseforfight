# BaseForFight UI/UX Leitlinien (Referenz)

Dieses Dokument ist die verbindliche Referenz fuer UI-Entscheidungen im Projekt.
Bei Konflikten zwischen schneller Umsetzung und UI-Qualitaet gilt dieses Dokument.

## 1. Zielbild

BaseForFight soll nicht wie ein technisches Admin-Panel wirken, sondern wie ein klar gefuehrtes Produkt fuer Trainer, Kaempfer, Manager und Veranstalter.

Designziel:
- wenig visuelles Rauschen
- klare Positionierung statt langer Erklaertexte
- selbsterklaerende Nutzerfuehrung

## 2. Referenz-Rendering (bff.baseforfight.de)

Die Seite [https://bff.baseforfight.de/](https://bff.baseforfight.de/) wird als Layout- und Klarheitsreferenz genutzt.

Was daran uebernommen wird:
- klarer Kopfbereich mit direktem Home-Anker
- schlanke Navigation mit wenigen, eindeutigen Eintraegen
- starke Inhaltsflaeche mit klarer Hierarchie (H1 -> Kernaussage -> Aktion)
- Eventbereich als eigenstaendiger Block mit klaren Datumsankern
- Login/Anmeldung nicht mitten im Content, sondern sauber separiert

## 3. Platzierung von Logo und Icons

Diese Regeln sind verbindlich:

1. Primarlogo
- Position: oben links im Header.
- Verhalten: immer klickbar auf die Startseite.
- Wiederholung: pro Seite genau einmal als Hauptmarke.

2. Brand-Icon
- Einsatz: klein im Header oder als Akzent im Hero-Titel.
- Nicht gleichzeitig mehrfach in Karten, Sektionen und Footer verteilen.

3. Favicon/App-Icon
- Nur Browser-Tab und App-Metadaten.
- Nicht als Ersatz fuer das Hauptlogo verwenden.

4. Funktionsicons
- Nur fuer Navigation oder Status mit echtem Mehrwert.
- Keine rein dekorativen Icons neben jedem Label.

## 4. Die 5 UX-Regeln fuer alle neuen Screens

1. Informationshierarchie und visuelle Prioritaet
- Jede Seite braucht genau eine Hauptaufgabe.
- Es gibt genau eine primaere CTA pro Hauptbereich.
- Nebenaktionen sind visuell klar nachrangig.

2. Konsistenz bei Typografie, Abstaenden und Komponenten
- Einheitliche Abstandslogik (z. B. 8/12/16/24/32).
- Einheitliche Button-Hierarchie (primary, secondary, ghost).
- Wiederkehrende Muster an gleicher Stelle (z. B. Seitenkopf, Aktionen, Filter).

3. Lesbarkeit und Scanbarkeit
- Kurze Abschnitte, klare Zwischenueberschriften, wenig Fliesstext.
- Relevante Inhalte zuerst, Details spaeter.
- Tabellen und Karten nur mit wirklich noetigen Feldern.

4. Barrierefreiheit
- Kontrast und Fokuszustand muessen sichtbar sein.
- Interaktive Elemente sind per Tastatur erreichbar.
- Semantik: korrekte Heading-Struktur, Labels, Landmarken.

5. Progressive Disclosure
- Standardansicht zeigt nur das, was fuer den naechsten Schritt noetig ist.
- Erweiterte Optionen in Tabs, Drawer, Details oder Sekundaerbereichen.
- Keine ueberladenen Startscreens mit allen Fachdetails.

## 5. Anti-Pattern (nicht mehr umsetzen)

- Zu viele gleich starke Buttons in einer Zeile.
- CTA-Widersprueche (z. B. Login zeigen, obwohl User eingeloggt ist).
- Lange Erklaertexte ohne klare Struktur oder Aktionsfokus.
- Doppelte Navigation auf derselben Ebene.
- Markenlogo in mehreren Varianten gleichzeitig im sichtbaren Bereich.

## 6. Definition of Done fuer UI-Aenderungen

Eine UI-Aenderung gilt erst als fertig, wenn:

1. Die Seite eine klare Hauptaktion hat.
2. Header, Navigation und Markenplatzierung den Regeln aus Abschnitt 3 entsprechen.
3. Die 5 UX-Regeln aus Abschnitt 4 sichtbar umgesetzt sind.
4. Die Ansicht in Desktop und Mobile strukturiert bleibt.
5. Keine Gast-Aktionen in authentifizierten Hauptbereichen auftauchen.

## 7. Anwendung im aktuellen Projekt

Diese Leitlinien sind sofort auf folgende Seiten anzuwenden:
- Welcome
- Club-Portal
- SuperUser-Dashboard

Bei jeder Erweiterung gilt: erst Struktur und Prioritaet, dann Styling-Details.