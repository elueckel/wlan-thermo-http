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
			$this->RegisterPropertyBoolean("System_Data", 0);
			$this->RegisterPropertyInteger("Timer", 0);
			
			$this->RegisterPropertyBoolean("Channel1Active", 0);
			$this->RegisterPropertyBoolean("Channel2Active", 0);
			$this->RegisterPropertyBoolean("Channel3Active", 0);
			$this->RegisterPropertyBoolean("Channel4Active", 0);
			$this->RegisterPropertyBoolean("Channel5Active", 0);
			$this->RegisterPropertyBoolean("Channel6Active", 0);
			$this->RegisterPropertyBoolean("ArchiveTurnedOn", 0);
			$this->RegisterPropertyBoolean("ArchiveDumpTemperature", 0);
			
			$this->RegisterPropertyInteger("EmailVariable", 0);
			$this->RegisterPropertyInteger("WebfrontVariable", 0);
			

			if (IPS_VariableProfileExists("WT.Channel_Status") == false){
					IPS_CreateVariableProfile("WT.Channel_Status", 1);
					IPS_SetVariableProfileValues("WT.Channel_Status", 0, 0, 1);
					IPS_SetVariableProfileDigits("WT.Channel_Status", 1);
					IPS_SetVariableProfileIcon("WT.Channel_Status",  "WindSpeed");
					IPS_SetVariableProfileAssociation("WT.Channel_Status", 0, "Not Found", "",-1);
					IPS_SetVariableProfileAssociation("WT.Channel_Status", 1, "OK","",-1);
					IPS_SetVariableProfileAssociation("WT.Channel_Status", 2, "Warming Up","",-1);
					IPS_SetVariableProfileAssociation("WT.Channel_Status", 3, "Too Cold","",-1);
					IPS_SetVariableProfileAssociation("WT.Channel_Status", 4, "Too Hot","",-1);
				}



			//Component sets timer, but default is OFF
			$this->RegisterTimer("WLAN BBQ Thermometer",0,"WT_CyclicTask(\$_IPS['TARGET']);");
					
		}
	
	public function ApplyChanges() {
			
		//Never delete this line!
		parent::ApplyChanges();

		$vpos = 10;
		$this->MaintainVariable('WT_SOC', $this->Translate('Batterie Charge'), vtInteger, "~Battery.100", $vpos++, $this->ReadPropertyBoolean("System_Data") == 1);

		$Channels = array(1,2,3,4,5,6);
		$vpos = 100;

		foreach ($Channels as $Channel) {
			$vpos = $vpos;
			$this->MaintainVariable('Channel'.$Channel.'_Temperature', $this->Translate('Channel '.$Channel.' Current Temperature'), vtFloat, '~Temperature', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$this->MaintainVariable('Channel'.$Channel.'_LowerTarget', $this->Translate('Channel '.$Channel.' Lower Target Temperature'), vtFloat, '~Temperature', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
			$this->MaintainVariable('Channel'.$Channel.'_HigherTarget', $this->Translate('Channel '.$Channel.' Higher Target Temperature'), vtFloat, '~Temperature', $vpos++, $this->ReadPropertyBoolean('Channel'.$Channel.'Active') == 1);
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


		}

		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("WLAN BBQ Thermometer",$TimerMS);
					
	}

	public function CyclicTask() {
		// This function will run all required modules

		//Ping to see if WLAN Thermometer is there




	}


		
	public function GetReadings() {

		$IP = $this->ReadPropertyString("IP");
				
		if ($IP != "") {
			
			$curl = curl_init("http://".$IP."/data");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_TIMEOUT, 5);
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

			$json = curl_exec($curl);
			$data = json_decode($json);

			$i = 0;
			$Channels = array(1,2,3,4,5,6);

			foreach ($Channels as $Channel) {

				$ChannelActive = $this->ReadPropertyBoolean("Channel".$Channel."Active");
				$this->SendDebug(($this->Translate('Channel ').$Channel),$ChannelActive,0);
				if ($ChannelActive == 1) {
					$Temperature = $data->channel[$i]->temp;
					$this->SendDebug(($this->Translate('Channel ').$Channel),"Temperature ".$Temperature,0);
					SetValue($this->GetIDForIdent("Channel".$Channel."_Temperature"), $Temperature);
					$Temperature_Min = $data->channel[$i]->min;
					$this->SendDebug(($this->Translate('Channel ').$Channel),"Temperature Minimum ".$Temperature_Min,0);
					SetValue($this->GetIDForIdent("Channel".$Channel."_LowerTarget"), $Temperature_Min);
					$Temperature_Max = $data->channel[$i]->max;
					$this->SendDebug(($this->Translate('Channel ').$Channel),"Temperature Maximum ".$Temperature_Max,0);
					SetValue($this->GetIDForIdent("Channel".$Channel."_HigherTarget"), $Temperature_Max);
					$i++;
				}

			}
						
		}
		else {
			$this->SendDebug($this->Translate('WLAN BBQ Thermometer'),$this->Translate('No IP or Device Name configured'),0);
			echo 'Login data is missing';
		}

	}

	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)	{
			//echo $SenderId." ".$Data;
			//$this->SetResetTimerInterval();
			$IP = $this->ReadPropertyString("IP");

			$this->SendDebug($this->Translate('Variable Update'),$SenderID." Message ".$Message." Data ".$Data,0);



			if ($SenderID == ($this->GetIDForIdent("Channel1_LowerTarget")) OR ($this->GetIDForIdent("Channel1_HigherTarget"))) {
				$this->SendDebug($this->Translate('Variable Update')," Match Lower ".$Data,0);
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

			elseif ($SenderID == $this->GetIDForIdent("ManualBlock")) {

			}

			else {
				//nix
			}

		}





	public function NotifyApp() {
		$NotifierTitle = $this->GetBuffer("NotifierTitle");
		$NotifierMessage = $this->GetBuffer("NotifierMessage");
		$WebFrontMobile = IPS_GetInstanceListByModuleID('{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}')[0];
		// to send notifications
		$this->SendDebug("Notifier","********** App Notifier **********", 0);
		$this->SendDebug("Notifier","Message: ".$NotifierMessage." was sent", 0);			
		WFC_PushNotification($WebFrontMobile, $NotifierTitle, $NotifierMessage , "", 0);
	}

	public function ArchiveCleaning() {
		
	}



}
