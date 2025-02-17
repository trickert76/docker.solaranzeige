<?php

/*****************************************************************************
//  Solaranzeige Projekt             Copyright (C) [2016-2021]  [Ulrich Kunz]
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
//  Es dient dem Auslesen der Regler der Tracer Serie über die USB Schnittstelle
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
$Version = "";
$Device = "LR"; // LR = Laderegler
$RemoteDaten = true;
if (empty($WR_Adresse)) {
  $WR_ID = "01";
}
elseif (strlen( $WR_Adresse ) == 1) {
  $WR_ID = str_pad( $WR_Adresse, 2, "0", STR_PAD_LEFT );
}
else {
  $WR_ID = str_pad( substr( $WR_Adresse, - 2 ), 2, "0", STR_PAD_LEFT );
}
Log::write( "HEX WR_ID: ".$WR_ID, "+  ", 9 );
$Befehl = array("DeviceID" => $WR_ID, "BefehlFunctionCode" => "04", "RegisterAddress" => "3104", "RegisterCount" => "0001");
$Start = time( ); // Timestamp festhalten
Log::write( "---------   Start  tracer_regler.php  ------------------------ ", "|--", 6 );
Log::write( "Zentraler Timestamp: ".$zentralerTimestamp, "   ", 8 );
$aktuelleDaten = array();
$aktuelleDaten["zentralerTimestamp"] = $zentralerTimestamp;
setlocale( LC_TIME, "de_DE.utf8" );
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
if ($HF2211) {
  // HF2211 WLAN Gateway wird benutzt
  $USB1 = fsockopen( $WR_IP, $WR_Port, $errno, $errstr, 5 ); // 5 Sekunden Timeout
  if ($USB1 === false) {
    Log::write( "Kein Kontakt zum Wechselrichter ".$WR_IP."  Port: ".$WR_Port, "XX ", 3 );
    Log::write( "Exit.... ", "XX ", 3 );
    goto Ausgang;
  }
}
else {
  //  Nach em Öffnen des Port muss sofort der Regler ausgelesen werden, sonst
  //  sendet er asynchrone Daten!
  $USB1 = USB::openUSB( $USBRegler );
  if (!is_resource( $USB1 )) {
    Log::write( "USB Port kann nicht geöffnet werden. [1]", "XX ", 7 );
    Log::write( "Exit.... ", "XX ", 7 );
    goto Ausgang;
  }
}

/***************************************************************************
//  Einen Befehl an den Tracer senden
//
//  Per MQTT
//  Per HTTP
//
***************************************************************************/
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

    /*********************************************************************************
    //  In der Datei "befehle.ini.php" müssen alle gültigen Befehle aufgelistet
    //  werden, die man benutzen möchte.
    //  Achtung! Genau darauf achten, dass der Befehl richtig geschrieben wird,
    //  damit das Gerät keinen Schaden nimmt.
    //  curr_6000 ist nur zum Testen ...
    //  Siehe Dokument:  Befehle_senden.pdf
    *********************************************************************************/
    if (file_exists( $basedir."/config/befehle.ini" )) {
      Log::write( "Die Befehlsliste 'befehle.ini.php' ist vorhanden----", "|- ", 9 );
      $INI_File = parse_ini_file( $basedir."/config/befehle.ini", true );
      $Regler3 = $INI_File["Regler3"];
      Log::write( "Befehlsliste: ".print_r( $Regler3, 1 ), "|- ", 9 );
      foreach ($Regler3 as $Template) {
        $Subst = $Befehle[$i];
        $l = strlen( $Template );
        for ($p = 1; $p < $l;++$p) {
          Log::write( "Template: ".$Template." Subst: ".$Subst." l: ".$l, "|- ", 10 );
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
    $Teile = explode( "_", $Befehle[$i] );
    $Antwort = "";

    /***********************************************************************
    // Hier wird der Befehl gesendet...
    //
    ***********************************************************************/
    if (strtoupper( $Teile[0] ) == "LOAD") {
      if (strtoupper( $Teile[1] ) == "ON") {
        //  Load einschalten
        //  Load einschalten
        $Befehl = array("DeviceID" => $WR_ID, "BefehlFunctionCode" => "05", "RegisterAddress" => "0002", "RegisterCount" => "FF00");
        $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
        Log::write( "Load Ausgang eingeschaltet", "   ", 1 );
      }
      else {
        //  Load ausschalten
        //  Load ausschalten
        $Befehl = array("DeviceID" => $WR_ID, "BefehlFunctionCode" => "05", "RegisterAddress" => "0002", "RegisterCount" => "0000");
        $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
        Log::write( "Load Ausgang ausgeschaltet", "   ", 1 );
      }
    }
    sleep( 2 );
  }
  $rc = unlink( "/var/www/pipe/".$GeraeteNummer.".befehl.steuerung" );
  if ($rc) {
    Log::write( "Datei  /../pipe/".$GeraeteNummer.".befehl.steuerung  gelöscht.", "    ", 9 );
  }
}
else {
  Log::write( "Steuerdatei '".$GeraeteNummer.".befehl.steuerung' nicht vorhanden----", "|- ", 9 );
}
$Befehl = array("DeviceID" => $WR_ID, "BefehlFunctionCode" => "04", "RegisterAddress" => "3104", "RegisterCount" => "0001");
$i = 1;
do {
  Log::write( "Die Daten werden ausgelesen...".print_r( $Befehl, 1 ), ">  ", 9 );

  if (($Start + 9) < time( )) {
    Log::write( "Keine gültigen Daten empfangen.", "!! ", 6 );
    goto Ausgang;
  }

  /**************************************************************************
  //  Ab hier wird der Regler ausgelesen.
  //
  //  Ergebniswerte:
  //  $aktuelleDaten["Firmware"]
  //  $aktuelleDaten["Produkt"]
  //  $aktuelleDaten["Batteriespannung"]
  //  $aktuelleDaten["Solarstrom"]
  //  $aktuelleDaten["Solarspannung"]
  //  $aktuelleDaten["Batterieentladestrom"]
  //  $aktuelleDaten["WattstundenGesamt"]
  //  $aktuelleDaten["Temperatur"]
  //
  **************************************************************************/

  /****************************************************************************
  //  Ab hier wird der Regler ausgelesen.
  //
  ****************************************************************************/
  //  Solarspannung      Solarspannung      Solarspannung      Solarspannung
  //  Solarspannung      Solarspannung      Solarspannung      Solarspannung
  $Befehl["RegisterAddress"] = "3100";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  Log::write( "3100 : ".$rc, "   ", 8 );
  if ($rc === false) {
    Log::write( "Lesefehler.", "   ", 5 );
    sleep(1);
    continue;
  }
  $aktuelleDaten["Solarspannung"] = SolarXXL::solarxxl_daten( $rc, true );
  //  Solarstrom      Solarstrom      Solarstrom      Solarstrom     Solarstrom
  //  Solarstrom      Solarstrom      Solarstrom      Solarstrom     Solarstrom
  $Befehl["RegisterAddress"] = "3101";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["Solarstrom"] = SolarXXL::solarxxl_daten( $rc, true );
  // $aktuelleDaten["Batterieladestrom"] = solarxxl_daten($rc,true);
  //  Batterie Ladestrom LOW     Batterie Ladestrom LOW     Batterie Ladestrom LOW
  //  Batterie Ladestrom LOW     Batterie Ladestrom LOW     Batterie Ladestrom LOW
  $Befehl["RegisterAddress"] = "331B";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  Log::write( "331B : ".$rc, "   ", 8 );
  if ($rc === false) {
    continue;
  }
  $BatterieladestromLow = Renogy::renogy_daten( $rc, false, false );
  //  Batterie Ladestrom HIGH   Batterie Ladestrom HIGH   Batterie Ladestrom HIGH
  //  Batterie Ladestrom HIGH   Batterie Ladestrom HIGH   Batterie Ladestrom HIGH
  $Befehl["RegisterAddress"] = "331C";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  Log::write( "331C : ".$rc, "   ", 8 );
  if ($rc === false) {
    continue;
  }
  $BatterieladestromHigh = Renogy::renogy_daten( $rc, false, false );
  //  Geändert 3.7.2021
    $aktuelleDaten["Batterieladestrom"] = (Utils::hexdecs( $BatterieladestromHigh.$BatterieladestromLow ) / 100);
  $aktuelleDaten["Batterieladestrom"] = (Utils::hexdecs( $BatterieladestromLow ) / 100);
  Log::write( "LadestromHigh: ".$BatterieladestromHigh." LadestromLow: ".$BatterieladestromLow." Dezimal: ".$aktuelleDaten["Batterieladestrom"], "   ", 5 );

  //  Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch
  //  Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch
  $Befehl["RegisterAddress"] = "310D";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["Batterieentladestrom"] = SolarXXL::solarxxl_daten( $rc, true );
  //  Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch
  //  Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch
  $Befehl["RegisterAddress"] = "310E";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $VerbrauchsleistungLow = Renogy::renogy_daten( $rc, false, false );
  //  Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch
  //  Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch     Verbrauch
  $Befehl["RegisterAddress"] = "310F";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["Batterieentladeleistung"] = round( Utils::hexdecs( Renogy::renogy_daten( $rc, false, false ).$VerbrauchsleistungLow ) / 100 );
  //  Device Temperatur    Device Temperatur     Device Temperatur
  //  Device Temperatur    Device Temperatur     Device Temperatur
  $Befehl["RegisterAddress"] = "3111";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $Temperatur = SolarXXL::solarxxl_daten( $rc, true, true );
  $aktuelleDaten["Temperatur"] = floor( round( $Temperatur, 0 ));
  //  Batterie Temperatur    Batterie Temperatur     Batterie Temperatur
  //  Batterie Temperatur    Batterie Temperatur     Batterie Temperatur
  $Befehl["RegisterAddress"] = "3110";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $Temperatur = SolarXXL::solarxxl_daten( $rc, true, true );
  $aktuelleDaten["BatterieTemperatur"] = floor( round( $Temperatur, 0 ));
  //  Batteriespannung    Batteriespannung    Batteriespannung    Batteriespannung
  //  Batteriespannung    Batteriespannung    Batteriespannung    Batteriespannung
  $Befehl["RegisterAddress"] = "331A";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["Batteriespannung"] = SolarXXL::solarxxl_daten( $rc, true );
  //  Wh Stunden Heute   Wh Stunden Heute   Wh Stunden Heute
  //  Wh Stunden Heute   Wh Stunden Heute   Wh Stunden Heute
  $Befehl["RegisterAddress"] = "330C";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $WattStundenGesamtHeuteLow = SolarXXL::solarxxl_daten( $rc, false );
  //  Wh Stunden Heute   Wh Stunden Heute   Wh Stunden Heute
  //  Wh Stunden Heute   Wh Stunden Heute   Wh Stunden Heute
  $Befehl["RegisterAddress"] = "330D";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["WattstundenGesamtHeute"] = round( (((SolarXXL::solarxxl_daten( $rc, false ) * 65535) + $WattStundenGesamtHeuteLow)) * 10 );

  /*********************************************************************/
  //  Wh Stunden Gesamt   Wh Stunden Gesamt   Wh Stunden Gesamt
  //  Wh Stunden Gesamt   Wh Stunden Gesamt   Wh Stunden Gesamt
  $Befehl["RegisterAddress"] = "3312";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $WattStundenGesamtLow = Renogy::renogy_daten( $rc, false, false );
  //  Wh Stunden    Wh Stunden    Wh Stunden    Wh Stunden    Wh Stunden
  //  Wh Stunden    Wh Stunden    Wh Stunden    Wh Stunden    Wh Stunden
  $Befehl["RegisterAddress"] = "3313";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["WattstundenGesamt"] = round( (Utils::hexdecs( Renogy::renogy_daten( $rc, false, false ).$WattStundenGesamtLow )) * 10 );
  //  Verbrauch Wh Stunden Heute    Verbrauch Wh Stunden Heute
  //  Verbrauch Wh Stunden Heute    Verbrauch Wh Stunden Heute
  $Befehl["RegisterAddress"] = "3304";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $VerbrauchGesamtHeuteLow = Renogy::renogy_daten( $rc, false, false );
  //  Verbrauch Wh Stunden Heute    Verbrauch Wh Stunden Heute
  //  Verbrauch Wh Stunden Heute    Verbrauch Wh Stunden Heute
  $Befehl["RegisterAddress"] = "3305";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["VerbrauchGesamtHeute"] = round( (Utils::hexdecs( Renogy::renogy_daten( $rc, false, false ).$VerbrauchGesamtHeuteLow )) * 10 );
  //  Verbrauch Wh Stunden Gesamt   Verbrauch Wh Stunden Gesamt
  //  Verbrauch Wh Stunden Gesamt   Verbrauch Wh Stunden Gesamt
  $Befehl["RegisterAddress"] = "330A";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $VerbrauchGesamtLow = Renogy::renogy_daten( $rc, false, false );
  //  Verbrauch Wh Stunden Gesamt   Verbrauch Wh Stunden Gesamt
  //  Verbrauch Wh Stunden Gesamt   Verbrauch Wh Stunden Gesamt
  $Befehl["RegisterAddress"] = "330B";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["VerbrauchGesamt"] = round( (Utils::hexdecs( Renogy::renogy_daten( $rc, false, false ).$VerbrauchGesamtLow )) * 10 );
  //  Max PV Volt Heute
  //  Max PV Volt Heute
  $Befehl["RegisterAddress"] = "3300";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["SolarspannungMaxHeute"] = SolarXXL::solarxxl_daten( $rc, true );
  //  Max PV Energie Heute
  //  Max PV Energie Heute
  $Befehl["RegisterAddress"] = "3102";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $SolarleistungLow = Renogy::renogy_daten( $rc, false, false );
  //  Max PV Energie Heute
  //  Max PV Energie Heute
  $Befehl["RegisterAddress"] = "3103";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $SolarleistungHigh = Renogy::renogy_daten( $rc, false, false );
  $aktuelleDaten["Solarleistung"] = (Utils::hexdecs( $SolarleistungHigh.$SolarleistungLow ) / 100);
  //  Status
  //  Status
  $Befehl["RegisterAddress"] = "3201";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["Optionen"] = Utils::d2b( SolarXXL::solarxxl_daten( $rc, false ));
  $aktuelleDaten["Ladestatus"] = bindec( substr( $aktuelleDaten["Optionen"], - 4, 2 ));
  //  Batterie SOC     Batterie SOC     Batterie SOC     Batterie SOC     Batterie SOC
  //  Batterie SOC     Batterie SOC     Batterie SOC     Batterie SOC     Batterie SOC
  $Befehl["RegisterAddress"] = "311A";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["BatterieSOC"] = SolarXXL::solarxxl_daten( $rc, false );
  //  Batterie max Volt    Batterie max Volt    Batterie max Volt     Batterie max Volt
  //  Batterie max Volt    Batterie max Volt    Batterie max Volt     Batterie max Volt
  $Befehl["RegisterAddress"] = "3302";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["BatterieMaxVoltHeute"] = SolarXXL::solarxxl_daten( $rc, true );
  //  Batterie min Volt    Batterie min Volt    Batterie min Volt     Batterie min Volt
  //  Batterie min Volt    Batterie min Volt    Batterie min Volt     Batterie min Volt
  $Befehl["RegisterAddress"] = "3303";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["BatterieMinVoltHeute"] = SolarXXL::solarxxl_daten( $rc, true );
  //  Load Volt    Load Volt    Load Volt    Load Volt    Load Volt    Load Volt
  //  Load Volt    Load Volt    Load Volt    Load Volt    Load Volt    Load Volt
  $Befehl["RegisterAddress"] = "310C";
  $Befehl["BefehlFunctionCode"] = "04";
  $rc = Tracer::tracer_auslesen( $USB1, $Befehl );
  if ($rc === false) {
    continue;
  }
  $aktuelleDaten["LoadVolt"] = SolarXXL::solarxxl_daten( $rc, true );

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
  $aktuelleDaten["Firmware"] = 0;
  $aktuelleDaten["Produkt"] = "Tracer Serie";
  $aktuelleDaten["zentralerTimestamp"] = ($aktuelleDaten["zentralerTimestamp"] + 10);
  if ($i == 1)
    Log::write( var_export( $aktuelleDaten, 1 ), "   ", 8 );

  /****************************************************************************
  //  User PHP Script, falls gewünscht oder nötig
  ****************************************************************************/
  if (file_exists($basedir."/custom/tracer_regler_math.php" )) {
    include $basedir.'/custom/tracer_regler_math.php'; // Falls etwas neu berechnet werden muss.
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
} while (($Start + 54) > time( ));
if (isset($aktuelleDaten["Firmware"]) and isset($aktuelleDaten["Regler"])) {

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
Ausgang:

Log::write( "---------   Stop   tracer_regler.php    ---------------------- ", "|--", 6 );
return;
?>