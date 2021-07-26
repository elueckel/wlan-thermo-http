# WLAN Thermo BBQ Modul 

Das WLAN Thermo Modul für Symcon basiert auf der HTTP Webservice Schnittstelle des WLAN Thermo BBQ Thermometers (https://wlanthermo.de/) und ermöglich die komfortable Nutzung der WLANThermo Grill Thermometer in Verbindung mit Symcon. Das Modul kann 

* bis zu 6 Sensoren abfragen
* jeder Sensor kann einzeln aktiviert werden 
* zentraler Timer für alle Sensoren (definitiert die Häufigkeit der Abfrage)
* einzelne Alarme für min/max
* Warnung via Message Control und Email bei zu hoher/niedriger Temperatur, wie auch "nicht Erreichbarkeit"
* das Modul deaktiviert sich nach einer bestimmten Anzahl von Zyklen und schickt, wenn aktivert, Warnungen bei der Hälfte bez. beim ausschalten
* alle Variablen werden persistent angelegt und können so über das Webfront eingebunden werden
* wenn man "die Active Variable" mit einer Aktion verbindet, die mit einem Präsenzmelder (z.B. im Unifi Modul) gekoppelt ist, schaltet sich das Modul selbst ein

