<?php

namespace Parser;

class VatsimParser {

    /**
     *  Users uses this information to get what they need, parsed to JSON
     */
    public $data;
    protected $parsedData;
    protected $apiData;

    protected $cacheFile;
    protected $debugInfo;

    /**
     *  Construct function
     */
    public function __construct($debug = false) {
        $this->cacheFile = 'buffer/api.data';
        $this->debug = $debug;
        self::GetApiData();
        $this->parsedData->debug = $this->debugInfo;

        $this->data = json_encode($this->parsedData);
    }

    /**
     *  Gets data from master server
     */
    private function GetApiData($apiUrl = 'http://cluster.data.vatsim.net/vatsim-data.txt') {
        $this->apiData = file_get_contents($apiUrl);
        file_put_contents($this->cacheFile, $this->apiData);
        
        $this->parsedData->airports = self::GetAirports();
        $this->parsedData->connected_users = self::GetConnectedUsers();
        $this->parsedData->unique_users = self::GetUniqueUsers();
        $this->parsedData->users = self::GetUsers();
        $this->parsedData->vatsim_servers = self::GetVatsimServers();
    }

    /**
     * Parse airports
     */
    private function GetAirports() {
        $this->debugInfo->regex->GetAirports->return_type = 'Array of Objects';
        $this->debugInfo->regex->GetAirports->regex = null;
        
        $airportsFile = file_get_contents("buffer/airports.dat");        
        $airports = array();

        foreach(explode("\n", $airportsFile) as $a) {
            $a = explode(',', $a);

            $airport = new \StdClass();
            $airport->i = $a[0];
            $airport->name = str_replace('"', '', $a[1]);
            $airport->city = str_replace('"', '', $a[2]);
            $airport->country = str_replace('"', '', $a[3]);
            $airport->icao = str_replace('"', '', $a[4]);
            $airport->iata = str_replace('"', '', $a[5]);
            $airport->latitude = $a[6];
            $airport->longitude = $a[7];

            array_push($airports, $airport);
        }

        return $airports;
    }

    /**
     *  Parse connected user count
     * @return [Integer] $connectedUsers
     */
    private function GetConnectedUsers() {
        $pattern = '/CONNECTED CLIENTS = (\d+)/';
        $this->debugInfo->regex->GetConnectedUsers->return_type = 'Integer';
        $this->debugInfo->regex->GetConnectedUsers->regex = $pattern;

        preg_match($pattern, $this->apiData, $connectedUsers);
        return $connectedUsers[1] + 0;
    }

    /**
     *  Parse unique user count
     * @return [Integer] $uniqueUsers
     */
    private function GetUniqueUsers() {
        $pattern = '/UNIQUE USERS = (\d+)/';
        $this->debugInfo->regex->GetUniqueUsers->return_type = 'Integer';
        $this->debugInfo->regex->GetUniqueUsers->regex = $pattern;

        preg_match($pattern, $this->apiData, $uniqueUsers);
        return $uniqueUsers[1] + 0;
    }

    /**
     *  Parse all connected users
     * @return [Array] $users
     */
    private function GetUsers() {
        $pattern = '/(?<=!CLIENTS:\n)(.*)(?=!SERVERS:)/s';
        $this->debugInfo->regex->GetUsers->return_type = 'Array of Objects';
        $this->debugInfo->regex->GetUsers->regex = $pattern;

        $users = array();

        preg_match($pattern, $this->apiData, $connectedUsers);
        $connectedUsers = explode(PHP_EOL, $connectedUsers[0]);

        foreach($connectedUsers as $user){
            $user = explode(":", $user);
            $connectedUser = new \StdClass();
            
            $connectedUser->callsign                    = $user[0];
            $connectedUser->cid                         = $user[1];
            $connectedUser->realname                    = $user[2];
            $connectedUser->clienttype                  = $user[3];
            $connectedUser->frequency                   = $user[4];
            $connectedUser->latitude                    = $user[5];
            $connectedUser->longitude                   = $user[6];
            $connectedUser->altitude                    = $user[7];
            $connectedUser->groundspeed                 = $user[8];
            $connectedUser->planned_aircraft            = $user[9];
            $connectedUser->planned_tascruise           = $user[10];
            $connectedUser->planned_depairport          = $user[11];
            $connectedUser->planned_altitude            = $user[12];
            $connectedUser->planned_destairport         = $user[13];
            $connectedUser->server                      = $user[14];
            $connectedUser->protrevision                = $user[15];
            $connectedUser->rating                      = $user[16];
            $connectedUser->transponder                 = $user[17];
            $connectedUser->facilitytype                = $user[18];
            $connectedUser->visualrange                 = $user[19];
            $connectedUser->planned_revision            = $user[20];
            $connectedUser->planned_flighttype          = $user[21];
            $connectedUser->planned_deptime             = $user[22];
            $connectedUser->planned_actdeptime          = $user[23];
            $connectedUser->planned_hrsenroute          = $user[24];
            $connectedUser->planned_minenroute          = $user[25];
            $connectedUser->planned_hrsfuel             = $user[26];
            $connectedUser->planned_minfuel             = $user[27];
            $connectedUser->planned_altairport          = $user[28];
            $connectedUser->planned_remarks             = $user[29];
            $connectedUser->planned_route               = $user[30];
            $connectedUser->planned_depairport_lat      = $user[31];
            $connectedUser->planned_depairport_lon      = $user[32];
            $connectedUser->planned_destairport_lat     = $user[33];
            $connectedUser->planned_destairport_lon     = $user[34];
            $connectedUser->atis_message                = $user[35];
            $connectedUser->time_last_atis_received     = $user[36];
            $connectedUser->time_logon                  = $user[37];
            $connectedUser->heading                     = $user[38];
            $connectedUser->iconHeading                 = round(360 - $user[38], -1);
            $connectedUser->QNH_iHg                     = $user[39];
            $connectedUser->QNH_Mb                      = $user[40];
            
            if($connectedUser->cid !== null)
                array_push($users, $connectedUser);
        }

        return $users;
    }

    /**
     * parse Vatsim Servers
     * @return [Array] $vatsimServers
     */
    private function GetVatsimServers() {
        $pattern = '/(?<=!SERVERS:\n)(.*)(?=!PREFILE:)/s';
        $this->debugInfo->regex->GetVatsimServers->return_type = 'Array of Objects';
        $this->debugInfo->regex->GetVatsimServers->regex = $pattern;

        $vatsimServers = array();

        preg_match($pattern, $this->apiData, $vatsimServersMatches);
        $servers = explode(PHP_EOL, $vatsimServersMatches[0]);

        foreach($servers as $vatsimserver){
            $vatsimserver = explode(":", $vatsimserver);
            $server = new \StdClass();

            $server->code = $vatsimserver[0];
            $server->ip_address = $vatsimserver[1];
            $server->location = $vatsimserver[2];
            $server->instance = $vatsimserver[4];

            if(!empty($server->ip_address))
                array_push($vatsimServers, $server);
        }

        return $vatsimServers;
    }

}