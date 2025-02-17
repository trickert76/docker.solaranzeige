<?php

/*****************************************************************************
//  Solaranzeige Projekt             Copyright (C) [2015-2016]  [Ulrich Kunz]
//
//  Dieses Programm ist freie Software. Sie können es unter den Bedingungen
//  der GNU General Public License, wie von der Free Software Foundation
//  veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
//  Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.
//
//  Die Veröffentlichung dieses Programms erfolgt in der Hoffnung, daß es
//  Ihnen von Nutzen sein wird, aber OHNE IRGENDEINE GARANTIE, sogar ohne
//  die implizite Garantie der MARKTREIFE oder der VERWENDBARKEIT FÜR EINEN
//  BESTIMMTEN ZWECK. Details finden Sie in der GNU General Public License.
//
//  Ein original Exemplar der GNU General Public License finden Sie hier:
//  http://www.gnu.org/licenses/
//
//  Dies ist ein Programmteil des Programms "Solaranzeige"
//
//  Es dient dem Auslesen des SolarMax S Wechselrichter über die LAN Schnittstelle
//  Eine Protokollbeschreibung gibt es nicht dafür.
//  Das Auslesen wird hier mit einer Schleife durchgeführt. Wie oft die Daten
//  ausgelesen und gespeichert werden steht in der user.config.php
//
//
*****************************************************************************/
// Im Fall, dass man die Device manuell eingeben muss
if (isset($USBDevice) and !empty($USBDevice)) {
  $USBRegler = $USBDevice;
}

$Tracelevel = 7;  //  1 bis 10  10 = Debug
$RemoteDaten = true;
$Device = "WR"; // WR = Wechselrichter
$Version = "";
$Start = time();  // Timestamp festhalten
Log::write("-------------   Start  solarmax_s_serie.php    --------------- ","|--",6);

Log::write("Zentraler Timestamp: ".$zentralerTimestamp,"   ",8);
$aktuelleDaten = array();
$aktuelleDaten["zentralerTimestamp"] = $zentralerTimestamp;

setlocale(LC_TIME,"de_DE.utf8");


//  Hardware Version ermitteln.
$Teile =  explode(" ",$Platine);
if ($Teile[1] == "Pi") {
  $Version = trim($Teile[2]);
  if ($Teile[3] == "Model") {
    $Version .= trim($Teile[4]);
    if ($Teile[5] == "Plus") {
      $Version .= trim($Teile[5]);
    }
  }
}
Log::write("Hardware Version: ".$Version,"o  ",8);

switch($Version) {
  case "2B":
  break;
  case "3B":
  break;
  case "3BPlus":
  break;
  case "4B":
  break;
  default:
  break;
}

if(Utils::tageslicht() or $InfluxDaylight === false)  {
  //  Der Wechselrichter wird nur am Tage abgefragt.
  $COM1 = fsockopen($WR_IP,$WR_Port, $errno, $errstr, 15);  // 15 Sekunden Timeout
  if (!is_resource($COM1)) {
    Log::write("Kein Kontakt zum Wechselrichter ".$WR_IP.",  Port: ".$WR_Port.",  Fehlermeldung: ".$errstr,"XX ",3);
    Log::write("Exit.... ","XX ",8);
    goto Ausgang2;
  }
}
else {
  Log::write("Es ist dunkel... ","X  ",7);
  goto Ausgang;
}

