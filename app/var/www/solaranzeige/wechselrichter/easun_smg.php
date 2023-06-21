<?php

/*****************************************************************************
//  Solaranzeige Projekt             Copyright (C) [2015-2020]  [Ulrich Kunz]
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
//  Es dient dem Auslesen des Wechselrichters EASUN SMG über eine RS485
//  Schnittstelle mit USB Adapter.
//  Das Auslesen wird hier mit einer Schleife durchgeführt. Wie oft die Daten
//  ausgelesen und gespeichert werden steht in der user.config.php
//
//
*****************************************************************************/
// Im Fall, dass man die Device manuell eingeben muss
if (isset($USBDevice) and !empty($USBDevice)) {
  $USBRegler = $USBDevice;
}
$Tracelevel = 7; //  1 bis 10  10 = Debug
$RemoteDaten = true;
$Start = time( ); // Timestamp festhalten
Log::write( "----------------------   Start  easun_smg.php   --------------------- ", "|--", 6 );
Log::write( "Zentraler Timestamp: ".$zentralerTimestamp, "   ", 8 );
$aktuelleDaten = array();
$aktuelleDaten["zentralerTimestamp"] = $zentralerTimestamp;
setlocale( LC_TIME, "de_DE.utf8" );
//  Hardware Version ermitteln.
$Teile = explode( " ", $Platine );
if ($Teile[1] == "Pi") {
  Log::write( "Hardware Version: ".$Platine, "o  ", 7 );
  $Version = trim( $Teile[2] );
  if ($Teile[3] == "Model") {
    $Version .= trim( $Teile[4] );
    if ($Teile[5] == "Plus") {
      $Version .= trim( $Teile[5] );
    }
  }
}
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
if (empty($WR_Adresse)) {
  $WR_ID = "01";
}
elseif (strlen( $WR_Adresse ) == 1) {
  $WR_ID = str_pad( dechex( $WR_Adresse ), 2, "0", STR_PAD_LEFT );
}
elseif (strlen( $WR_Adresse ) == 2) {
  $WR_ID = str_pad( dechex( substr( $WR_Adresse, - 2 )), 2, "0", STR_PAD_LEFT );
}
else {
  $WR_ID = dechex( $WR_Adresse );
}
Log::write( "WR_ID: ".$WR_ID, "+  ", 8 );

/*****************************************************************************
//  Die Status Datei wird dazu benutzt, um die Leistung des Reglers
//  pro Tag zu speichern.
//
*****************************************************************************/
$StatusFile = $basedir."/database/".$GeraeteNummer.".WhProTag.txt";
if (!file_exists( $StatusFile )) {

  /***************************************************************************
  //  Inhalt der Status Datei anlegen, wenn nicht existiert.
  ***************************************************************************/
  $rc = file_put_contents( $StatusFile, "0" );
  if ($rc === false) {
    Log::write( "Konnte die Datei whProTag_delta.txt nicht anlegen.", 5 );
  }
  $aktuelleDaten["WattstundenGesamtHeute"] = 0;
}
else {
  $aktuelleDaten["WattstundenGesamtHeute"] = file_get_contents( $StatusFile );
  Log::write( "WattstundenGesamtHeute: ".$aktuelleDaten["WattstundenGesamtHeute"], "   ", 8 );
}
$USB1 = USB::openUSB( $USBRegler );
if (!is_resource( $USB1 )) {
  Log::write( "USB Port kann nicht geöffnet werden. [1]", "XX ", 7 );
  Log::write( "Exit.... ", "XX ", 7 );
  goto Ausgang;
}

