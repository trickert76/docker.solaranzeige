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
//  Es dient dem Auslesen des Pylontech US 2000B BMS über die USB Schnittstelle
//  Das Auslesen wird hier mit einer Schleife durchgeführt. Wie oft die Daten
//  ausgelesen und gespeichert werden steht in der user.config.php
//
//  Achtung! Der Regler sendet zwischendurch immer wieder asynchrone Daten!
//
*****************************************************************************/
// Im Fall, dass man die Device manuell eingeben muss
if (isset($USBDevice) and !empty($USBDevice)) {
  $USBRegler = $USBDevice;
}
$Tracelevel = 7; //  1 bis 10  10 = Debug
$Device = "BMS"; // BMS = Batteriemanagementsystem
$Version = "";
$Start = time( ); // Timestamp festhalten
Log::write( "------------   Start  seplos_bms.php   ----------------- ", "|--", 6 );
Log::write( "Zentraler Timestamp: ".$zentralerTimestamp, "   ", 8 );
$aktuelleDaten = array();
$aktuelleDaten["zentralerTimestamp"] = $zentralerTimestamp;
setlocale( LC_TIME, "de_DE.utf8" );
$RemoteDaten = true;
//  Hardware Version ermitteln.
$Teile = explode( " ", $Platine );
if ($Teile[1] == "Pi") {
  $Version = trim( $Teile[2] );
  if ($Teile[3] == "Model") {
    $Version .= trim( $Teile[4] );
    if ($Teile[5] == "Plus") {
      $Version .= trim( $Teile[5] );
    }
  }
}
Log::write( "Hardware Version: ".$Version, "o  ", 8 );
switch ($Version) {

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
//  Nach em Öffnen des Port muss sofort der Regler ausgelesen werden, sonst
//  sendet er asynchrone Daten!
$USB1 = USB::openUSB( $USBRegler );
if (!is_resource( $USB1 )) {
  Log::write( "USB Port kann nicht geöffnet werden. [1]", "XX ", 7 );
  Log::write( "Exit.... ", "XX ", 7 );
  goto Ausgang;
}

/************************************************************************************
//  Sollen Befehle an das Gerät gesendet werden?
//  Achtung! Diese Funktion ist noch nicht fertig und noch nicht geprüft.
************************************************************************************/
if (file_exists( "/var/www/pipe/".$GeraeteNummer.".befehl.steuerung" )) {
  Log::write( "Steuerdatei '".$GeraeteNummer.".befehl.steuerung' vorhanden----", "|- ", 5 );
  $Inhalt = file_get_contents( "/var/www/pipe/".$GeraeteNummer.".befehl.steuerung" );
  $Befehle = explode( "\n", trim( $Inhalt ));
  Log::write( "Befehle: ".print_r( $Befehle, 1 ), "|- ", 9 );
  for ($i = 0; $i < count( $Befehle ); $i++) {
    if ($i > 10) {
      //  Es werden nur maximal 10 Befehle pro Datei verarbeitet!
      break;
    }

    /***************************************************************************
    //  In der Datei "befehle.ini.php" müssen alle gültigen Befehle aufgelistet
    //  werden, die man benutzen möchte.
    //  Achtung! Genau darauf achten, dass der Befehl richtig geschrieben wird,
    //  damit das Gerät keinen Schaden nimmt.
    //  Siehe Dokument:  Befehle_senden.pdf
    ***************************************************************************/
    if (file_exists( $basedir."/config/befehle.ini" )) {
      Log::write( "Die Befehlsliste 'befehle.ini.php' ist vorhanden----", "|- ", 9 );
      $INI_File = parse_ini_file( $basedir."/config/befehle.ini", true );
      $Regler13 = $INI_File["Regler13"];
      Log::write( "Befehlsliste: ".print_r( $Regler13, 1 ), "|- ", 10 );
      if (!in_array( strtoupper( $Befehle[$i] ), $Regler13 )) {
        Log::write( "Dieser Befehl ist nicht zugelassen. ".$Befehle[$i], "|o ", 3 );
        Log::write( "Die Verarbeitung der Befehle wird abgebrochen.", "|o ", 3 );
        break;
      }
    }
    else {
      Log::write( "Die Befehlsliste 'befehle.ini.php' ist nicht vorhanden----", "|- ", 3 );
      break;
    }
    $Wert = false;
    $Antwort = "";

    /************************************************************************
    //  Ab hier wird der Befehl gesendet.
    //  Diese Funktion ist noch nicht fertig programmiert.
    ************************************************************************/
    Log::write( "Befehl zur Ausführung: ".strtoupper( $Befehle[$i] ), "|- ", 3 );
  }
  $rc = unlink( "/var/www/pipe/".$GeraeteNummer.".befehl.steuerung" );
  if ($rc) {
    Log::write( "Datei  /pipe/".$GeraeteNummer.".befehl.steuerung  gelöscht.", "    ", 8 );
  }
}
else {
  Log::write( "Steuerdatei '".$GeraeteNummer.".befehl.steuerung' nicht vorhanden----", "|- ", 8 );
}

/*******************************************************************************
//
//  Befehle senden Ende
//
//  Hier beginnt das Auslesen der Daten
//
*******************************************************************************/
$i = 1;
do {
  Log::write( "Die Daten werden ausgelesen...", "+  ", 8 );
  $aktuelleDaten["SOC_SUM"] = 0;
  /****************************************************************************
  //  Ab hier wird der Regler ausgelesen.
  //
  //  Ergebniswerte:
  //  $aktuelleDaten["Firmware"]
  //  $aktuelleDaten["Produkt"]
  //  $aktuelleDaten["Batteriespannung"]
  //  $aktuelleDaten["Batteriestrom"]
  //  $aktuelleDaten["KilowattstundenGesamt"]
  //  $aktuelleDaten["AmperestundenGesamt"]
  //  $aktuelleDaten["Temperatur"]
  //  $aktuelleDaten["SOC"]
  //  $aktuelleDaten["TTG"]
  //  $aktuelleDaten["Leistung"]
  //
  //
  //  Die Adresse fängt bei 02 an!
  //  Die Packnummer auch.
  ****************************************************************************/
  if (isset($Batteriepacks)) {
    $aktuelleDaten["Packs"] = $Batteriepacks; // Aus der user.config.php
  }
  else {
    Log::write( "Die Variable Batteriepacks fehlt in der user.config.php", "   ", 6 );
    $aktuelleDaten["Packs"] = 1;
  }
  for ($n = 0; $n < $aktuelleDaten["Packs"]; $n++) {
    $AdrHex = strtoupper( substr( "00".dechex( $n), - 2 ));
    $AdrHex2 = strtoupper( substr( "00".dechex( $n +1 ), - 2 ));
    $Befehl = "20".$AdrHex."4642E002".$AdrHex2; // Packnummer fängt bei 02 an zu zählen.
    Log::write( "Befehl: ".$Befehl, "   ", 9 );
    $CRC = strtoupper(Utils::crc16_us2000( $Befehl ));
    $rc = US2000::us2000_auslesen( $USB1, "~".$Befehl.$CRC."\r" );
    if ($rc) {
      $Daten = US2000::us2000_daten_entschluesseln( $rc );
      $aktuelleDaten["Pack".$n]["Anz_Zellen"] = hexdec( substr( $Daten["INFO"], 4, 2 ));
      $aktuelleDaten["Pack".$n]["Zelle1"] = (hexdec( substr( $Daten["INFO"], 6, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle2"] = (hexdec( substr( $Daten["INFO"], 10, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle3"] = (hexdec( substr( $Daten["INFO"], 14, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle4"] = (hexdec( substr( $Daten["INFO"], 18, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle5"] = (hexdec( substr( $Daten["INFO"], 22, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle6"] = (hexdec( substr( $Daten["INFO"], 26, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle7"] = (hexdec( substr( $Daten["INFO"], 30, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle8"] = (hexdec( substr( $Daten["INFO"], 34, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle9"] = (hexdec( substr( $Daten["INFO"], 38, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle10"] = (hexdec( substr( $Daten["INFO"], 42, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle11"] = (hexdec( substr( $Daten["INFO"], 46, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle12"] = (hexdec( substr( $Daten["INFO"], 50, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle13"] = (hexdec( substr( $Daten["INFO"], 54, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle14"] = (hexdec( substr( $Daten["INFO"], 58, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle15"] = (hexdec( substr( $Daten["INFO"], 62, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Zelle16"] = (hexdec( substr( $Daten["INFO"], 66, 4 )) / 1000);
      $aktuelleDaten["Pack".$n]["Temp_Anz"] = substr( $Daten["INFO"], 70, 2 );
      $aktuelleDaten["Pack".$n]["Temp1"] = ((hexdec( substr( $Daten["INFO"], 72, 4 )) - 2731) / 10);
      $aktuelleDaten["Pack".$n]["Temp2"] = ((hexdec( substr( $Daten["INFO"], 76, 4 )) - 2731) / 10);
      $aktuelleDaten["Pack".$n]["Temp3"] = ((hexdec( substr( $Daten["INFO"], 80, 4 )) - 2731) / 10);
      $aktuelleDaten["Pack".$n]["Temp4"] = ((hexdec( substr( $Daten["INFO"], 84, 4 )) - 2731) / 10);
      $aktuelleDaten["Pack".$n]["Temp5"] = ((hexdec( substr( $Daten["INFO"], 88, 4 )) - 2731) / 10);
      $aktuelleDaten["Pack".$n]["Temp6"] = ((hexdec( substr( $Daten["INFO"], 92, 4 )) - 2731) / 10);
      $aktuelleDaten["Pack".$n]["Strom"] = (Utils::hexdecs( substr( $Daten["INFO"], 96, 4 )) / 100);
      $aktuelleDaten["Pack".$n]["Spannung"] = (hexdec( substr( $Daten["INFO"], 100, 4 )) / 100);
      $aktuelleDaten["Pack".$n]["Ah_left"] = (hexdec( substr( $Daten["INFO"], 104, 4 )) / 100);
      $aktuelleDaten["Pack".$n]["KDNR"] = (hexdec( substr( $Daten["INFO"], 108, 2 )));
      $aktuelleDaten["Pack".$n]["Ah_total"] = (hexdec( substr( $Daten["INFO"], 110, 4 )) / 100);
      $aktuelleDaten["Pack".$n]["SOC"] = (hexdec( substr( $Daten["INFO"], 114, 4 )) / 10);
      $aktuelleDaten["Pack".$n]["Ah_nominal"] = (hexdec( substr( $Daten["INFO"], 118, 4 )) / 100);
      $aktuelleDaten["Pack".$n]["cycle"] = hexdec( substr( $Daten["INFO"], 122, 4 ));
      $aktuelleDaten["SOC_SUM"] = ($aktuelleDaten["SOC_SUM"] + $aktuelleDaten["Pack".$n]["SOC"]);
      $aktuelleDaten["Batterie"]["Leistung"] =round(( $aktuelleDaten["Pack".$n]["Spannung"] * $aktuelleDaten["Pack".$n]["Strom"]),1);
    }
    else {
      goto Ausgang;
    }
    $Befehl = "20".$AdrHex."4644E002".$AdrHex;
    $CRC = Utils::crc16_us2000( $Befehl );
    $rc = US2000::us2000_auslesen( $USB1, "~".$Befehl.$CRC."\r" );
    if ($rc) {
      $Daten = US2000::us2000_daten_entschluesseln( $rc );
      $aktuelleDaten["Pack".$n]["Warn_Zelle1"] = hexdec( substr( $Daten["INFO"], 6, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle2"] = hexdec( substr( $Daten["INFO"], 8, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle3"] = hexdec( substr( $Daten["INFO"], 10, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle4"] = hexdec( substr( $Daten["INFO"], 12, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle5"] = hexdec( substr( $Daten["INFO"], 14, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle6"] = hexdec( substr( $Daten["INFO"], 16, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle7"] = hexdec( substr( $Daten["INFO"], 18, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle8"] = hexdec( substr( $Daten["INFO"], 20, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle9"] = hexdec( substr( $Daten["INFO"], 22, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle10"] = hexdec( substr( $Daten["INFO"], 24, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle11"] = hexdec( substr( $Daten["INFO"], 26, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle12"] = hexdec( substr( $Daten["INFO"], 28, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle13"] = hexdec( substr( $Daten["INFO"], 30, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle14"] = hexdec( substr( $Daten["INFO"], 32, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle15"] = hexdec( substr( $Daten["INFO"], 34, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Zelle16"] = hexdec( substr( $Daten["INFO"], 36, 2 ));
      $aktuelleDaten["Pack".$n]["Anz_Warn_Temp"] = hexdec( substr( $Daten["INFO"], 38, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Temp1"] = hexdec( substr( $Daten["INFO"], 40, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Temp2"] = hexdec( substr( $Daten["INFO"], 42, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Temp3"] = hexdec( substr( $Daten["INFO"], 44, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Temp4"] = hexdec( substr( $Daten["INFO"], 46, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Temp5"] = hexdec( substr( $Daten["INFO"], 48, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Temp6"] = hexdec( substr( $Daten["INFO"], 50, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_LadeStrom"] = hexdec( substr( $Daten["INFO"], 52, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Spannung"] = hexdec( substr( $Daten["INFO"], 54, 2 ));
      $aktuelleDaten["Pack".$n]["Anz_Alarme"] = hexdec( substr( $Daten["INFO"], 56, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Status2"] = hexdec( substr( $Daten["INFO"], 58, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Status3"] = hexdec( substr( $Daten["INFO"], 60, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Status4"] = hexdec( substr( $Daten["INFO"], 62, 2 ));
      $aktuelleDaten["Pack".$n]["Warn_Status5"] = hexdec( substr( $Daten["INFO"], 64, 2 ));
    }
  }

  /*************************************************************************/
  // Durchschnittswert aller Packs
  $aktuelleDaten["Batterie"]["SOC"] = ($aktuelleDaten["SOC_SUM"] / $aktuelleDaten["Packs"]);
  Log::write( "SOC Gesamt:".$aktuelleDaten["Batterie"]["SOC"], "   ", 6 );

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
  $aktuelleDaten["Info"]["Objekt.Text"] = $Objekt;
  $aktuelleDaten["Info"]["Firmware.Text"] = "0";
  $aktuelleDaten["Info"]["Produkt.Text"] = "Seplos BMS";
  $aktuelleDaten["zentralerTimestamp"] = ($aktuelleDaten["zentralerTimestamp"] + 10);
  if ($i == 1)
    Log::write( var_export( $aktuelleDaten, 1 ), "   ", 8 );


  /****************************************************************************
  //  User PHP Script, falls gewünscht oder nötig
  ****************************************************************************/
  if (file_exists($basedir."/custom/seplos_bms_math.php" )) {
    include $basedir.'/custom/seplos_bms_math.php'; // Falls etwas neu berechnet werden muss.
  }

  /**************************************************************************
  //  Alle ausgelesenen Daten werden hier bei Bedarf als mqtt Messages
  //  an den mqtt-Broker Mosquitto gesendet.
  //  Achtung! Die Übertragung dauert ca. 30 Sekunden!
  **************************************************************************/
  if ($MQTT) {
    Log::write( "MQTT Daten zum [ $MQTTBroker ] senden.", "   ", 1 );
    require($basedir."/services/mqtt_senden.php");
  }

  /****************************************************************************
  //  Zeit und Datum
  ****************************************************************************/
  //  Der Regler hat keine interne Uhr! Deshalb werden die Daten vom Raspberry benutzt.
  $aktuelleDaten["Timestamp"] = time( );
  $aktuelleDaten["Monat"] = date( "n" );
  $aktuelleDaten["Woche"] = date( "W" );
  $aktuelleDaten["Wochentag"] = strftime( "%A", time( ));
  $aktuelleDaten["Datum"] = date( "d.m.Y" );
  $aktuelleDaten["Uhrzeit"] = date( "H:i:s" );

  /****************************************************************************
  //  InfluxDB  Zugangsdaten ...stehen in der user.config.php
  //  falls nicht, sind das hier die default Werte.
  ****************************************************************************/
  $aktuelleDaten["InfluxAdresse"] = $InfluxAdresse;
  $aktuelleDaten["InfluxPort"] = $InfluxPort;
  $aktuelleDaten["InfluxUser"] = $InfluxUser;
  $aktuelleDaten["InfluxPassword"] = $InfluxPassword;
  $aktuelleDaten["InfluxDBName"] = $InfluxDBName;
  $aktuelleDaten["InfluxDaylight"] = $InfluxDaylight;
  $aktuelleDaten["InfluxDBLokal"] = $InfluxDBLokal;
  $aktuelleDaten["InfluxSSL"] = $InfluxSSL;
  $aktuelleDaten["Demodaten"] = false;
  Log::write( print_r( $aktuelleDaten, 1 ), "** ", 9 );

  /*********************************************************************
  //  Daten werden in die Influx Datenbank gespeichert.
  //  Lokal und Remote bei Bedarf.
  *********************************************************************/
  if ($InfluxDB_remote) {
    // Test ob die Remote Verbindung zur Verfügung steht.
    if ($RemoteDaten) {
      $rc = InfluxDB::influx_remote_test( );
      if ($rc) {
        $rc = InfluxDB::influx_remote( $aktuelleDaten );
        if ($rc) {
          $RemoteDaten = false;
        }
      }
      else {
        $RemoteDaten = false;
      }
    }
    if ($InfluxDB_local) {
      $rc = InfluxDB::influx_local( $aktuelleDaten );
    }
  }
  else {
    $rc = InfluxDB::influx_local( $aktuelleDaten );
  }
  if (is_file( $basedir."/config/1.user.config.php" )) {
    // Ausgang Multi-Regler-Version
    $Zeitspanne = (7 - (time( ) - $Start));
    Log::write( "Multi-Regler-Ausgang. ".$Zeitspanne, "   ", 2 );
    if ($Zeitspanne > 0) {
      sleep( $Zeitspanne );
    }
    break;
  }
  else {
    Log::write( "Schleife: ".($i)." Zeitspanne: ".(floor( (56 - (time( ) - $Start)) / ($Wiederholungen - $i + 1))), "   ", 9 );
    sleep( floor( (56 - (time( ) - $Start)) / ($Wiederholungen - $i + 1)));
  }
  if ($Wiederholungen <= $i or $i >= 6) {
    Log::write( "OK. Daten gelesen.", "   ", 9 );
    Log::write( "Schleife ".$i." Ausgang...", "   ", 8 );
    break;
  }
  $i++;
} while (($Start + 55) > time( ));
if (isset($aktuelleDaten["Packs"]) and isset($aktuelleDaten["Regler"])) {

  /*********************************************************************
  //  Jede Minute werden bei Bedarf einige Werte zur Homematic Zentrale
  //  übertragen.
  *********************************************************************/
  if (isset($Homematic) and $Homematic == true) {
    Log::write( "Daten werden zur HomeMatic übertragen...", "   ", 8 );
    require($basedir."/services/homematic.php");
  }

  /*********************************************************************
  //  Sollen Nachrichten an einen Messenger gesendet werden?
  //  Bei einer Multi-Regler-Version sollte diese Funktion nur bei einem
  //  Gerät aktiviert sein.
  *********************************************************************/
  if (isset($Messenger) and $Messenger == true) {
    Log::write( "Nachrichten versenden...", "   ", 8 );
    require($basedir."/services/meldungen_senden.php");
  }
  Log::write( "OK. Datenübertragung erfolgreich.", "   ", 7 );
}
else {
  Log::write( "Keine gültigen Daten empfangen.", "!! ", 6 );
}
Ausgang:Log::write( "------------   Stop   seplos_bms.php   ----------------- ", "|--", 6 );
return;
?>