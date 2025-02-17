<?php
/******************************************************************************
//  Hier werden die Kunden spezifischen Einstellungen         (c) U.Kunz  2016
//  vorgenommen, damit keine der Daten bei einem Softwareupdate
//  überspielt werden. Diese Datei bitte mit Vorsicht
//  ändern. Die Form und die Schreibweise darf in keinen Fällen
//  geändert werden. Weitere Hilfe finden Sie im FORUM   www.solaranzeige.de
//
//  Diese Datei ist hier zu finden:  /var/www/html/user.config.php
//
//
******************************************************************************/
//
/******************************************************************************
//  REGLER  und  WECHSELRICHTER       REGLER  und  WECHSELRICHTER      REGLER
******************************************************************************/
//  Diese Regler knnen derzeit mit der Software benutzt werden:
//
//  Welcher Regler wird benutzt?
//
//   1 = ivt-Hirschau Regler SCPlus oder SCDPlus
//
//   2 = Steca Regler Tarom 6000 und Tarom 4545
//
//   3 = Regler der Tracer Serie  z.B. Tracer2210A
//       mit RS-485 zu USB Anschlusskabel (MODBUS) (Unbedingt FTDI Chipsatz)
//
//   4 = BlueSolar oder SmartSolar Regler von Victron-energy mit
//       VE.Direkt zu USB Anschlusskabel
//       --------------------------------------------------------------------
//       [Im Moment noch nicht in Betrieb]
//       Zusätzlich kann ein MultiPlus Wechselrichter mit einem MK3 USB Kabel
//       angeschlossen werden.
//
//   5 = Micro Wechselrichter von AEconversion z.B. INV250-45
//       mit RS-485 zu USB Anschlusskabel   (Unbedingt FTDI Chipsatz)
//
//   6 = Victron BMV 7xx Batteriewächter
//       mit VE.direct zu USB Kabel
//
//   7 = Voltronic Geräte der Axpert Serie
//       Effekta Geräte der AX und HX Serie,
//       Steca Solarix PLI 5000-48,
//       InfiniSolar PIP Serie 3k,
//       MPPSolar PIP HSP/MSP , MS/MSX und MSD Serien
//       EAsun ISolar SPV SMV 1KVA-5KVA Inverter
//       EAsun IGrid VE
//       mit einfachem  USB Kabel (A und B Stecker)
//       (Kein Seriell zu USB Wandler!)
//
//   8 = InfiniSolar V Serie , MPP Solar HSE/MSE Serie und viele Baugleiche.
//       mit einfachem USB Kabel (A und B Stecker) (Kein Seriell zu USB Wandler!)
//
//   9 = MPPSolar MPI Hybrid Serie 3 Phasen Inverter
//       Baugleich: FSP 5,5 Hybrid und Panta 10 Hybrid
//       mit einfachem USB Kabel (A und B Stecker) (Kein Seriell zu USB Wandler!)
//
//  10 = SolarMax S-Serie
//       mit Ethernet (LAN) Kabel Anschluss.
//
//  11 = Phoenix Wechselrichter von Victron mit VE.direct Kabel Anschluss
//
//  12 = Fronius Symo Wechselrichter  inkl. Hybrid Geräte und Fronius Prymo
//       MIT Ethernet (LAN) Kabel Anschluss
//
//  13 = Joulie-16  Batterie-Management-System von AutarcTech  (BMS)
//       Mit LAN Anschluss
//
//  14 = Rover Laderegler von Renogy, Toyo von SRNE und baugleiche
//       mit USB Kabel Anschluss
//
//  15 = PYLONTECH US2000B Plus Batteriespeicher  Batterie-Management-System (BMS)
//       Mit Seriell zu USB Adapter. Den Console Port im Gerät benutzen.
//
//  16 = SolarEdge 3 Phasen Wechselrichter mit LAN Schnittstelle
//       (Für 1 Phasen Geräte muss das Dashboard geändert werden)
//
//  17 = KOSTAL Plenticore Wechselrichter und Pico der 3. Generation
//       mit LAN Schnittstelle
//
//  18 = S10E und S10 mini von E3/DC mit LAN Schnittstelle   (Port 502)
//       Nur im Simple-Mode möglich.
//
//  19 = eSmart3  Laderegler  40A, 50A, 60A
//
//  20 = SolarEdge 3 Phasen Wechselrichter ohne MODBUS Zähler
//       mit Ethernet (LAN) Kabel Anschluss.
//       (Für 1 Phasen Geräte muss das Dashboard geändert werden)
//
//  21 = KOSTAL Piko mit RS485 Anschluss
//       (in der user.config.php muss $WR_IP = "0.0.0.0"; stehen!)
//       Eine IP Adresse nur eintragen, wenn man den Piko über das LAN auslesen will.
//       Beim PIKO MP Plus in der user.config.php zusätzlich
//       $SerielleGeschwindigkeit = 38400;  eintragen.
//
//  22 = Smart Energy Meter von KOSTAL oder Anderen
//
//  23 = Sonoff / Shelly Relais:
//       Bei folgenden Relais muss der "DeviceName" wie folgt eingetragen sein:
//       Shelly 2.5    = "Shelly 2.5"
//       Shelly 1      = "Shelly 1"
//       Shelly 1PM    = "Shelly 1PM"
//       Sonnof POW R3 = "Sonoff POW R3"
//
//       Beispiel der Änderung:
//       Auf der Tasmota Konsole einfach    DeviceName Shelly_1    eingeben.
//
//  24 = Infini xx KW Hybrid Wechselrichter. Protokoll 16  1 Phase
//
//  25 = SonnenBatterie mit LAN Anschluss  [ API Version 1 ]
//
//  26 = MPPSolar 5048MK und 5048GK ( PIP MK und GK Serie )
//       EAsun ISolar V III Off-Grid lnverter
//       sowie Baugleiche mit USB Anschluss
//
//  27 = SMA Wechselrichter Sunny Island und Sunny Tripower 
//       Modbus TCP mit LAN Anschluss
//
//  28 = HRDi marlec Laderegler für PV und Windgenerator
//       mit Seriell - USB Adapter
//
//  29 = go-e Charger  (Wallbox)
//
//  30 = Keba Wallbox  P20 + P30
//
//  31 = Shelly EM und Shelly 3EM
//
//  32 = KACO Wechselrichter der TL3 Serie
//
//  33 = Labornetzteil JOY-IT  JT-DPM8624
//
//  34 = SDM630  Energy Meter (RS485 Anschluss)
//
//  35 = Wallbe Wallbox  Eco 2.0 und andere.
//
//  36 = Delta Wechselrichter SI 2500  mit RS485 Anschluss
//
//  37 = Simple EVSE WiFi Wallbox
//
//  38 = ALPHA ESS T10  Wechselrichter + Batteriesystem
//
//  39 = openWB  Wallbox
//
//  40 = Phocos PH1800 Wechselrichter
//
//  41 = PylonTech US 3000 A mit RS485 Schnittstelle  Anz. Batterie-Packs auch angeben!
//
//  42 = PV18-3KW VHM oder PV1800 VHM oder Baugleiche mit RS485 Schnittstelle
//
//  43 = Senec Stromspeicher
//
//  44 = Webasto Wallbox
//
//  45 = Phocos Any-Grid mit RS232 zu USB Adapter
//
//  46 = Huawei SUN2000 Wechselrichter ältere Modelle bis Modell ID 100 (siehe Regler 56)
//
//  47 = Phoenix Contact Wallbox
//
//  48 = Growatt Wechselrichter
//
//  49 = Huawei SmartLogger  DTSU666
//
//  50 = SDM230 Zähler 1 Phase
//
//  51 = Siemens PAC2200 SmartMeter
//
//  52 = Goodwe Wechselrichter der Serien ES , EM und SBP (mit Batterie)
//
//  53 = Solarlog Pro 380 - Mod
//
//  54 = SMA Energy Meter 2.0
//
//  55 = Studer Xtender Wechselrichter inkl BSP
//
//  56 = Huawei SUN2000 Wechselrichter M0 , M1 und M2 Modelle
//
//  57 = Daly BMS China
//
//  58 = SolarEdge Ertragszähler WND-3Y400-MB
//
//  59 = EASUN POWER Solar Inverter 3KVA mit seriellem Anschluss
//
//  60 = Hardy Barth Wallbox
//
//  61 = SMARTPI Zähler
//
//  62 = Huawei Wechselrichter mit SDongle (LAN Anschluss)
//
//  63 = cFos Wallbox
//
//  64 = Goodwe Wechselrichter der Serien ET, EH, BH, BT
//
//  65 = RCT Wechselrichter 
//
//  66 = Kostal Piko CI
//
//  67 = Goodwe Wechselrichter XS Serie
//
//  68 = VARTA Pulse Neo
//
//  69 = Vestel Wallbox
//
//  70 = Sungrow Wechselrichter
//
//  71 = EASUN SMG II Wechselrichter
//
//  72 = HomeMatic Gaszähler (und andere Geräte. Siehe Homematic.pdf Dokument)
//
//  73 = SofarSolar Wechselrichter x.x KTL-X (ältere Geräte)
//
//  74 = Carlo Gavazzi EM24 SmartMeter (Baugleich Victron EM24)
//
//  75 = Hager Meter (Stromzähler)
//
//  76 = Hoymiles Microwechselrichter
//
//  77 = AX Licom Box von Effekta
//
//  78 = Innogy Wallbox / Compleo Wallbox
//
//  79 = IAMMETER WEM3080T Stromzähler
//
//  80 = Solax X3 Wechselrichter + qcells Q.HOME ESS HYB-G3
//
//  81 = my-PV  AC-THOR  und AC-THOR 9s
//
//  82 = Solis Wechselrichter
//
//  83 = JK-BMS (Batterie Management System)
//
//  84 = SofarSolar HYD Modelle  [ neu Regler 87 ]
//
//  85 = Ökofen Pelletronic   Pelletofen
//
//  86 = Victron Venus OS GX, CCGX, Cerbo GX
//
//  87 = SofarSolar alle Modelle - [ neu, mit mehr Werten ]
//
//  88 = Ahoy-DTU
//
//  89 = OpenDTU
//
//  90 = NILAN Wärmepumpe
//
//  91 = SEPLOS BMS
//
//  92 = FSP MES BMS
//
//  93 = Deye Wechselrichter
//
//  ---------------------------------------------------------------------------
//
$Regler = Utils::getEnvAsString("SA_REGLER","0");
//
/******************************************************************************
//  Raspberry Gerätenummer   Raspberry Gerätenummer   Raspberry Gerätenummer
//  Falls mehr als ein Gerät pro Raspberry betrieben wird.
//  Es ist die Reihenfolge der Geräte und taucht auch in der Nummerierung
//  der  x.user.config.php Dateien auf
******************************************************************************/
//  GeräteID bzw. GeräteNummer Muss gleich mit der x.user.config.php sein.
//  Bitte nur bei einer Multi-Regler-Version ändern.  [ 1 bis 6 ]
$GeraeteNummer = Utils::getEnvAsString("SA_GERAETENUMMER","1");
//
//  Bei einem Micro Wechselrichter von AEconversion oder LiCom Box von Effekta
//  und anderen Geräten, die für den Zugang eine Seriennummer benötigen.
//  --------------------------------------------------------------------------
//  Z.B. Typ INV250-45 oder INV500-60 oder LiCom Box von Effekta
//  Steht auf dem Gerät! Ist 10 stellig. Serial-No. 0607600...
//  Bitte alle 10 Stellen hier eintragen.
//  Bei der AX LiCom Box von Effekta sind es 14 Stellen.
//  Bei den neuen AEconversion Geräten den Bootcode hier eintragen.
$Seriennummer = Utils::getEnvAsString("SA_SERIENNUMMER","0000000000");
//  Wird nur in seltenen Fällen gebraucht.
$Zugang_Kennwort = Utils::getEnvAsString("SA_ZUGANG_KENNWORT","");                        // wird zur Zeit nicht benutzt
//  Falls ein WLAN HF2211 serial   Gateway benutzt wird true eingeben
$HF2211 = Utils::getEnvAsBoolean("SA_HF2211",false);
//
//  Nur bei PylonTech BMS US3000..       ($Regler = "41" )
//  und den neuen US2000C aus dem Jahr 2019 und später
//  Anzahl der vorhandenen Batteriepacks und Modell 2000 / 3000
//  -------------------------------------------------------------------
$Batteriepacks = Utils::getEnvAsString("SA_BATTERY_PACK","1"); //                                Regler = "41"
$PylonTech = Utils::getEnvAsString("SA_PYLON_TECH","2000");  //                                Regler = "41"
//
//
//  Ethernet Kabelverbindung:          Local Area Network  (LAN)
//  Alle Geräte, die über das LAN angesprochen und ausgelesen werden,
//  oder ein Serial Device Server, wie z.B. der HF2211 oder der Elfin-EW11,
//  dazwischen geschaltet haben, bitte hier IP und Port eintragen und
//  falls erforderlich die Device ID. (Geräteadresse = WR_Adresse)
//  Die Geräte Adresse wird auch manchmal bei RS485 Verbindungen benutzt.
//  -------------------------------------------------------------------
//  Bitte die Daten aus dem Gerät übernehmen
//
$WR_IP = Utils::getEnvAsString("SA_WR_IP","0.0.0.0");  //  Keine führenden Nullen!  67.xx Ja!, 067.xx Nein!
$WR_Port = Utils::getEnvAsString("SA_WR_PORT","12345");
$WR_Adresse = Utils::getEnvAsInteger("SA_WR_ADDRESS",1);   //  Achtung Adresse als Dezimalzahl eingeben / 1 bis 256
//                       Maximal "256" = Hex FF
/*****************************************************************************/
//
//
//  Bezeichnung des Objektes. Freie Wahl, maximal 15 Buchstaben.
$Objekt = Utils::getEnvAsString("SA_OBJECT","");
//
//
/******************************************************************************
//  InfluxDB     InfluxDB     InfluxDB     InfluxDB     InfluxDB     InfluxDB
//  ***************************************************************************
//  Die Daten können jede Minute oder öfter an eine InfluxDB Datenbank
//  übertragen werden. Die Datenbank muss nur über das Netzwerk erreichbar
//  sein. Sie kann sich im lokalen Netz, im Intenet oder aber auch auf diesem
//  Raspberry befinden. Bitte lesen Sie auch das Dokument
//  "Solaranzeige + InfluxDB" welches Sie auf unserem Support Server finden.
******************************************************************************/
//  Sollen die Daten in die lokale Influx Datenbank geschrieben werden?
//  Für die lokale Datenbank sind keine weiteren Angaben nötig.
//  true oder false
$InfluxDB_local = Utils::getEnvAsBoolean("SA_INFLUX_LOCAL_ENABLED",false);
//
//  Name der lokalen Datenbank. Bitte nicht ändern, sonst funktionieren die
//  Standard Dashboards nicht!
//  ---  Nur bei Multi-Regler-Version  Nur bei Multi-Regler-Version  ----
//  Bei einer Muti-Regler-Version müssen hier unterschiedliche lokale
//  Datenbanknamen eingetragen werden. Mit gleichem Namen müssen die Datenbanken
//  in der InfluxDB angelegt werden. Siehe Dokument:
//  "Multi-Regler-Version Installation"
$InfluxDBLokal  = Utils::getEnvAsString("SA_INFLUX_LOCAL","solaranzeige");
//
//  Wie oft pro Minute sollen die Daten ausgelesen und zur InfluxDB
//  übertragen werden?
//  Gültige Werte sind 1 bis 6 (6 = alle 10 Sekunden)
//  Bei einer zusätzlichen entfernten Datenbank kann das zu erheblichen
//  Traffic führen! Dieses gilt nur für die Single-Geräte-Version!
//  Wie es bei der Multi-Regler-Version funktioniert bitte in dem
//  entsprechenden Dokument nachlesen.
//  Default ist 1 (Ein mal pro Minute)
$Wiederholungen = Utils::getEnvAsInteger("SA_INFLUX_FREQUENCE",1);
//
/****************************************************************************/
//  ENTFERNTE INFLUX DATENBANK:
//  ---------------------------
//  Ist eine entfernte InfluxDB vorhanden und sollen dorthin auch die Daten
//  übertragen werden?
//  true oder false
$InfluxDB_remote = Utils::getEnvAsBoolean("SA_INFLUX_REMOTE_ENABLED",true);
//
//  Port an den die Daten geschickt werden. Normal ist Port 8086
$InfluxPort = Utils::getEnvAsInteger("SA_INFLUX_PORT",8086);
//
//  Name der entfernten Datenbank eintragen
//  Beispiel:  "solaranzeige" oder "MeineDatenbank"
$InfluxDBName  = Utils::getEnvAsString("SA_INFLUX_DATABASE","solaranzeige");
//
//  Adresse der Datenbank
//  Entweder die IP Adresse "xxx.xxx.xxx.xxx" oder den Hostnamen oder "localhost"
//  eintragen.
//  Beispiel:  "db.solaranzeige.de" oder "34.101.3.20"
$InfluxAdresse = Utils::getEnvAsString("SA_INFLUX_HOST","");
//
//  Wenn man mit UserID und Kennwort die Daten übertragen möchte, sollte man
//  auf jeden Fall auch die SSL Verschlüsselung einschalten. Dazu muss die
//  Influx Datenbank aber erst auf https eingerichtet werden.
$InfluxSSL = Utils::getEnvAsBoolean("SA_INFLUX_SSL",false);
//
//  Wenn die entfernte Datenbank mit UserID und Kennwort geschützt ist.
//  Wenn nicht, bitte leer lassen.
$InfluxUser = Utils::getEnvAsString("SA_INFLUX_USERNAME","");
$InfluxPassword = Utils::getEnvAsString("SA_INFLUX_PASSWORD","");
//
//  Sollen die Daten nur bei Tageslicht an eine remote Datenbank gesendet werden?
//  Das reduziert den Traffic bei teuren Leitungen. Das betrifft nur die Remote
//  Datenbank falls konfiguriert.
//  true / false     ( false = die Daten werden rund um die Uhr gesendet. )
$InfluxDaylight = Utils::getEnvAsBoolean("SA_INFLUX_ONLY_WITH_LIGHT",false);
//
//
//
/*******************************************************************************
//  HOMEMATIC  ANBINDUNG      HOMEMATIC  ANBINDUNG      HOMEMATIC  ANBINDUNG
//  Teil 1    Teil 1    Teil 1    Teil 1    Teil 1    Teil 1    Teil 1    Teil 1  
//  ****************************************************************************
//  Anbindung an eine vorhandene HomeMatic Zentrale
//  Für die genaue Einrichtung bitte das PDF Dokument "Homematic_Anschluss.pdf" lesen.
//  Es befindet sich auf unserem Support Server im Bereich "Verschiedene PDF Dokumente"
//  Kapitel 1 bis 7                Kapitel 1 bis 7              Kapitel 1 bis 7
********************************************************************************/
//  Sollen die Daten an eine vorhandene Homematic Zentrale gesendet werden?
//  Diese Werte kann dann die Zentrale dann verarbeiten.
//  Ein Beispiel: Folgende Werte werden übertragen:
//  * Ladestatus 0 = Keine Ladung, 2 = Fehler, 3 = Ladung (bulk); 4 = Nachladung (absorbtion),
//               5 = Erhaltungsladung (float)
//  * Ladestatus als Textzeile (Keine_Ladung, Normale_Ladung, Nachladung, Erhaltungsladung, Fehler)
//  * Batteriespannung in Volt
//  * Erzeugte Leistung am Tage in kWh
//  * Aktuell erzeugte Solar-Leistung
//  * Batteriestatus in % (Wie voll ist die Batterie?) Nicht bei allen Geräten!
//
//  true / false
$Homematic = Utils::getEnvAsBoolean("SA_HM_ENABLED",false);
//
//  Welche IP Adresse hat Ihre Homematic Zentrale? Sie muss sich im selben
//  Netzwerk wie der Raspberry Pi befinden. Beispiel: 192.168.33.200
$Homematic_IP = Utils::getEnvAsString("SA_HM_IP","xxx.xxx.xxx.xxx");
//
//  Hier die Variablen eintragen, die zur HomeMatic Zentrale übermittelt werden
//  sollen. Siehe Dokument "HomeMatic_Anbindung.pdf"
//  Beispiel: "BatterieLadestatus,BatteriestatusText,Batteriespannung,Solarleistung,SolarleistungTag,Solarspannung";
$HomeMaticVar = Utils::getEnvAsString("SA_HM_VAR","");
//
//  Den Status einzelner Geräte aus der HomeMatic Zentrale auslesen und in die
//  Influx Datenbank schreiben, damit man den Status im Dashboard anzeigen kann.
//  Nähere Einzelheiten stehen im Dokument "HomeMatic Anbindung"
$HM_auslesen = Utils::getEnvAsBoolean("SA_HM_READ",false);
//
//  Für jedes Gerät, dessen Status ausgelesen werden soll, müssen 4 Variablen
//  angegeben werden.
//  $HM[0]["Variable"] =       Kann man nennen wie man will, steht dann so in der Influx Datenbank.
//  $HM[0]["Interface"] =      Steht in der HomeMatic, bitte übernehmen
//  $HM[0]["Seriennummer"] =   Steht auch in der HomeMatic
//  $HM[0]["Datenpunkt"] =     STATE, POWER, ACTUAL_TEMPERATURE usw. Siehe HomeMatic
//
//  Für jede Systemvariable müssen 2 Variablen angegeben werden:
//  $HM[0]["Variable"] =        Kann man nennen wie man will. Steht dann so in der Influx Datenbank
//  $HM[0]["Systemvariable"] =  Name der Systemvariable in der HomeMatic
//  -----------------------------------------------------------------------
//
//  Beispiele:  ( Die zwei Schrägstich bei Aktivierung bitte entfernen. )
//  $HM[0]["Variable"] = "Wasserboiler";
//  $HM[0]["Interface"] = "BidCos-RF";
//  $HM[0]["Seriennummer"] = "OEQ1150699:1";
//  $HM[0]["Datenpunkt"] = "STATE";
//  $HM[1]["Variable"] = "Heizluefter";
//  $HM[1]["Interface"] = "BidCos-RF";
//  $HM[1]["Seriennummer"] = "OEQ1399311:1";
//  $HM[1]["Datenpunkt"] = "STATE";
//  $HM[2]["Variable"] = "...";
//  $HM[2]["Interface"] = "...";
//  $HM[2]["Seriennummer"] = "...";
//  $HM[2]["Datenpunkt"] = "POWER";
//  $HM[3]["Variable"] = "Anwesenheit";
//  $HM[3]["Systemvariable"] = "Anwesenheit";
//  usw.
//
//
//
/*******************************************************************************
//  HOMEMATIC  ANBINDUNG      HOMEMATIC  ANBINDUNG      HOMEMATIC  ANBINDUNG
//  XML API    XML API        XML API    XML API        XML API    XML API
//  Teil 2    Teil 2    Teil 2    Teil 2    Teil 2    Teil 2    Teil 2    Teil 2  
//  ****************************************************************************
//  Ab Kapitel 7                AB Kapitel 7              AB Kapitel 7
//  Möchte man die variablen Daten eines an die Homematic angeschlossenen
//  Gerätes auslesen, wie z.B. ein Heizkörperventil, dann müssen hier die Daten
//  der angeschlossenen Geräte angegeben werden. Nähere Einzelheiten bitte im
//  Dokument Homematic_Anbindung.pdf ab Kapitel 7 lesen.
//
//  Beispiel:
//  ---------
//  HM_Geraet 1
//  $HM_Geraetetyp[1] = "HM-CC-RT-DN";     // Heizungsthermostat
//  $HM_Seriennummer[1] = "OEQ2419985";    // Wohnzimmer
//
//  HM_Geraet 2
//  $HM_Geraetetyp[2] = "HmIP-eTRV-B";     // Heizungsthermostat
//  $HM_Seriennummer[2] = "00201D89A8A446";// Badezimmer
//
//  HM_Geraet 3
//  $HM_Geraetetyp[3] = "HmIP-STHD";       // Wandthermostat
//  $HM_Seriennummer[3] = "000E9BE9967967";// Badezimmer
//
//  HM_Geraet 4
//  $HM_Geraetetyp[4] = "HM-CC-RT-DN";     // Heizungsthermostat
//  $HM_Seriennummer[4] = "OEQ2421488";    // Küche
//
//  HM_Systemvariable 1
//  $HM_Systemvariable[1] = "Test";        // Variable 1
//  $HM_Systemvariable[2] = "DutyCycle";   // Variable 2

*******************************************************************************/
//
//  HM_Geraet 1
$HM_Geraetetyp[1] = Utils::getEnvAsString("SA_HM_DEVICETPE","");      // Typenbezeichnung
$HM_Seriennummer[1] = Utils::getEnvAsString("SA_HM_SERIALNUMBER","");    // Seriennummer
//
//
/******************************************************************************
//  MQTT Protokoll     MQTT Protokoll      MQTT Protokoll      MQTT Protokoll
//  Senden und / oder Empfangen
******************************************************************************/
//  Sollen alle ausgelesenen Daten mit dem MQTT Protokoll an einen
//  MQTT-Broker gesendet werden oder MQTT Daten empfangen werden? 
//  Bitte das Solaranzeige-MQTT PDF Dokument lesen
$MQTT = Utils::getEnvAsBoolean("SA_MQTT",false);
//
//
/******************************************************************************/
//  MQTT Daten senden     MQTT Daten senden     MQTT Daten senden     MQTT Daten
//  Wenn Daten mit dem MQTT Protokoll versendet werden sollen. 
//
//  Wo ist der MQTT-Broker zu finden?
//  Entweder "localhost", eine Domain oder IP Adresse "xxx.xxx.xxx.xxx" eintragen.
//  broker.hivemq.com ist ein Test Broker   Siehe http://www.mqtt-dashboard.com/
$MQTTBroker = Utils::getEnvAsString("SA_MQTT_BROKER","localhost");
//
//  Benutzter Port des Brokers. Normal ist 1883  mit SSL 8883
$MQTTPort = Utils::getEnvAsInteger("SA_MQTT_PORT",1883);
//
//  Falls der Broker gesichert ist. Sonst bitte leer lassen.
$MQTTBenutzer = Utils::getEnvAsString("SA_MQTT_USERNAME","");
$MQTTKennwort = Utils::getEnvAsString("SA_MQTT_PASSWORD","");
//
//  Wenn man die Daten mit SSL Verschlüsselung versenden möchte.
//  Wenn hier true steht, muss im Verzeichnis "/var/www/html/" die "cerfile"
//  'ca.crt' vorhanden sein. Nähere Einzelheiten über diese Datei findet
//  man im Internet in der Mosquitto Dokumentation.
$MQTTSSL = Utils::getEnvAsBoolean("SA_MQTT_SSL",false);
//
//  Timeout der Übertragung zum Broker. Normal = 10 bis 60 Sekunden
$MQTTKeepAlive = Utils::getEnvAsInteger("SA_MQTT_KEEP_ALIVE",60);
//
//  Topic Name oder Nummer des Gerätes solaranzeige/1
//  oder solaranzeige/box1                     (solaranzeige ist fest vorgegeben.)
//  Man kann das Gerät nennen wie man will, nur jedes Gerät, welches Daten
//  senden soll unterschiedlich. Entwerder 1 bis 6 oder Namen Ihrer Wahl vergeben.
$MQTTGeraet = Utils::getEnvAsString("SA_MQTT_GERAET","box1");
//
//  Welche Daten sollen als MQTT Message übertragen werden? Wenn hier nichts
//  aufgeführt ist, werden alle ausgelesenen Daten übertragen.
//  Bitte darauf achten, dass keine Leerstellen zwischen den Variablen sind.
//  Die einzelnen Variablen müssen mit einem Komma getrennt und klein geschrieben
//  werden. Zusätzlich müssen sie den Eintrag vom $MQTTGeraet und ein Schrägstrich
//  enthalten. Das ist nötig, da mehrere Geräte an dem Raspberry hängen können.
//  Beispiel mit obigen MQTTGeraet:
//  $MQTTAuswahl = "1/ladestatus,1/solarspannung,1/solarstrom"
//  Werden hier Variablen eingetragen, dann werden auch nur diese Topics
//  übertragen.
$MQTTAuswahl = Utils::getEnvAsString("SA_MQTT_AUSWAHL","");
//
//
/******************************************************************************
//  MQTT Empfang       MQTT Empfang       MQTT Empfang       MQTT Empfang
//  Subscribing    Subscribing    Subscribing    Subscribing    Subscribing
******************************************************************************/
//  Welche Daten sollen empfangen werden. Hier können die Topics, die
//  empfangen werden sollen aufgeführt werden. Dabei gibt es 2 Möglichkeiten
//  Entweder ein einzelner Wert oder eine Reihe von Werten.
//  Wichtig! Das basis Topics ist immer solaranzeige. Dann muss entweder befehl
//  oder anzeige kommen, dann die Gerätenummer und dann die Bezeichnung des
//  Wertes. Die Gerätenummer ist immer 1, außer bei Multi-Regler-Versionen.
//  Beispiel:  solaranzeige/anzeige/1/PV-Spannung
//  In diesem Beispiel wird der Wert der PV-Spannung in die Influx Datenbank
//  geschrieben unter dem Measurement MQTT
//  oder
//  Beispiel:  solaranzeige/befehl/1/POP  mit Wert 00
//  Der Befehl POP00 wird zum Wechselrichter geschickt. Er wird jedoch nur
//  ausgeführt wenn es sich um einen erlaubten Befehl handelt, der in der
//  Datei "befehle.ini.php" enthalten ist.
//
//  Beispiele:
//  $MQTTTopic[1] = "solaranzeige/befehl/1/POP";
//  $MQTTTopic[2] = "solaranzeige/befehl/1/PCP";
//  $MQTTTopic[3] = "solaranzeige/anzeige/1/Wasserboiler";
//
//  Oder auch
//  $MQTTTopic[1] = "solaranzeige/befehl/1/#";
//  Es können so viele Topics wie benötigt aufgeführt werden. Sie müssen nur
//  durch nummeriert werden [1] bis [n]
//  Bei Multi-Regler-Versionen muss zusätzlich noch die Gerätenummer angegeben
//  werden. Weitere Informationen finden Sie auf dem Support Forum.
//  Achtung! Damit der Empfang auch funktioniert muss $MQTT = true;
//  etwas weiter oben stehen!
$MQTTTopic[1] = Utils::getEnvAsString("SA_MQTT_TOPIC","solaranzeige/befehl/1/#");
//
//
/******************************************************************************
//  SONOFF Geräte mit Tasmota Firmware       SONOFF Geräte mit Tasmota Firmware
//  POW R2 / TH10 R2 oder TH16 R2  oder GOSUND SP1xx
******************************************************************************/
//  Bitte den Topic-Namen, der in der TASMOTA Firmware angegeben ist, hier
//  eintragen. Unbedingt auf Groß- und Keinschreibung achten! Der Name kann
//  frei gewählt werden, er muss nur im Gerät und hier gleich sein. Werden
//  mehrere Sonoff Geräte mit der Solaranzeige betrieben, muss jedes einzelne
//  Gerät einen anderen Topic-Namen benutzen!
$Topic = Utils::getEnvAsString("SA_TOPIC","sonoff");
//
//
/******************************************************************************
//  WETTERDATEN     WETTERDATEN    WETTERDATEN    WETTERDATEN    WETTERDATEN
******************************************************************************/
//  Die Wetterdaten werden vom Server openweathermap.org geholt, da von dort
//  die Informationen kostenlos sind.
//  Man muss sich jedoch auf dem Server anmelden, um eine APP ID zu bekommen.
//
//  Bei einer Multi-Regler-Version nur in der 1.user.config.php aktivieren!
//  Sollen die aktuellen Wetterdaten geholt und abgespeichert werden?
//  Dadurch wird mehr Traffic generiert. Die Daten stehen dann in der Influx
//  Datenbank "aktuellesWetter" unter dem Measurement "Wetter" zur Verfügung.
//  Sie werden alle 30 Minuten aktualisiert
//  true oder false
$Wetterdaten = Utils::getEnvAsBoolean("SA_WEATHER",false);
//
//  Die Application ID bekommt man, wenn man sich auf dem Server
//  www.openweathermap.org registriert. Sie hat 32 Stellen und muss hier
//  eingetragen werden. Beispiel: "57b78415a343540e3a4e4f72751c90f9"
$APPID = Utils::getEnvAsString("SA_WEATHER_APPID","");
//
//  Der Standort wird mit einer StandortID angegeben. Wie die StandortID
//  ermittelt wird, bitte im Support Forum nachlesen. Man kann eine Liste
//  aller Standort ID's Weltweit hier herunterladen:
//  http://bulk.openweathermap.org/sample/city.list.json.gz
//  Default = "2925533" Frankfurt am Main oder die ID Ihres Standortes.
$StandortID = Utils::getEnvAsString("SA_WEATHER_LOCATION_ID","2925533");
//
//
/******************************************************************************
//  PROGNOSEDATEN     PROGNOSEDATEN    PROGNOSEDATEN    PROGNOSEDATEN
******************************************************************************/
//  Die Wetterprognosedaten werden vom Server www.solarprognose.de geholt.
//  Teilweise sind die Daten dort kostenlos. [ www.solarprognose.de ]
//  Man muss sich jedoch auf dem Server anmelden, um eine Prognose ID zu bekommen.
//
//  Sollen die aktuellen Prognosedaten geholt und abgespeichert werden?
//  Die Daten stehen dann in der Influx Datenbank "solaranzeige" unter dem
//  Measurement "Wetterprognose" zur Verfügung. Sie werden pro Stunde einmal
//  aktualisiert.
//  Möchte man seinen eigenen Prognose Script nutzen, dann bitte hier User eingeben.
//  In diesem Fall wird alle 30 Minuten der Script "prognose.php" aufgerufen.
//  Dort müssen die Funktionen hinterlegt sein.
//  keine, API, User, beide
$Prognosedaten = Utils::getEnvAsString("SA_PROGNOSE","keine");              //  "keine" , "API" , "User" , "beide"
//
//  Wenn API eingetragen wird, dann folgende 3 Variablen füllen:
$AccessToken = Utils::getEnvAsString("SA_PROGNOSE_ACCESSTOKEN","");                     // Bekommt man bei www.solarprognose.de
$PrognoseItem = Utils::getEnvAsString("SA_PROGNOSE_ITEM","inverter");            // plant, inverter
$PrognoseID = Utils::getEnvAsString("SA_PROGNOSE_ID","0");                     // Anlagen ID oder Wechselrichter ID
$Algorithmus = Utils::getEnvAsString("SA_ALGORITHMUS","");                     // kann leer bleiben oder
//                                     // mosmix | own-v1 | clearsky
//
/******************************************************************************
//  MESSENGER   MELDUNGEN        MESSENGER   MELDUNGEN        MESSENGER
******************************************************************************/
//  Es können Fehlermeldungen, Ereignisse oder Statistiken mit einem
//  Messenger übertragen werden. Dazu bitte Messenger = true eintragen
//  Genaue Informationen stehen im Dokument "Messenger_Nachrichten.pdf"
//
//  true / false
$Messenger = Utils::getEnvAsBoolean("SA_MESSENGER",false);
//
//  Welcher Messengerdienst soll benutzt werden?
//  Pushover / Signal / WhatsApp
$Messengerdienst[1] = Utils::getEnvAsString("SA_MESSENGERDIENST","Pushover");   //  Pushover, Signal oder WhatsApp
//  Die Solaranzeige müssen Sie bei Pushover / Signal oder WhatsApp
//  registrieren und einen API Token holen. 
//  Wie das geht, steht in dem Dokument "Messenger_Nachrichten" auf dem
//  Support Server
//  Pushover Beispiel $API_Token = "amk4be851bcegnirhu1b71u6ou7uoh";
//  Signal Beispiel $API_Token = "999999";
$API_Token[1] = Utils::getEnvAsString("SA_API_TOKEN"," ");
//
//  Der User_Key ist die Messeger Empfänger Adresse. Bei Pushover können bis zu
//  9 Empfänger angegeben werden. $User_Key[1]  bis  $User_Key[9]
//  Am Ende jeder Zeile das Semikolon nicht vergessen!
//  Pushover Beispiel: $User_Key[1] = "ub6c3wmw4a3idwk9b5ajgfs5a7aypt";
//  Siehe Dokument "Nachrichten_senden.pdf"
//  Bei WhatsApp und Signal kann nur ein Empfänger angegeben werden, da der Token 
//  zur Rufnummer passen muss.
//  Signal Beispiel: $User_Key[1] = "+491769000000";
$User_Key[1] = Utils::getEnvAsString("SA_USERKEY_1","");
//
//  ------------------------------------------------------------------------
//  Und jetzt eventuell für weitere Personen:
//
//  $Messengerdienst[2] = "";     // Pushover, WhatsApp oder Signal
//  $API_Token[2] = "";
//  $User_Key[2] = "";
//
//  $Messengerdienst[3] = "";     / Pushover, WhatsApp oder Signal
//  $API_Token[3] = "";
//  $User_Key[3] = "";
//
//*****************************************************************************
//  Sonnen Auf und Untergang:
//  Standort für Frankfurt. Wer es etwas genauer haben möchte, hier den eigenen
//  Standort eintragen. Bitte als Dezimalzahl wie hier vorgegeben!
$Breitengrad = Utils::getEnvAsFloat("SA_BREITENGRAD",50.1143999);
$Laengengrad = Utils::getEnvAsFloat("SA_LAENGENGRAD",8.6585178);
//
//
/******************************************************************************
//  aWATTar Börsenpreise      aWATTar Börsenpreise      aWATTar Börsenpreise.
//
//  Sollen die aktuellen Strom Börsenpreise in die oben angegebene locale.
//  Datenbank in das Measurement "awattarPreise" geschrieben werden?
******************************************************************************/
//
$aWATTar = Utils::getEnvAsBoolean("SA_AWATTAR",false);
//
$Aufschlag = Utils::getEnvAsString("SA_AUFSCHLAG","0");       // Z.B.  "20,6"        Preis des Aufschlages in Cent
//
$aWATTarLand = Utils::getEnvAsString("SA_AWATTAR_LAND","DE");     // "DE" = Deutschland   "AT" = Österreich 
/******************************************************************************
//  ACHTUNG!   ACHTUNG!   ACHTUNG!   ACHTUNG!   ACHTUNG!   ACHTUNG!   ACHTUNG!
//
//  Alles ab hier nicht ändern! Nur auf Anweisung. Änderungen hier können
//  das System zum Absturz bringen.
/******************************************************************************
//  USB Device      USB Device      USB Device      USB Device      USB Device
******************************************************************************/
//
//  USB Device, die automatisch erkannt wurde...  bitte nicht ändern
//  Wird nicht bei der Multi-Regler-Version benötigt.
//
$USBRegler = Utils::getEnvAsString("SA_USB_REGLER","/dev/ttyUSB0");
//
//  Nur wenn die automatischer Erkennung nicht funktioniert hat, bitte manuell
//  eintragen. Im Normalfall wird das nicht benötigt. So lassen wie es ist.
//  ---  Nur bei Multi-Regler-Version  Nur bei Multi-Regler-Version  ----
//  Bei einer Multi-Regler-Version muss hier der Devicename manuell
//  eingetragen werden.
//
$USBDevice = Utils::getEnvAsString("SA_USB_DEVICE","");
//
//  Wird nur in seltenen Fällen gebraucht.
//  $SerielleGeschwindigkeit = "9600";
/*****************************************************************************/
//
/******************************************************************************
//  Raspberry Pi   Hardware   Raspberry Pi   Hardware   Raspberry Pi   Hardware
******************************************************************************/
// Bitte nicht ändern, wird automatisch ermittelt.
//
$Platine = Utils::getEnvAsString("SA_PLATINE","Raspberry unbekannt");
//
/******************************************************************************
//  PHP Error Reporting        PHP Error Reporting        PHP Error Reporting
//  Bei ungeklärten Problemen hier einschalten. Normal = ausgeschaltet
******************************************************************************/
// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_STRICT);
//
/******************************************************************************
//  Hier können zusätzliche Parameter, je nach Bedarf und Beschreibung 
//  eingetragen werden.
******************************************************************************/
//  Ist für die neue Datenbankstruktur des Alpha ESS Wechselrichters
//  Mit 0 kann die alte Struktur eingeschaltet werden.
$Alpha_ESS = Utils::getEnvAsInteger("SA_ALPHA_ESS",0);
//
// ENDE ENDE ENDE ENDE ENDE ENDE ENDE ENDE ENDE ENDE ENDE ENDE ENDE ENDE ENDE

?>
