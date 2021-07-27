# WLAN Thermo (HTTP) BBQ Controller 

Das WLAN Thermo Modul für Symcon basiert auf der HTTP Webservice Schnittstelle des WLAN Thermo BBQ Thermometers (https://wlanthermo.de/) und ermöglich die komfortable Nutzung der WLANThermo Grill Thermometer in Verbindung mit Symcon. Das Modul kann 

* bis zu 6 Sensoren abfragen
* jeder Sensor kann einzeln aktiviert werden 
* zentraler Timer für alle Sensoren (definitiert die Häufigkeit der Abfrage)
* Temperaturen für Min/Max werden auf das Thermometer übertragen
* einzelne Alarme für min/max
* Warnung via Message Control und Email bei zu hoher/niedriger Temperatur, wie auch "nicht Erreichbarkeit"
* das Modul deaktiviert sich nach einer bestimmten Anzahl von Zyklen und schickt, wenn aktivert, Warnungen bei der Hälfte bez. beim Ausschalten
* alle Variablen werden persistent angelegt und können so über das Webfront eingebunden werden
* wenn man "die Active Variable" mit einer Aktion verbindet, die mit einem Präsenzmelder (z.B. im Unifi Modul) gekoppelt ist, schaltet sich das Modul selbst ein
* Kerntemperaturen für diverse Fleischarten im Modul als Variablen hinterlegt - diese können z.B. im Webfront eingebunden werden um Werte nachzuschlagen. Eine automatische Übernahme ist nicht geplant - ebenso sind die Texte nur in Deutsch.

## Setup
Die Einrichtung des Moduls ist sehr einfach. 
1. Download des Moduls via Module Store oder github https://github.com/elueckel/wlan-thermo-http 
2. Anlegen der Instanz: WLAN Thermo (HTTP) BBQ Controller
3. Im Modul die IP-Adresse des Thermometers angeben
4. Die Kanäle wählen, die genutzt werden sollen

Damit wäre das Modul grundsätzlich einsatzbereit

## Nutzung
Das Modul liest die Temperatur alle xx Sekunden (entsprechend dem eingestellten Wert aus) und stellt sie in der Temperaturvariable zur Verfügung. Sollte die Archivierung aktiviert sein, so sieht man auch den Temperaturverlauf für den aktuell Grillvorgang. 

Die Werte für minimale und maximale Temperatur werden bei Veränderung im Webfront oder der App zum Thermometer zurückgeschrieben. 

Die Variable Status zeigt an, wie sich die Temperatur verhält bez. ob es z.B. aus ist.

## Nachrichten
Ein wichtiger Punkt bei der Entwicklung war, umfangreiche Benachrichtigungen zu ermöglichen. So ist es möglich Nachrichten in der Symcon App aber auch als Email zu erhalten, wenn die Temperatur genau richtig (zwischen Min/Max oder unter Max), zu warm oder kalt ist. 
Weiterhin werden Nachrichten bei Auto An/Aus oder leerer Batterie versendet, wenn gewünscht. 
Alle Texte können frei vergeben werden - somit kann auch die Mehrsprachigkeit dargestellt werden. 

## Archivfunktion
Da man beim Grillen evtl. auch den Temperaturverlauf sehen will, kann man die Aufzeichnung aktivieren. Diese zeichnet dann den Temperaturverlauf pro Kanal auf. Beim Deaktivieren des Moduls werden alle Daten aber wieder automatisch gelöscht!

## Auto An/Aus
Das Modul wird über die "Aktiv" Variable aktiviert. Dies kann natürlich im Webfront oder der App passieren. Sollte man z.B. ein Unifi Gateway und das Modul nutzen, so kann man aber auch die Anwesenheit (über die IP) des Thermometers erfassen und das Modul so aktivieren. 
Das Modul deaktiviert sich, wenn es für eine bestimmte Anzahl von Zyklen das Thermometer nicht erreichen kann. Zyklen sind hier die Abfrageintervalle (also wären 5 Zyklen bei 60 Sekunden - 3 Minuten). Das Modul wird bei Erreichen von 50% der Zyklen eine Email oder Nachricht senden, dass das Thermometer nicht zu erreichen ist. Damit soll sichergestellt werden, dass z.B. bei leerer Batterie Gegenmaßnahmen ergreifen werden können. 


## Version
1.0 - 26-07-2021
* Erstes Release
* Unterstützung von 6 Kanälen 
* Auto An/Aus
* Nachrichten via Webfront und Email (Temperatur, Erreichbarkeit)
* Temporäre Aufzeichnung von Variablen
* Kerntemperaturen für Schwein, Rind, Lamm, Wild, Kalb und Fisch
