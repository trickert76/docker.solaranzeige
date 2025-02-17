<?php

/*****************************************************************************
//  Solaranzeige Projekt             Copyright (C) [2016-2020]  [Ulrich Kunz]
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
//  Es dient dem Auslesen des Labornetzteil JT-8600 über den
//  Seriell - USB Adapter.  Die serielle Geschwindgkeit muss au 38400 Baud stehen.
//  Und die GeräteID muss mit der GeräteNummer in der user.config.php Datei
//  übereinstimmen.
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
$Antwort="";
$Start = time();  // Timestamp festhalten
Log::write("---------------   Start  labornetzteil.php   -------------------- ","|--",6);

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



//  Nach em Öffnen des Port muss sofort der Regler ausgelesen werden, sonst
//  sendet er asynchrone Daten!
$USB1 = USB::openUSB($USBRegler);
if (!is_resource($USB1)) {
  Log::write("USB Port kann nicht geöffnet werden. [1]","XX ",7);
  Log::write("Exit.... ","XX ",7);
  goto Ausgang;
}





/***************************************************************************
//  Einen Befehl an das Netzteil senden
//
//  Per MQTT  10 = 1234   Setzen der Ausgangspannung auf 12,34 Volt
//  Per HTTP  10_1234
//
***************************************************************************/
if (file_exists("/var/www/pipe/".$GeraeteNummer.".befehl.steuerung")) {

    Log::write("Steuerdatei '".$GeraeteNummer.".befehl.steuerung' vorhanden----","|- ",5);
    $Inhalt = file_get_contents("/var/www/pipe/".$GeraeteNummer.".befehl.steuerung");
    $Befehle = explode("\n",trim($Inhalt));
    Log::write("Befehle: ".print_r($Befehle,1),"|- ",9);

    for ($i = 0; $i < count($Befehle); $i++) {

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
      if (file_exists($basedir."/config/befehle.ini")) {

        Log::write("Die Befehlsliste 'befehle.ini.php' ist vorhanden----","|- ",9);
        $INI_File =  parse_ini_file($basedir."/config/befehle.ini", true);
        $Regler33 = $INI_File["Regler33"];
        Log::write("Befehlsliste: ".print_r($Regler33,1),"|- ",9);

        foreach ($Regler33 as $Template) {
          $Subst = $Befehle[$i];
          $l = strlen($Template);
          for ($p = 1; $p < $l; ++$p) {
            Log::write("Template: ".$Template." Subst: ".$Subst." l: ".$l,"|- ",10);
            if ($Template[$p] == "#") {
              $Subst[$p] = "#";
            }
          }
          if ($Template == $Subst) {
            break;
          }
        }
        if ($Template != $Subst) {
          Log::write("Dieser Befehl ist nicht zugelassen. ".$Befehle[$i],"|o ",3);
          Log::write("Die Verarbeitung der Befehle wird abgebrochen.","|o ",3);
          break;
        }
      }
      else {
        Log::write("Die Befehlsliste 'befehle.ini.php' ist nicht vorhanden----","|- ",3);
        break;
      }

      $Teile = explode("_",$Befehle[$i]);
      $Antwort = "";
      // Hier wird der Befehl gesendet...
      //
      //  $Teile[0] = Befehl  10,11,12
      //  $Teile[1] = Wert  >  Siehe Protokollbeschreibung!

      $rc = Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"w".$Teile[0]."=".$Teile[1]);
      Log::write("Befehl: ".$Teile[0]."  Wert: ".$Teile[1]." Ergebnis: ".$rc,"   ",3);

      sleep(1);
    }
    $rc = unlink("/var/www/pipe/".$GeraeteNummer.".befehl.steuerung");
    if ($rc) {
      Log::write("Datei  /../pipe/".$GeraeteNummer.".befehl.steuerung  gelöscht.","    ",9);
    }
}
else {
  Log::write("Steuerdatei '".$GeraeteNummer.".befehl.steuerung' nicht vorhanden----","|- ",9);
}


$i = 1;
do {
  Log::write("Die Daten werden ausgelesen...","+  ",9);

  /****************************************************************************
  //  Ab hier wird der Laderegler ausgelesen.
  //
  ****************************************************************************/


  $aktuelleDaten["DC_maxVolt"] = (Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"r00=0")/100);
  $aktuelleDaten["DC_maxAmpere"] = (Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"r01=0")/1000);
  $aktuelleDaten["DC_setVolt"] = (Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"r10=0")/100);
  $aktuelleDaten["DC_setAmpere"] = (Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"r11=0")/1000);
  $aktuelleDaten["Geraetestatus"] = (Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"r12=0"));
  $aktuelleDaten["DC_Volt"] = (Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"r30=0")/100);
  $aktuelleDaten["DC_Ampere"] = (Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"r31=0")/1000);
  $aktuelleDaten["DC_Konstante"] = (Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"r32=0"));
  $aktuelleDaten["Temperatur"] = (Labornetzteil::ln_lesen($USB1,$GeraeteNummer,"r33=0"));


  /***************************************************************************
  //  Ende Laderegler auslesen
  ***************************************************************************/



  $FehlermeldungText = "";


  /****************************************************************************
  //  Die Daten werden für die Speicherung vorbereitet.
  ****************************************************************************/
  $aktuelleDaten["Regler"] = $Regler;
  $aktuelleDaten["Objekt"] = $Objekt;
  $aktuelleDaten["Firmware"] = "1.0";
  $aktuelleDaten["Produkt"] = "JT-DPM8600";
  $aktuelleDaten["zentralerTimestamp"] = ($aktuelleDaten["zentralerTimestamp"]+10);

  // Dummy
  $aktuelleDaten["WattstundenGesamtHeute"] = 0;


  Log::write(var_export($aktuelleDaten,1),"   ",8);


  /****************************************************************************
  //  User PHP Script, falls gewünscht oder nötig
  ****************************************************************************/
  if ( file_exists($basedir."/custom/labornetzteil_math.php")) {
    include $basedir.'/custom/labornetzteil_math.php';  // Falls etwas neu berechnet werden muss.
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


  Log::write(print_r($aktuelleDaten,1),"*- ",8);


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
    Log::write("Schleife ".$i." Ausgang...","   ",5);
    break;
  }

  $i++;
} while (($Start + 54) > time());


if ($aktuelleDaten["KeineSonne"] == false) {


  /*********************************************************************
  //  Jede Minute werden bei Bedarf einige Werte zur Homematic Zentrale
  //  übertragen.
  *********************************************************************/
  if (isset($Homematic) and $Homematic == true) {
    $aktuelleDaten["Solarspannung"] = $aktuelleDaten["Solarspannung1"];
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

Log::write("---------------   Stop   labornetzteil.php   -------------------- ","|--",6);

return;






?>
