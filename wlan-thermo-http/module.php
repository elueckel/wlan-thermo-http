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
			$this->RegisterPropertyString("UserName","");
			$this->RegisterPropertyString("Password","");
			$this->RegisterPropertyInteger("Timer", 0);
			
			$this->RegisterPropertyBoolean("Channel1Active", 0);
			$this->RegisterPropertyBoolean("Channel2Active", 0);
			$this->RegisterPropertyBoolean("Channel3Active", 0);
			$this->RegisterPropertyBoolean("Channel4Active", 0);
			$this->RegisterPropertyBoolean("Channel5Active", 0);
			$this->RegisterPropertyBoolean("Channel6Active", 0);
			
			

			if (IPS_VariableProfileExists("WT.Channel_Status") == false){
					IPS_CreateVariableProfile("WT.Channel_Status", 2);
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
			$this->RegisterTimer("WLAN BBQ Thermometer",0,"WT_GetReadings(\$_IPS['TARGET']);");
					
		}
	
	public function ApplyChanges() {
			
		//Never delete this line!
		parent::ApplyChanges();

		$vpos = 10;
		$this->MaintainVariable('WT_SOC', $this->Translate('Batterie Charge'), vtInteger, "~Battery.100", $vpos++, $this->ReadPropertyBoolean("System_Data") == 1);

		$vpos = 100;
		$this->MaintainVariable('Channel1_Temperature', $this->Translate('Channel 1 Current Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyBoolean("Channel1Active") == 1);
		$this->MaintainVariable('Channel1_LowerTarget', $this->Translate('Channel 1 Lower Target Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyBoolean("Channel1Active") == 1);
		$this->MaintainVariable('Channel1_HigherTarget', $this->Translate('Channel 1 Higher Target Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyBoolean("Channel1Active") == 1);
		$this->MaintainVariable('Channel1_Status', $this->Translate('Channel 1 Status'), vtFloat, "WT.Channel_Status", $vpos++, $this->ReadPropertyBoolean("Channel1Active") == 1);
		
		
		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("WLAN BBQ Thermometer",$TimerMS);
					
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

	public function ProcessReadings() {

		$Readings = $this->GetBuffer("Readings");
		
		$i = 1;
		$channels = array(1,2,3,4,5,6);

		foreach ($channels as $channel) {

			$ChannelActive = $this->ReadPropertyBoolean("Channel".$channel."Active");
			$this->SendDebug(($this->Translate('Channel ').$channel),$ChannelActive,0);



		}

/*

		$Channel1Active = $this->ReadPropertyBoolean("Channel1Active");
		$Channel2Active = $this->ReadPropertyBoolean("Channel2Active");
		$Channel3Active = $this->ReadPropertyBoolean("Channel3Active");
		$Channel4Active = $this->ReadPropertyBoolean("Channel4Active");
		$Channel5Active = $this->ReadPropertyBoolean("Channel5Active");
		$Channel6Active = $this->ReadPropertyBoolean("Channel6Active");

		if ($Channel1Active == 1) {
			$Channel1Temperature = $Readings->channel[0]->temp;
			$Channel1LowerTarget = $Readings->channel[0]->min;
			$Channel1HigherTarget = $Readings->channel[0]->max;
			
			if (isset($Channel1Temperature)) {
				SetValue($this->GetIDForIdent($i.'Pet_LastDetectedBy'), $Pet_LastDetectedByName);
			}
			

		}
*/




	}


}
