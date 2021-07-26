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
			}

			//Fixed Variables

			$this->RegisterVariableBoolean('Active', $this->Translate('Active'),"~Switch");
			$this->RegisterVariableInteger('Battery', $this->Translate('Battery'),"~Battery.100");

			//In case of an update de-active module - otherwise status is not clear
			SetValue($this->GetIDForIdent("Active"), false);



			//Component sets timer, but default is OFF
			$this->RegisterTimer("WLAN BBQ Thermometer",0,"WT_CyclicTask(\$_IPS['TARGET']);");
					
		}
	
	public function ApplyChanges() {
			
		//Never delete this line!
		parent::ApplyChanges();

		$ActiveID= @IPS_GetObjectIDByIdent('Active', $this->InstanceID);	
		if (IPS_GetObject($ActiveID)['ObjectType'] == 2) {
				$this->RegisterMessage($ActiveID, VM_UPDATE);
		}

		$Channels = array(1,2,3,4,5,6);
		$vpos = 100;

		foreach ($Channels as $Channel) {
			$vpos = $vpos;
			$this->MaintainVariable('Channel'.$Channel.'_Temperature', $this->Translate('Channel '.$Channel.' Current Temperature'), vtFloat, 'WT.BBQ_Temperature', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$this->MaintainVariable('Channel'.$Channel.'_LowerTarget', $this->Translate('Channel '.$Channel.' Lower Target Temperature'), vtFloat, 'WT.BBQ_Temperature', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$this->MaintainVariable('Channel'.$Channel.'_HigherTarget', $this->Translate('Channel '.$Channel.' Higher Target Temperature'), vtFloat, 'WT.BBQ_Temperature', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$this->MaintainVariable('Channel'.$Channel.'_Status', $this->Translate('Channel '.$Channel.' Status'), vtInteger, 'WT.Channel_Status', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$vpos = 10 * ceil($vpos/10);

			$Channel_LowerTargetID= @IPS_GetObjectIDByIdent('Channel'.$Channel.'_LowerTarget', $this->InstanceID);	
			if (IPS_GetObject($Channel_LowerTargetID)['ObjectType'] == 2) {
					$this->RegisterMessage($Channel_LowerTargetID, VM_UPDATE);
			}

			$Channel_HigherTargetID= @IPS_GetObjectIDByIdent('Channel'.$Channel.'_HigherTarget', $this->InstanceID);
			if (IPS_GetObject($Channel_HigherTargetID)['ObjectType'] == 2) {
					$this->RegisterMessage($Channel_HigherTargetID, VM_UPDATE);
						
			}

			$ChannelActive = $this->ReadPropertyBoolean("Channel".$Channel."Active");
			if ($ChannelActive == 1) {
				//Add actions for Webfront when channel is active

				$this->EnableAction('Channel'.$Channel.'_LowerTarget');
				$this->EnableAction('Channel'.$Channel.'_HigherTarget');
				
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
		$SystemBatteryText = $this->ReadPropertyString("System_SystemBatteryText");
		$System_AutoOff = $this->ReadPropertyInteger("System_AutoOff");
		$System_OffWarningText = $this->ReadPropertyString("System_OffWarningText");
		$System_OffText = $this->ReadPropertyString("System_OffText");
		$System_Messages = $this->ReadPropertyBoolean("System_Messages");

		$NotifyByApp = $this->ReadPropertyBoolean("NotifyByApp");
		$NotifyByEmail = $this->ReadPropertyBoolean("NotifyByEmail");

		$IP = $this->ReadPropertyString("IP");
		$Port = 80;
		$WaitTimeoutInSeconds = 1;

		if($fp = fsockopen($IP,$Port,$WaitTimeoutInSeconds)){
			
			$curl = curl_init("http://".$IP."/data");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_TIMEOUT, 5);
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
			$json = curl_exec($curl);
			$data = json_decode($json);
			
			$Battery = $data->system->soc;
			SetValue($this->GetIDForIdent("Battery",$Battery));

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

			if ($UnreachCounter >= round(($System_AutoOff / 2))) {
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
		fclose($fp);

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
			//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
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
			echo 'Login data is missing';
		}

	}

	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)	{
		
		//$this->SendDebug("Sender",$SenderID." ".$Message." ".$Data, 0);

		$IP = $this->ReadPropertyString("IP");

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

			if (strpos($SenderName, 'Lower')) {
				$set_temp_min = $SenderValue;
			} elseif (strpos($SenderName, 'Higher')) {
				$set_temp_max = $SenderValue;
			}

			$set_channel = $Channel;
			//$set_temp_max = '40';
			//$set_temp_min = $SenderValue;
			$set_alarm = '0';

			$data = array(
				'number' => $set_channel,
				'max' => $set_temp_max,
				'min' => $set_temp_min,
				'alarm' => $set_alarm // 0 = off, 1 = push, 2 = buzzer, 3 = push + buzzer
			);
			
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
		
		if ($SenderID == $this->GetIDForIdent('Active')) {
			
			$SenderValue = GetValue($SenderID);
			if ($SenderValue == 1) {
				$this->SendDebug("System","Module activated", 0);
				$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
				$this->SetTimerInterval("WLAN BBQ Thermometer",$TimerMS);
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

	public function UnsetValuesAtShutdown() {

		$Channels = array(1,2,3,4,5,6);

		foreach ($Channels as $Channel) {
			$this->SendDebug(($this->Translate('Channel ').$Channel),"Temperature ".$Temperature,0);
			SetValue($this->GetIDForIdent("Channel".$Channel."_Temperature"), 0);
			SetValue($this->GetIDForIdent("Channel".$Channel."_LowerTarget"), 0);
			SetValue($this->GetIDForIdent("Channel".$Channel."_HigherTarget"), 0);
			SetValue($this->GetIDForIdent("Channel".$Channel."_Status"), 0);

		}
	}

}