$i = 1;
do {
  Log::write("Die Daten werden ausgelesen...","+  ",9);

  /****************************************************************************
  //  Ab hier wird der Wechselrichter ausgelesen.
  //
  //  Auf jeden Fall die Geräteadresse kontrollieren.
  //  user.config.php  [ $WR_Adresse = "??"; ]
  //
  //
  //  Ergebniswerte:
  //  $aktuelleDaten["Firmware"]                Nummer
  //  $aktuelleDaten["Produkt"]                 Text
  //  $aktuelleDaten["Objekt"]                  Text
  //  $aktuelleDaten["Solarstrom"]
  //  $aktuelleDaten["Solarspannung"]
  //  $aktuelleDaten["AC_Strom"]
  //  $aktuelleDaten["AC_Spannung"]
  //  $aktuelleDaten["AC_Wirkleistung"]
  //  $aktuelleDaten["Ausgangslast"]
  //  $aktuelleDaten["WattstundenGesamt"]
  //  $aktuelleDaten["WattstundenGesamtHeute"]
  //  $aktuelleDaten["WattstundenGesamtGestern"]
  //  $aktuelleDaten["WattstundenGesamtMonat"]
  //  $aktuelleDaten["WattstundenGesamtJahr"]
  //  $aktuelleDaten["Temperatur"]
  //  $aktuelleDaten["Optionen"]
  //  $aktuelleDaten["ErrorCodes"]
  //
  //
  //
  //  status_codes = 
  //        20000: 'No Communication',
  //        20001: 'In Use',
  //        20002: 'Too little radiation',
  //        20003: 'Approach', #? translation of Anfahren
  //        20004: 'Operating at MPP',
  //        20005: 'Fan Runs', #Ventilator läuft
  //        20006: 'Operating at maximum power',
  //        20007: 'Temperature Limit',
  //        20008: 'Mains Operation',
  //
  //  alarm_codes = 
  //            0: 'No Error',
  //            1: 'External Fault 1',
  //            2: 'Insulation fault DC side',
  //            4: 'Earth fault current too large',
  //            8: 'Fuse failure midpoint Earth',
  //           16: 'External alarm 2',
  //           32: 'Long-term temperature limit',
  //           64: 'Error AC supply ',
  //          128: 'External alarm 4',
  //          256: 'Fan failure',
  //          512: 'Fuse failure ',
  //         1024: 'Failure temperature sensor',
  //         2048: 'Alarm 12',
  //         4096: 'Alarm 13',
  //         8192: 'Alarm 14',
  //        16384: 'Alarm 15',
  //        32768: 'Alarm 16',
  //        65536: 'Alarm 17',
  //   
  ****************************************************************************/


  $aktuelleDaten["Produkt"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "TYP"));
  $aktuelleDaten["Firmware"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "SWV"));
  $aktuelleDaten["Betriebsstunden"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "KHR"));
  $aktuelleDaten["WattstundenGesamtHeute"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "KDY"))*100;
  $aktuelleDaten["WattstundenGesamtGestern"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "KLD"))*100;
  $aktuelleDaten["WattstundenGesamtMonat"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "KMT"))*1000;
  $aktuelleDaten["WattstundenGesamtJahr"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "KYR"))*1000;
  $aktuelleDaten["WattstundenGesamt"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "KTO"))*1000;
  $aktuelleDaten["Solarspannung"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "UDC"))/10;
  $aktuelleDaten["Solarstrom"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "IDC"))/100;
  $aktuelleDaten["AC_Ausgangsstrom"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "IL1"))/100;
  $aktuelleDaten["AC_Ausgangsspannung"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "UL1"))/10;
  $aktuelleDaten["AC_Leistung"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "PAC"))/2;
  $aktuelleDaten["Ausgangslast"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "PRL"));
  $aktuelleDaten["Temperatur"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "TKK"));
  $aktuelleDaten["ErrorCodes"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "E11"));
  $aktuelleDaten["AC_Frequenz"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "TNF"))/100;
  $aktuelleDaten["Wp_Install"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "PIN"))/2;

  // MT Serie

  $aktuelleDaten["Solarspannung_String_1"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "UD01"))/10;
  $aktuelleDaten["Solarspannung_String_2"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "UD02"))/10;
  $aktuelleDaten["Solarspannung_String_3"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "UD03"))/10;
  $aktuelleDaten["Solarstrom_String_1"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "ID01"))/100;
  $aktuelleDaten["Solarstrom_String_2"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "ID02"))/100;
  $aktuelleDaten["Solarstrom_String_3"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "ID03"))/100;
  $aktuelleDaten["Solarleistung_String_1"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "PD01"))/2;
  $aktuelleDaten["Solarleistung_String_2"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "PD02"))/2;
  $aktuelleDaten["Solarleistung_String_3"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "PD03"))/2;

  $aktuelleDaten["AC_Ausgangsspannung_R"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "UL1"))/10;
  $aktuelleDaten["AC_Ausgangsspannung_S"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "UL2"))/10;
  $aktuelleDaten["AC_Ausgangsspannung_T"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "UL3"))/10;
  $aktuelleDaten["AC_Ausgangsstrom_R"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "IL1"))/100;
  $aktuelleDaten["AC_Ausgangsstrom_S"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "IL2"))/100;
  $aktuelleDaten["AC_Ausgangsstrom_T"] = hexdec(SolarMax::com_lesen($COM1,$WR_Adresse, "IL3"))/100;

  $aktuelleDaten["SYS"] = SolarMax::com_lesen($COM1,$WR_Adresse, "SYS");


  $Teile = explode(",",$aktuelleDaten["SYS"]);
  // Dummy für die HomeMatic
  $aktuelleDaten["Geraetestatus"] = hexdec($Teile[0]);
  $aktuelleDaten["Solarleistung"] =  ($aktuelleDaten["Solarleistung_String_1"] + $aktuelleDaten["Solarleistung_String_2"] + $aktuelleDaten["Solarleistung_String_3"]);
  if ($aktuelleDaten["Solarleistung"] == 0) {
    $aktuelleDaten["Solarleistung"] = ($aktuelleDaten["Solarspannung"] * $aktuelleDaten["Solarstrom"]);
  }



  /****************************************************************************
  //  ENDE REGLER AUSLESEN      ENDE REGLER AUSLESEN      ENDE REGLER AUSLESEN
  ****************************************************************************/



  /**************************************************************************
  //  Falls ein ErrorCode vorliegt, wird er hier in einen lesbaren
  //  Text umgewandelt, sodass er als Fehlermeldung gesendet werden kann.
  //  Die Funktion ist noch nicht überall implementiert.
  **************************************************************************/
  $FehlermeldungText = "";


  /****************************************************************************
  //  Die Daten werden für die Speicherung vorbereitet.
  ****************************************************************************/
  $aktuelleDaten["Regler"] = $Regler;
  $aktuelleDaten["Objekt"] = $Objekt;
  $aktuelleDaten["zentralerTimestamp"] = ($aktuelleDaten["zentralerTimestamp"]+10);


  if ($i == 1) 
    Log::write(var_export($aktuelleDaten,1),"   ",8);


  /****************************************************************************
  //  User PHP Script, falls gewünscht oder nötig
  ****************************************************************************/
  if ( file_exists($basedir."/custom/solarmax_s_serie_math.php")) {
    include $basedir.'/custom/solarmax_s_serie_math.php';  // Falls etwas neu berechnet werden muss.
  }


  /**************************************************************************
  //  Alle ausgelesenen Daten werden hier bei Bedarf als mqtt Messages
  //  an den mqtt-Broker Mosquitto gesendet.
  //  Achtung! Die Übertragung dauert ca. 30 Sekunden!
  **************************************************************************/
  if ($MQTT) {
    Log::write("MQTT Daten zum [ $MQTTBroker ] senden.","   ",1);
    require($basedir."/services/mqtt_senden.php");
  }

  /****************************************************************************
  //  Zeit und Datum
  ****************************************************************************/
  //  Der Regler hat keine interne Uhr! Deshalb werden die Daten vom Raspberry benutzt.
  $aktuelleDaten["Timestamp"] = time();
  $aktuelleDaten["Monat"]     = date("n");
  $aktuelleDaten["Woche"]     = date("W");
  $aktuelleDaten["Wochentag"] = strftime("%A",time());
  $aktuelleDaten["Datum"]     = date("d.m.Y");
  $aktuelleDaten["Uhrzeit"]   = date("H:i:s");


  /****************************************************************************
  //  InfluxDB  Zugangsdaten ...stehen in der user.config.php
  //  falls nicht, sind das hier die default Werte.
  ****************************************************************************/
  $aktuelleDaten["InfluxAdresse"] = $InfluxAdresse;
  $aktuelleDaten["InfluxPort"] = $InfluxPort;
  $aktuelleDaten["InfluxUser"] =  $InfluxUser;
  $aktuelleDaten["InfluxPassword"] = $InfluxPassword;
  $aktuelleDaten["InfluxDBName"] = $InfluxDBName;
  $aktuelleDaten["InfluxDaylight"] = $InfluxDaylight;
  $aktuelleDaten["InfluxDBLokal"] = $InfluxDBLokal;
  $aktuelleDaten["InfluxSSL"] = $InfluxSSL;
  $aktuelleDaten["Demodaten"] = false;





  Log::write(print_r($aktuelleDaten,1),"*- ",9);



  /*********************************************************************
  //  Daten werden in die Influx Datenbank gespeichert.
  //  Lokal und Remote bei Bedarf.
  *********************************************************************/
  if ($InfluxDB_remote) {
    // Test ob die Remote Verbindung zur Verfügung steht.
    if ($RemoteDaten) {
      $rc = InfluxDB::influx_remote_test();
      if ($rc) {
        $rc = InfluxDB::influx_remote($aktuelleDaten);
        if ($rc) {
          $RemoteDaten = false;
        }
      }
      else {
        $RemoteDaten = false;
      }
    }
    if ($InfluxDB_local) {
      $rc = InfluxDB::influx_local($aktuelleDaten);
    }
  }
  else {
    $rc = InfluxDB::influx_local($aktuelleDaten);
  }


  if (is_file($basedir."/config/1.user.config.php")) {
    // Ausgang Multi-Regler-Version
    $Zeitspanne = (7 - (time() - $Start));
    Log::write("Multi-Regler-Ausgang. ".$Zeitspanne,"   ",2);
    if ($Zeitspanne > 0) {
      sleep($Zeitspanne);
    }
    break;
  }
  else {
    Log::write("Schleife: ".($i)." Zeitspanne: ".(floor((56 - (time() - $Start))/($Wiederholungen-$i+1))),"   ",9);
    sleep(floor((56 - (time() - $Start))/($Wiederholungen-$i+1)));
  }
  if ($Wiederholungen <= $i or $i >= 6) {
    Log::write("OK. Daten gelesen.","   ",9);
    Log::write("Schleife ".$i." Ausgang...","   ",8);
    break;
  }

  $i++;
} while (($Start + 54) > time());


if (isset($aktuelleDaten["Firmware"]) and isset($aktuelleDaten["Regler"])) {


  /*********************************************************************
  //  Jede Minute werden bei Bedarf einige Werte zur Homematic Zentrale
  //  übertragen.
  *********************************************************************/
  if (isset($Homematic) and $Homematic == true) {
    Log::write("Daten werden zur HomeMatic übertragen...","   ",8);
    require($basedir."/services/homematic.php");
  }

  /*********************************************************************
  //  Sollen Nachrichten an einen Messenger gesendet werden?
  //  Bei einer Multi-Regler-Version sollte diese Funktion nur bei einem
  //  Gerät aktiviert sein.
  *********************************************************************/
  if (isset($Messenger) and $Messenger == true) {
    Log::write("Nachrichten versenden...","   ",8);
    require($basedir."/services/meldungen_senden.php");
  }

  Log::write("OK. Datenübertragung erfolgreich.","   ",7);
}
else {
  Log::write("Keine gültigen Daten empfangen.","!! ",6);
}


Ausgang:

fclose($COM1);

Ausgang2:

Log::write("-------------   Stop   solarmax_s_serie.php    --------------- ","|--",6);

return;



?>