/************************************************************************************
//  Sollen Befehle an den Wechselrichter gesendet werden?
//  Start "Befehl senden"
************************************************************************************/
if (file_exists( "/var/www/pipe/".$GeraeteNummer.".befehl.steuerung" )) {
  Log::write( "Steuerdatei '".$GeraeteNummer.".befehl.steuerung' vorhanden----", "|- ", 5 );
  $Inhalt = file_get_contents( "/var/www/pipe/".$GeraeteNummer.".befehl.steuerung" );
  $Befehle = explode( "\n", trim( $Inhalt ));
  Log::write( "Befehle: ".print_r( $Befehle, 1 ), "|- ", 9 );
  for ($i = 0; $i < count( $Befehle ); $i++) {
    if ($i >= 4) {
      //  Es werden nur maximal 5 Befehle pro Datei verarbeitet!
      break;
    }

    /**************************************************************************
    //  In der Datei "befehle.ini.php" müssen alle gültigen Befehle aufgelistet
    //  werden, die man benutzen möchte.
    //  Achtung! Genau darauf achten, dass der Befehl richtig geschrieben wird,
    //  damit das Gerät keinen Schaden nimmt.
    //  QPI ist nur zum Testen ...
    //  Siehe Dokument:  Befehle_senden.pdf
    **************************************************************************/
    if (file_exists( $basedir."/config/befehle.ini" )) {
      Log::write( "Die Befehlsliste 'befehle.ini.php' ist vorhanden----", "|- ", 9 );
      $INI_File = parse_ini_file( $basedir."/config/befehle.ini", true );
      $Regler71 = $INI_File["Regler71"];
      Log::write( "Befehlsliste: ".print_r( $Regler71, 1 ), "|- ", 10 );
      foreach ($Regler71 as $Template) {
        $Subst = $Befehle[$i];
        $l = strlen( $Template );
        for ($p = 1; $p < $l;++$p) {
          if ($Template[$p] == "#") {
            $Subst[$p] = "#";
          }
        }
        if ($Template == $Subst) {
          break;
        }
      }
      if ($Template != $Subst) {
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
    if (strlen( $Befehle[$i] ) > 4) {
      $Teile = explode( "_", $Befehle[$i] );
      $RegWert = str_pad( dechex( $Teile[1] ), 4, "0", STR_PAD_LEFT );
      $Befehl["DeviceID"] = $WR_ID;
      $Befehl["BefehlFunctionCode"] = "10";
      $Befehl["RegisterAddress"] = str_pad( dechex( $Teile[0] ), 4, "0", STR_PAD_LEFT );
      $Befehl["RegisterCount"] = "0001";
      $Befehl["Befehl"] = $RegWert;
      Log::write( "Befehl: ".print_r( $Befehl, 1 ), "    ", 10 );
      $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
      if ($rc["ok"] == true) {
        $wert = true;
        Log::write( "Befehlsausführung war erfolgreich.", "   ", 7 );
        Log::write( "Register ".hexdec( $Befehl["RegisterAddress"] )." Wert: ".$Befehl["Befehl"], "   ", 2 );
      }
      else {
        $Wert = false;
        Log::write( "Befehlsausführung war nicht erfolgreich! ", "XX ", 2 );
        Log::write( "Register ".hexdec( $Befehl["RegisterAddress"] )." Wert: ".$Befehl["Befehl"], "XX ", 2 );
      }
    }
    else {
      Log::write( "Befehl ungültig: ".$Befehle[$i], "    ", 2 );
    }
  }
  $rc = unlink( "/var/www/pipe/".$GeraeteNummer.".befehl.steuerung" );
  if ($rc) {
    Log::write( "Datei  /pipe/".$GeraeteNummer.".befehl.steuerung  gelöscht.", "    ", 8 );
  }
}
else {
  Log::write( "Steuerdatei '".$GeraeteNummer.".befehl.steuerung' nicht vorhanden----", "|- ", 9 );
}

/*****************************************************************************
//  Ende "Befehl senden"
*****************************************************************************/
$i = 1;
do {
  Log::write( "Die Daten werden ausgelesen...", "+  ", 9 );

  /****************************************************************************
  //  Ab hier wird der Regler ausgelesen.
  //
  //
  ****************************************************************************/
  $Befehl["DeviceID"] = $WR_ID;
  $Befehl["BefehlFunctionCode"] = "03";
  $Befehl["RegisterAddress"] = str_pad( dechex( 186 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "000C";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Seriennummer"] = trim( Utils::hex2str( $rc["data"] ));
  $Befehl["RegisterAddress"] = str_pad( dechex( 201 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Regler_Mode"] = hexdec( $rc["data"] ); // 0: Power On Mode
  $Befehl["RegisterAddress"] = str_pad( dechex( 202 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Netzspannung"] = (hexdec( $rc["data"] ) / 10);
  $Befehl["RegisterAddress"] = str_pad( dechex( 203 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Netzfrequenz"] = (hexdec( $rc["data"] ) / 100);
  $Befehl["RegisterAddress"] = str_pad( dechex( 204 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Netzleistung"] = hexdec( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 205 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["AC_Ausgangsspannung"] = (hexdec( $rc["data"] ) / 10);
  $Befehl["RegisterAddress"] = str_pad( dechex( 206 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["AC_Ausgangsstrom"] = (hexdec( $rc["data"] ) / 10);
  $Befehl["RegisterAddress"] = str_pad( dechex( 207 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["AC_Ausgangsfrequenz"] = (hexdec( $rc["data"] ) / 100);
  $Befehl["RegisterAddress"] = str_pad( dechex( 208 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["AC_Ausgangsleistung"] = Utils::hexdecs( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 209 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["AC_Ausgangslast"] = Utils::hexdecs( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 210 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Out_Spannung"] = (hexdec( $rc["data"] ) / 10);
  $Befehl["RegisterAddress"] = str_pad( dechex( 211 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Out_Strom"] = (hexdec( $rc["data"] ) / 10);
  $Befehl["RegisterAddress"] = str_pad( dechex( 212 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Out_Frequenz"] = (hexdec( $rc["data"] ) / 100);
  $Befehl["RegisterAddress"] = str_pad( dechex( 213 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Out_Leistung"] = Utils::hexdecs( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 215 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Batteriespannung"] = (hexdec( $rc["data"] ) / 10);
  $Befehl["RegisterAddress"] = str_pad( dechex( 216 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Batterie_Strom"] = (Utils::hexdecs( $rc["data"] ) / 10);
  $Befehl["RegisterAddress"] = str_pad( dechex( 217 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Batterie_Leistung"] = Utils::hexdecs( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 229 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["SOC"] = hexdec( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 227 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Temperatur"] = Utils::hexdecs( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 219 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Solarspannung"] = (hexdec( $rc["data"] ) / 10);
  $Befehl["RegisterAddress"] = str_pad( dechex( 220 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Solarstrom"] = (hexdec( $rc["data"] ) / 10);
  $Befehl["RegisterAddress"] = str_pad( dechex( 223 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["PV_Leistung"] = Utils::hexdecs( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 300 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Mode"] = hexdec( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 100 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0002";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Fehler"] = hexdec( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 108 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0002";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Warnungen"] = hexdec( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 331 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Modus"] = hexdec( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 301 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["DeviceStatus"] = hexdec( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 326 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Bat_Charge_priority"] = hexdec( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 332 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Charge_max"] = hexdec( $rc["data"] );
  $Befehl["RegisterAddress"] = str_pad( dechex( 333 ), 4, "0", STR_PAD_LEFT );
  $Befehl["RegisterCount"] = "0001";
  $rc = Phocos::phocos_pv18_auslesen( $USB1, $Befehl );
  $aktuelleDaten["Mains_max"] = hexdec( $rc["data"] );

  /****************************************************************************
  //  ENDE REGLER AUSLESEN      ENDE REGLER AUSLESEN      ENDE REGLER AUSLESEN
  ****************************************************************************/
  // Dummy
  $aktuelleDaten["Max_Ampere"] = 0;
  $aktuelleDaten["IntModus"] = 0;
  $aktuelleDaten["OutputMode"] = 0;
  $aktuelleDaten["Inverterstatus"] = 0;
  $aktuelleDaten["AC_Ausgangslast"] = 0;

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
  $aktuelleDaten["Produkt"] = "EASUN SMG";
  $aktuelleDaten["Firmware"] = "0";
  $aktuelleDaten["zentralerTimestamp"] = ($aktuelleDaten["zentralerTimestamp"] + 10);
  Log::write( print_r( $aktuelleDaten, 1 ), "   ", 8 );

  /****************************************************************************
  //  User PHP Script, falls gewünscht oder nötig
  ****************************************************************************/
  if (file_exists($basedir."/custom/easun_smg_math.php" )) {
    include $basedir.'/custom/easun_smg_math.php'; // Falls etwas neu berechnet werden muss.
  }

  /**************************************************************************
  //  Alle ausgelesenen Daten werden hier bei Bedarf als mqtt Messages
  //  an den mqtt-Broker Mosquitto gesendet.
  //  Achtung! Die Übertragung dauert ca. 30 Sekunden!
  **************************************************************************/
  if ($MQTT and strtoupper( $MQTTAuswahl ) != "OPENWB") {
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
  elseif ($InfluxDB_local) {
    $rc = InfluxDB::influx_local( $aktuelleDaten );
  }
  if (is_file( $basedir."/config/1.user.config.php" )) {
    // Ausgang Multi-Regler-Version
    $Zeitspanne = (9 - (time( ) - $Start));
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
    Log::write( "Schleife ".$i." Ausgang...", "   ", 8 );
    break;
  }
  $i++;
} while (($Start + 54) > time( ));
if (isset($aktuelleDaten["Seriennummer"]) and isset($aktuelleDaten["SOC"])) {

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

/*****************************************************************************
//  Die Status Datei wird dazu benutzt, um die Leistung des Reglers
//  pro Tag zu speichern.
//  Der Aufwand wird betrieben, da der Wechselrichter mit sehr wenig Licht
//  tagsüber sich ausschaltet und der Zähler sich zurück setzt.
//  Achtung! Dieser Wert wird jeden Tag um Mitternacht auf 0 gesetzt.
//  Leistung in Watt / 60 Minuten, da 60 mal in der Stunde addiert wird.
*****************************************************************************/
if (file_exists( $StatusFile )) {

  /***************************************************************************
  //  Die Status Datei wird dazu benutzt, um die Leistung des Reglers
  //  pro Tag zu speichern.
  //  Jede Nacht 0 Uhr Tageszähler auf 0 setzen
  ***************************************************************************/
  if (date( "H:i" ) == "00:00" or date( "H:i" ) == "00:01") {
    $rc = file_put_contents( $StatusFile, "0" );
    Log::write( "WattstundenGesamtHeute  gesetzt.", "o- ", 5 );
  }

  /***************************************************************************
  //  Daten einlesen ...   ( Watt * Stunden ) pro Tag = Wh
  ***************************************************************************/
  $whProTag = file_get_contents( $StatusFile );
  $whProTag = ($whProTag + ($aktuelleDaten["PV_Leistung"]) / 60);
  $rc = file_put_contents( $StatusFile, round( $whProTag, 2 ));
  Log::write( "WattstundenGesamtHeute: ".round( $whProTag, 2 ), "   ", 5 );
}
Ausgang:Log::write( "----------------------   Stop   easun_smg.php   --------------------- ", "|--", 6 );
return;
?>