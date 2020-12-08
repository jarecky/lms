W tej chwili brakuje:
- obslugi RB z PoE (odczyt pradu, itp z portow)
- kompaktowanie bazy danych 

Co dziala:
- odczyt sygnalow z mikrotikow on-line wprost z LMS
- odczyt stanow portow ether (ale musza byc opisane - port 1 to ether1 itp)
- wrzucanie do bazy danych z nadajnikow (urzadzen podpietych do sektorow
  radiowych - ja ten skrypt odpalam co godzine)

Konfiguracja:
- W phpui w sekcji mikrotik sa zmienne login i haslo
- sektory musza sie nazywac: "cos-wlanx" gdzie wlanx to nazwa interfejsu (w
  innym przypadku odczyt bedzie ze wszystkich interfejsow na danym MT)

