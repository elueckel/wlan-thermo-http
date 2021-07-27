<?php

if (!defined('vtBoolean')) {
    define('vtBoolean', 0);
    define('vtInteger', 1);
    define('vtFloat', 2);
    define('vtString', 3);
    define('vtArray', 8);
    define('vtObject', 9);
}


	class WLAN_BBQ_Thermo_HTTP extends IPSModule
	
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			//Properties
			
			$this->RegisterPropertyString("IP","");
			$this->RegisterPropertyBoolean("System_Messages", 0);
			$this->RegisterPropertyInteger("Timer", "60");
			$this->RegisterPropertyInteger("System_BatteryThreshold", "15");
			$this->RegisterPropertyString("System_BatteryText", "Die Batterie ist fast leer");
			$this->RegisterPropertyInteger("System_AutoOff", "5");
			$this->RegisterPropertyString("System_OffWarningText", "Das Thermometer ist nicht erreichbar - prüfen?");
			$this->RegisterPropertyString("System_OffText", "Das Thermometer Modul wurde ausgeschaltet");
			$this->RegisterPropertyBoolean("Coretemp", 0);

			$this->RegisterPropertyBoolean("Channel1Active", 0);
			$this->RegisterPropertyBoolean("Channel2Active", 0);
			$this->RegisterPropertyBoolean("Channel3Active", 0);
			$this->RegisterPropertyBoolean("Channel4Active", 0);
			$this->RegisterPropertyBoolean("Channel5Active", 0);
			$this->RegisterPropertyBoolean("Channel6Active", 0);
			$this->RegisterPropertyBoolean("ArchiveTurnedOn", 0);
			$this->RegisterPropertyBoolean("ArchiveDumpTemperature", 0);
			
			$this->RegisterPropertyInteger("EmailVariable", 0);
			
			$this->RegisterPropertyBoolean("NotifyByApp", 0);
			$this->RegisterPropertyBoolean("NotifyByEmail", 0);

			$this->RegisterPropertyBoolean("MessageOK", 0);
			$this->RegisterPropertyString("MessageOKText","Die Temperatur ist im Zielbereich - alles OK");
			$this->RegisterPropertyBoolean("MessageWarmingup", 0);
			$this->RegisterPropertyString("MessageWarmingupText","Der Grill wärmt auf");
			$this->RegisterPropertyBoolean("MessageTooCold", 0);
			$this->RegisterPropertyString("MessageTooColdText","Die Temperatur hat den Minimalwert unterschritten");
			$this->RegisterPropertyBoolean("MessageTooHigh", 0);
			$this->RegisterPropertyString("MessageTooHighText","Die Temperatur hat den Maximalwert überschritten");
			
			

			if (IPS_VariableProfileExists("WT.Channel_Status") == false){
				IPS_CreateVariableProfile("WT.Channel_Status", 1);
				IPS_SetVariableProfileValues("WT.Channel_Status", 0, 0, 1);
				IPS_SetVariableProfileDigits("WT.Channel_Status", 1);
				IPS_SetVariableProfileIcon("WT.Channel_Status",  "Temperature");
				IPS_SetVariableProfileAssociation("WT.Channel_Status", 0, $this->Translate("Not Found"),"",0x808080);
				IPS_SetVariableProfileAssociation("WT.Channel_Status", 1, $this->Translate("OK"),"",0x00ff00);
				IPS_SetVariableProfileAssociation("WT.Channel_Status", 2, $this->Translate("Warming Up"),"",0x00ffff);
				IPS_SetVariableProfileAssociation("WT.Channel_Status", 3, $this->Translate("Too Cold"),"",0x0000ff);
				IPS_SetVariableProfileAssociation("WT.Channel_Status", 4, $this->Translate("Too Hot"),"",0xff0000);
			}

			if (IPS_VariableProfileExists("WT.BBQ_Temperature") == false){
				IPS_CreateVariableProfile("WT.BBQ_Temperature", 2);
				IPS_SetVariableProfileValues("WT.BBQ_Temperature", 0, 400, 1);
				IPS_SetVariableProfileDigits("WT.BBQ_Temperature", 0);
				IPS_SetVariableProfileIcon("WT.BBQ_Temperature",  "Temperature");
				IPS_SetVariableProfileText("WT.BBQ_Temperature", "", "°C");
			}

			if (IPS_VariableProfileExists("WT.CoreTemp_Pork") == false){
				IPS_CreateVariableProfile("WT.CoreTemp_Pork", 3);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 0, "Bauch, gefüllt vollgar - 70-75°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 1, "Bauch, vollgar 80-85°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 2, "Hackfleisch 75°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 3, "Haxe gebraten, vollgar 80-85°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 4, "Haxe gepökelt, vollgar 75-80°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 5, "Kassler Aufschintt, Buffet rosa, 55-60°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 6, "Kassler, vollgar 60-68°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 7, "Keule, vollgar 75°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 8, "Keule, hellrosa 65-68°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 9, "Kochschinken, sehr saftig 64-68°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 10, "Pulled Pork, 95°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 11, "Rippchen, vollgar 65°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 12, "Schinken im Brotteig, vollgar 65-70°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 13, "Schweinefilet, rosa 63°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 14, "Schweinefilet, vollgar 65°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 15, "Schweinekamm, vollgar 70-75°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 16, "Schweinekopf, vollgar 75-82°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 17, "Schweinerücken, leicht hellrosa 65-70°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 18, "Schweineschulter, vollgar 75°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Pork", 19, "Schweinezunge, vollgar 85-90°C","",-1);
			}


			if (IPS_VariableProfileExists("WT.CoreTemp_Beef") == false){
				IPS_CreateVariableProfile("WT.CoreTemp_Beef", 3);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 0, "Beef Brisket, 85°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 1, "Falsches Filet, medium 60-65°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 2, "Falsches Filet, vollgar 70-75°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 3, "Rinderfilet/lende, englisch-rosa 38-55°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 4, "Rinderfilet/lende, medium 55-58°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 5, "Rinderbraten, vollgar 85-90°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 6, "Rindergrust, vollgar 90-95°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 7, "Rindsrose, vollgar 85-90°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 8, "Roastbeef, medium 55-60°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 9, "Rouladen aus Filet, 58°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 10, "Rouladen aus Keule, 70°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Beef", 11, "Tafelspitz, vollgar 90°C","",-1);
			}

			if (IPS_VariableProfileExists("WT.CoreTemp_Calf") == false){
				IPS_CreateVariableProfile("WT.CoreTemp_Calf", 3);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Calf", 0, "Kalbsbraten, vollgar 64-74°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Calf", 1, "Kalbsbrust, vollgar 75-78°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Calf", 2, "Kalbsrücken, hellrosa 65-70°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Calf", 3, "Kalbsschulter, vollgar 75-80°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Calf", 4, "Kuele/Oberschalte/Nuss, vollgar 78°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Calf", 5, "Nierenbraten, vollgar 75-80°C","",-1);
			}

			if (IPS_VariableProfileExists("WT.CoreTemp_Chicken") == false){
				IPS_CreateVariableProfile("WT.CoreTemp_Chicken", 3);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Chicken", 0, "Ente, vollgar 80-90°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Chicken", 1, "Gans, rosa 75-80°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Chicken", 2, "Gans, vollgar 90-92°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Chicken", 3, "Hähnchen, vollgar 80-90°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Chicken", 4, "Pute, vollgar 80-90°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Chicken", 5, "Strauss, vollgar 58°C","",-1);
			}

			if (IPS_VariableProfileExists("WT.CoreTemp_Venison") == false){
				IPS_CreateVariableProfile("WT.CoreTemp_Venison", 3);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Venison", 0, "Gespickter Rehrücken, vollgar 50-56°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Venison", 1, "Rehbraten, rosa 75-80°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Venison", 2, "Wildschweinbraten, vollgar 75-78°C","",-1);
			}

			if (IPS_VariableProfileExists("WT.CoreTemp_Lamb") == false){
				IPS_CreateVariableProfile("WT.CoreTemp_Lamb", 3);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 0, "Hammelrücken, leicht rosa 70-75°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 1, "Hammelrücken, vollgar 80°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 2, "Hammelkeule, leicht rosa 75-78°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 3, "Hammelkeule, vollgar 82-85°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 4, "Lamm, vollgar 79-85°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 5, "Lammkarree, rosa 55°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 6, "Lammkeule, rosa 60°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 7, "Lammkeule, vollgar 70-72°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 8, "Lammkoteletts, rosa 55°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 9, "Lammrücken, rosa 60-62°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Lamb", 10, "Lammrücken, vollgar 68°C","",-1);
			}

			if (IPS_VariableProfileExists("WT.CoreTemp_Fish") == false){
				IPS_CreateVariableProfile("WT.CoreTemp_Fish", 3);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Fish", 0, "Crevetten, 62°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Fish", 1, "Dorade, 65°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Fish", 2, "Hecht, 63°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Fish", 3, "Lachs, 60°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Fish", 4, "Mousse de Poisson, 65°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Fish", 5, "Seeteufel, 62°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Fish", 6, "Thunfisch, 62°C","",-1);
				IPS_SetVariableProfileAssociation("WT.CoreTemp_Fish", 7, "Zander, 62C","",-1);
			}


			//Fixed Variables

			$this->RegisterVariableBoolean('Active', $this->Translate('Active'),"~Switch");
			$this->RegisterVariableInteger('Battery', $this->Translate('Battery'),"~Battery.100");

			//In case of an update de-active module - otherwise status is not clear
			SetValue($this->GetIDForIdent("Active"), false);

			$this->EnableAction("Active");

			//Component sets timer, but default is OFF
			$this->RegisterTimer("WLAN BBQ Thermometer",0,"WT_CyclicTask(\$_IPS['TARGET']);");
					
		}
	
	public function ApplyChanges() {
			
		//Never delete this line!
		parent::ApplyChanges();

		$vpos = 50;
		$this->MaintainVariable('CoreTemp_Pork', $this->Translate('Core Temperature Pork'), vtString, 'WT.CoreTemp_Pork', $vpos++,$this->ReadPropertyBoolean('CoreTemp') == 1);
		$this->MaintainVariable('CoreTemp_Beef', $this->Translate('Core Temperature Beef'), vtString, 'WT.CoreTemp_Beef', $vpos++,$this->ReadPropertyBoolean('CoreTemp') == 1);
		$this->MaintainVariable('CoreTemp_Calf', $this->Translate('Core Temperature Calf'), vtString, 'WT.CoreTemp_Calf', $vpos++,$this->ReadPropertyBoolean('CoreTemp') == 1);
		$this->MaintainVariable('CoreTemp_Chicken', $this->Translate('Core Temperature Chicken'), vtString, 'WT.CoreTemp_Chicken', $vpos++,$this->ReadPropertyBoolean('CoreTemp') == 1);
		$this->MaintainVariable('CoreTemp_Venison', $this->Translate('Core Temperature Venison'), vtString, 'WT.CoreTemp_Venison', $vpos++,$this->ReadPropertyBoolean('CoreTemp') == 1);
		$this->MaintainVariable('CoreTemp_Lamb', $this->Translate('Core Temperature Lamb'), vtString, 'WT.CoreTemp_Lamb', $vpos++,$this->ReadPropertyBoolean('CoreTemp') == 1);
		$this->MaintainVariable('CoreTemp_Fish', $this->Translate('Core Temperature Fish'), vtString, 'WT.CoreTemp_Fish', $vpos++,$this->ReadPropertyBoolean('CoreTemp') == 1);

		if ($this->ReadPropertyBoolean('CoreTemp') == 1) {
			$this->EnableAction("CoreTemp_Pork");
			$this->EnableAction("CoreTemp_Beef");
			$this->EnableAction("CoreTemp_Calf");
			$this->EnableAction("CoreTemp_Chicken");
			$this->EnableAction("CoreTemp_Venison");
			$this->EnableAction("CoreTemp_Lamb");
			$this->EnableAction("CoreTemp_Fish");
		}

		$ActiveID= @IPS_GetObjectIDByIdent('Active', $this->InstanceID);	
		if (IPS_GetObject($ActiveID)['ObjectType'] == 2) {
				$this->RegisterMessage($ActiveID, VM_UPDATE);
		}

		$Channels = array(1,2,3,4,5,6);
		$vpos = 100;

		foreach ($Channels as $Channel) {
			$vpos = $vpos;
			$this->MaintainVariable('Channel'.$Channel.'_Temperature', $this->Translate('Channel ').$Channel.$this->Translate(' Current Temperature'), vtFloat, 'WT.BBQ_Temperature', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$this->MaintainVariable('Channel'.$Channel.'_LowerTarget', $this->Translate('Channel ').$Channel.$this->Translate(' Lower Target Temperature'), vtFloat, 'WT.BBQ_Temperature', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$this->MaintainVariable('Channel'.$Channel.'_HigherTarget', $this->Translate('Channel ').$Channel.$this->Translate(' Higher Target Temperature'), vtFloat, 'WT.BBQ_Temperature', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$this->MaintainVariable('Channel'.$Channel.'_Status', $this->Translate('Channel ').$Channel.$this->Translate(' Status'), vtInteger, 'WT.Channel_Status', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$vpos = 10 * ceil($vpos/10);

			$Channel_LowerTargetID = @IPS_GetObjectIDByIdent('Channel'.$Channel.'_LowerTarget', $this->InstanceID);	
			if (IPS_GetObject($Channel_LowerTargetID)['ObjectType'] == 2) {
					$this->RegisterMessage($Channel_LowerTargetID, VM_UPDATE);
			}

			$Channel_HigherTargetID = @IPS_GetObjectIDByIdent('Channel'.$Channel.'_HigherTarget', $this->InstanceID);
			if (IPS_GetObject($Channel_HigherTargetID)['ObjectType'] == 2) {
					$this->RegisterMessage($Channel_HigherTargetID, VM_UPDATE);
						
			}

			$ChannelActive = $this->ReadPropertyBoolean("Channel".$Channel."Active");
			if ($ChannelActive == 1) {
				//Add actions for Webfront when channel is active

				$this->EnableAction("Channel".$Channel."_LowerTarget");
				$this->EnableAction("Channel".$Channel."_HigherTarget");				
				
				//Add archiving if activated by channel
				$ArchiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
				$ArchiveTurnedOn = $this->ReadPropertyBoolean("ArchiveTurnedOn");
				if ($ArchiveTurnedOn == 1) {
					AC_SetLoggingStatus($ArchiveID, $this->GetIDForIdent("Channel".$Channel."_Temperature"), true);
					AC_SetAggregationType($ArchiveID, $this->GetIDForIdent("Channel".$Channel."_Temperature"), 0);
				}
			}
		}

		//$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		//$this->SetTimerInterval("WLAN BBQ Thermometer",$TimerMS);
					
	}

	public function CyclicTask() {

		$BatteryThreshold = $this->ReadPropertyInteger("System_BatteryThreshold");
		$SystemBatteryText = $this->ReadPropertyString("System_BatteryText");
		$System_AutoOff = $this->ReadPropertyInteger("System_AutoOff");
		$System_OffWarningText = $this->ReadPropertyString("System_OffWarningText");
		$System_OffText = $this->ReadPropertyString("System_OffText");
		$System_Messages = $this->ReadPropertyBoolean("System_Messages");

		$NotifyByApp = $this->ReadPropertyBoolean("NotifyByApp");
		$NotifyByEmail = $this->ReadPropertyBoolean("NotifyByEmail");

		$IP = $this->ReadPropertyString("IP");
		$Port = 80;
		$WaitTimeoutInSeconds = 1;

		//if($fp = @fsockopen($IP,$Port,$WaitTimeoutInSeconds)){
		$fp = @fsockopen($IP,$Port,$WaitTimeoutInSeconds);
		if (is_resource($fp)) {
			
			$curl = curl_init("http://".$IP."/data");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_TIMEOUT, 5);
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
			$json = curl_exec($curl);
			$data = json_decode($json);
			
			$Battery = $data->system->soc;
			SetValue($this->GetIDForIdent("Battery"),$Battery);

			if ($Battery < $BatteryThreshold) {
				$this->SetBuffer("NotifierMessage",$MessageBatteryText." ".$Battery."%");
				$Battery_WarningStatus = $this->GetBuffer("Battery_WarningStatus");

				if ($System_Messages == 1 AND $Battery_WarningStatus == 0) {
					if ($NotifyByApp == 1) {
						$this->SetBuffer("Battery_WarningStatus",1);
						$this->NotifyApp();
					}
					if ($NotifyByEmail == 1) {
						$this->SetBuffer("Battery_WarningStatus",1);
						$this->EmailApp();
					}
				}
			}
			else {
				$Battery_WarningStatus = 0;
			}

			$UnreachCounter = 0;
			$Unreach_WarningStatus = 0;
			$this->GetReadings();
		} 
		else {
			$this->SendDebug($this->Translate('System'),$this->Translate('Thermometer not reachable on IP ').$IP,0);
			
			//Starts a counter so the module can be switch off once automatic shutdown value is reached
			$UnreachCounter = $this->GetBuffer("UnreachCounter");		
			$this->SetBuffer("UnreachCounter",$UnreachCounter + 1);
			//echo $UnreachCounter;

			if ($UnreachCounter == round(($System_AutoOff / 2))) {
				//Nachricht
				$this->SetBuffer("NotifierMessage",$System_OffWarningText);
				if ($System_Messages == 1) {
					if ($System_Messages == 1) {
						if ($NotifyByApp == 1) {
							$this->NotifyApp();
						}
						if ($NotifyByEmail == 1) {
							$this->EmailApp();
						}
					}
				}
			}
			elseif ($UnreachCounter == $System_AutoOff) {
				//Nachricht + Aus
				SetValue($this->GetIDForIdent("Active"), false);
				
				$this->SetBuffer("NotifierMessage",$System_OffText);
				if ($NotifyByApp == 1) {
					$this->NotifyApp();
				}
				if ($NotifyByEmail == 1 AND $Unreach_WarningStatus == 1) {
					$this->EmailApp();
				}
			}

		} 
	}
		
	public function GetReadings() {

		$IP = $this->ReadPropertyString("IP");

		$NotifyByApp = $this->ReadPropertyBoolean("NotifyByApp");
		$NotifyByEmail = $this->ReadPropertyBoolean("NotifyByEmail");
		

		$MessageOK = $this->ReadPropertyBoolean("MessageOK");
		$MessageOKText = $this->ReadPropertyString("MessageOKText");
		$MessageWarmingup = $this->ReadPropertyBoolean("MessageWarmingup");
		$MessageWarmingupText = $this->ReadPropertyString("MessageWarmingupText");
		$MessageTooCold = $this->ReadPropertyBoolean("MessageTooCold");
		$MessageTooColdText = $this->ReadPropertyString("MessageTooColdText");
		$MessageTooHigh = $this->ReadPropertyBoolean("MessageTooHigh");
		$MessageTooHighText = $this->ReadPropertyString("MessageTooHighText");
				
		if ($IP != "") {
			
			$curl = curl_init("http://".$IP."/data");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_TIMEOUT, 5);
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

			$json = curl_exec($curl);
			$data = json_decode($json);

			$i = 0;
			$Channels = array(1,2,3,4,5,6);

			foreach ($Channels as $Channel) {

				$ChannelActive = $this->ReadPropertyBoolean("Channel".$Channel."Active");
				//$this->SendDebug(($this->Translate('Channel ').$Channel),$ChannelActive,0);
				if ($ChannelActive == 1) {
					$OldStatus = GetValue($this->GetIDForIdent("Channel".$Channel."_Status"));
					if (isset($data)) {
						$Temperature = $data->channel[$i]->temp;
						if ($Temperature != "999") {
							$this->SendDebug(($this->Translate('Channel ').$Channel),"Temperature ".$Temperature,0);
							SetValue($this->GetIDForIdent("Channel".$Channel."_Temperature"), $Temperature);
							$Temperature_Min = $data->channel[$i]->min;
							$this->SendDebug(($this->Translate('Channel ').$Channel),"Temperature Minimum ".$Temperature_Min,0);
							SetValue($this->GetIDForIdent("Channel".$Channel."_LowerTarget"), $Temperature_Min);
							$Temperature_Max = $data->channel[$i]->max;
							$this->SendDebug(($this->Translate('Channel ').$Channel),"Temperature Maximum ".$Temperature_Max,0);
							SetValue($this->GetIDForIdent("Channel".$Channel."_HigherTarget"), $Temperature_Max);
							$i++;

							//Actions

							if ($Temperature_Min> "0") {
								if ($Temperature < ($Temperature_Min * 0.8)) {
									$this->SendDebug(($this->Translate('Channel ').$Channel),"Status: Warming Up - Current Temperature ".$Temperature." C - Minimum Temperature ".$Temperature_Min." C - 1",0);
									SetValue($this->GetIDForIdent("Channel".$Channel."_Status"), 2);
									$NewStatus =  "2";
									if ($MessageWarmingup == 1) {
										$NotifierMessage = $MessageWarmingupText." Channel ".$Channel." - ".$Temperature."C";
									}
								}
								elseif (($Temperature < $Temperature_Min) AND ($Temperature > $Temperature_Min * 0.8)) {
									$this->SendDebug(($this->Translate('Channel ').$Channel),"Status: Too Cold - Current Temperature ".$Temperature." C - Minimum Temperature ".$Temperature_Min." C - 1",0);
									SetValue($this->GetIDForIdent("Channel".$Channel."_Status"), 3);
									$NewStatus =  "3";
									if ($MessageTooCold == 1) {
										$NotifierMessage = $MessageTooColdText." Channel ".$Channel." - ".$Temperature."C";
									}
								}
								elseif ($Temperature >= $Temperature_Min AND $Temperature < $Temperature_Max) {
									$this->SendDebug(($this->Translate('Channel ').$Channel),"Status: Heat OK - Current Temperature ".$Temperature." C - Minimum Temperature ".$Temperature_Min." C - 1",0);
									SetValue($this->GetIDForIdent("Channel".$Channel."_Status"), 1);
									$NewStatus =  "1";
									if ($MessageOK == 1) {
										$NotifierMessage = $MessageOKText." Channel ".$Channel." - ".$Temperature."C";
									}
								}
								elseif ($Temperature >= $Temperature_Max) {
									$this->SendDebug(($this->Translate('Channel ').$Channel),"Status: Too hot - Current Temperature ".$Temperature." C - Maximum Temperature ".$Temperature_Min." C - 1",0);
									SetValue($this->GetIDForIdent("Channel".$Channel."_Status"), 4);
									$NewStatus =  "4";
									if ($MessageTooHigh == 1) {
										$NotifierMessage = $MessageTooHighText." Channel ".$Channel." - ".$Temperature."C";
									}
								}
							}
							elseif ($Temperature < $Temperature_Max) {
								$this->SendDebug(($this->Translate('Channel ').$Channel),"Status: Heat OK - Current Temperature ".$Temperature." C - Maximum Temperature ".$Temperature_Min." C - 2",0);
								SetValue($this->GetIDForIdent("Channel".$Channel."_Status"), 1);
								$NewStatus =  "1";
								if ($MessageOK == 1) {
									$NotifierMessage = $MessageOKText." Channel ".$Channel." - ".$Temperature."C";
								}
							}
							elseif ($Temperature >= $Temperature_Max) {
								$this->SendDebug(($this->Translate('Channel ').$Channel),"Status: Too hot - Current Temperature ".$Temperature." C - Maximum Temperature ".$Temperature_Min." C - 3",0);
								SetValue($this->GetIDForIdent("Channel".$Channel."_Status"), 4);
								$NewStatus =  "4";
								if ($MessageTooHigh == 1) {
									$NotifierMessage = $MessageTooHighText." Channel ".$Channel." - ".$Temperature."C";
								}
							}
							else {

							}
						}
						else {
							SetValue($this->GetIDForIdent("Channel".$Channel."_Status"), 0);
							$NewStatus =  "0";
						}

						// Section where a noticiation is trigger if configured 

						// $this->SendDebug(($this->Translate('Channel ').$Channel),"Old ".$OldStatus." New ".$NewStatus,0);
						if (isset($OldStatus)) {
							if ($OldStatus != $NewStatus) {
								// check if message should be send
								$this->SendDebug(($this->Translate('Channel ').$Channel),"Status Changed - Check if message should be send",0);
								if (isset($NotifierMessage)) {
									$this->SetBuffer("NotifierMessage",$NotifierMessage);
									if ($NotifyByApp == 1) {
										$this->NotifyApp();
									}
									if ($NotifyByEmail == 1) {
										$this->EmailApp();
									}
								}
							}
							else {
								//do nothing
							}
						}
					}

				}	
			}
			
		}
		else {
			$this->SendDebug($this->Translate('WLAN BBQ Thermometer'),$this->Translate('No IP or Device Name configured'),0);
		}

	}

	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)	{
		
		//$this->SendDebug("Sender",$SenderID." ".$Message." ".$Data, 0);

		$IP = $this->ReadPropertyString("IP");
		$UnreachCounter = $this->GetBuffer("UnreachCounter");

		if ($SenderID == ($this->GetIDForIdent("Channel1_LowerTarget")) OR ($this->GetIDForIdent("Channel1_HigherTarget")) OR 
				($this->GetIDForIdent("Channel2_LowerTarget")) OR ($this->GetIDForIdent("Channel2_HigherTarget")) OR 
				($this->GetIDForIdent("Channel3_LowerTarget")) OR ($this->GetIDForIdent("Channel3_HigherTarget")) OR 
				($this->GetIDForIdent("Channel4_LowerTarget")) OR ($this->GetIDForIdent("Channel4_HigherTarget")) OR 
				($this->GetIDForIdent("Channel5_LowerTarget")) OR ($this->GetIDForIdent("Channel5_HigherTarget")) OR 
				($this->GetIDForIdent("Channel6_LowerTarget")) OR ($this->GetIDForIdent("Channel6_HigherTarget"))) {
			$SenderValue = GetValue($SenderID);
			$SenderName = IPS_GetName($SenderID);

			if (strpos($SenderName, '1')) {
				$Channel = "1";
			} elseif (strpos($SenderName, '2')) {
				$Channel = "2";
			} elseif (strpos($SenderName, '3')) {
				$Channel = "3";
			} elseif (strpos($SenderName, '4')) {
				$Channel = "4";
			} elseif (strpos($SenderName, '5')) {
				$Channel = "5";
			} elseif (strpos($SenderName, '6')) {
				$Channel = "6";
			} else {
			}

			if ($UnreachCounter == 0 AND isset($Channel) ) {

				//$this->SendDebug($this->Translate('Sender'),$SenderName,0);
				$set_channel = $Channel;
				$set_alarm = '0';

				if (strpos($SenderName, 'Lower') == True OR strpos($SenderName, 'Untere') == True) {
					$set_temp_min = $SenderValue;
					
					$data = array(
					'number' => $set_channel,
					'min' => $set_temp_min,
					//'max' => '',
					'alarm' => $set_alarm // 0 = off, 1 = push, 2 = buzzer, 3 = push + buzzer
					);
				} elseif (strpos($SenderName, 'Higher') == True OR strpos($SenderName, 'Obere') == True) {
					$set_temp_max = $SenderValue;

					$data = array(
					'number' => $set_channel,
					//'min' => '',
					'max' => $set_temp_max,
					'alarm' => $set_alarm // 0 = off, 1 = push, 2 = buzzer, 3 = push + buzzer
					);
				}
				
				$payload = json_encode($data);
				
				$ch = curl_init('http://'.$IP.'/setchannels');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLINFO_HEADER_OUT, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($payload))
				);
				
				$result = curl_exec($ch);
				curl_close($ch);
			}

		}
		
		if ($SenderID == $this->GetIDForIdent('Active')) {
			
			$SenderValue = GetValue($SenderID);
			if ($SenderValue == 1) {
				$this->SendDebug("System","Module activated", 0);
				$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
				$this->SetTimerInterval("WLAN BBQ Thermometer",$TimerMS);
				$this->SetBuffer("UnreachCounter",0);
				$this->GetReadings();
			}
			else {
				$this->SetTimerInterval("WLAN BBQ Thermometer", "0");
				$this->ArchiveCleaning();
				$this->UnsetValuesAtShutdown();
				$this->SendDebug("System","Switching module off", 0);
			}
		}
		else {
			
		}
		

	}

	public function NotifyApp() {
		$NotifierTitle = "BBG Thermometer";
		$NotifierMessage = $this->GetBuffer("NotifierMessage");
		if ($NotifierMessage == "") {
			$NotifierMessage = "Test Message";
		}
		$WebFrontMobile = IPS_GetInstanceListByModuleID('{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}')[0];
		$this->SendDebug("Notifier","********** App Notifier **********", 0);
		$this->SendDebug("Notifier","Message: ".$NotifierMessage." was sent", 0);			
		WFC_PushNotification($WebFrontMobile, $NotifierTitle, $NotifierMessage , "", 0);
	}

	public function EmailApp() {
		$EmailVariable = $this->ReadPropertyInteger("EmailVariable"); 
		if ($EmailVariable != "") {	
			$NotifierMessage = $this->GetBuffer("NotifierMessage");
			$EmailTitle = "BBG Thermometer";
			if ($NotifierMessage == "") {
				$NotifierMessage = "Test Message";
			}
			$this->SendDebug("Email","********** Email **********", 0);
			$this->SendDebug("Email","Message: ".$NotifierMessage." was sent", 0);			
			SMTP_SendMail($EmailVariable, $EmailTitle, $NotifierMessage);
		}
		else {
			echo $this->Translate('Email Instance is not configured');
		}
	}

	public function ArchiveCleaning() {

		$ArchiveTurnedOn = $this->ReadPropertyBoolean("ArchiveTurnedOn");
		if ($ArchiveTurnedOn == 1) {

			$Channels = array(1,2,3,4,5,6);

			foreach ($Channels as $Channel) {
				$ArchiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
				AC_DeleteVariableData ($ArchiveID, $this->GetIDForIdent("Channel".$Channel."_Temperature"), 0, 0);

				$ChannelActive = $this->ReadPropertyBoolean("Channel".$Channel."Active");
			
				if ($ChannelActive == 1) {
						$ArchiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
						$ArchiveTurnedOn = $this->ReadPropertyBoolean("ArchiveTurnedOn");
						if ($ArchiveTurnedOn == 1) {
							AC_SetLoggingStatus($ArchiveID, $this->GetIDForIdent("Channel".$Channel."_Temperature"), true);
							AC_SetAggregationType($ArchiveID, $this->GetIDForIdent("Channel".$Channel."_Temperature"), 0);
						}
				}
			}
		}		
	}

	public function UnsetValuesAtShutdown() {

		$this->SendDebug(($this->Translate('System')),"Reseting all values to 0 since system is off",0);

		$Channels = array(1,2,3,4,5,6);

		foreach ($Channels as $Channel) {
			SetValue($this->GetIDForIdent("Channel".$Channel."_Temperature"), 0);
			SetValue($this->GetIDForIdent("Channel".$Channel."_LowerTarget"), 0);
			SetValue($this->GetIDForIdent("Channel".$Channel."_HigherTarget"), 0);
			SetValue($this->GetIDForIdent("Channel".$Channel."_Status"), 0);
			$this->SetBuffer("UnreachCounter",0);
		}
	}

	public function RequestAction($Ident, $Value) {
		
		$this->SetValue($Ident, $Value);
		
	}

}
