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
			
			$this->RegisterPropertyBoolean("Channel1", 0);
			$this->RegisterPropertyBoolean("Channel2", 0);
			$this->RegisterPropertyBoolean("Channel3", 0);
			$this->RegisterPropertyBoolean("Channel4", 0);
			$this->RegisterPropertyBoolean("Channel5", 0);
			$this->RegisterPropertyBoolean("Channel6", 0);
			
			

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
		$this->MaintainVariable('Channel1_Temperature', $this->Translate('Channel 1 Current Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyBoolean("Channel1") == 1);
		$this->MaintainVariable('Channel1_LowerTarget', $this->Translate('Channel 1 Lower Target Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyBoolean("Channel1") == 1);
		$this->MaintainVariable('Channel1_HigherTarget', $this->Translate('Channel 1 Higher Target Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyBoolean("Channel1") == 1);
		$this->MaintainVariable('Channel1_Status', $this->Translate('Channel 1 Status'), vtFloat, "WT.Channel_Status", $vpos++, $this->ReadPropertyBoolean("Channel1") == 1);
		
		
		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("WLAN BBQ Thermometer",$TimerMS);
					
	}
		
	public function GetReadings() {

		$IP = $this->ReadPropertyString("IP");
				
		if ($IP != "") {
			
			$ch = curl_init("http://".$IP."/data");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json)));
			$result = json_decode(curl_exec($ch),true) or die("WLAN Thermo no reachable\n");
			var_dump($result);
			
		}
		else {
			$this->SendDebug($this->Translate('WLAN BBQ Thermometer'),$this->Translate('No IP or Device Name configured'),0);
			echo 'Login data is missing';
		}

	}


}
