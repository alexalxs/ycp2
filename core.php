<?php
require 'bases/browser/DetectorInterface.php';
require 'bases/browser/UserAgent.php';
require 'bases/browser/Os.php';
require 'bases/browser/OsDetector.php';
require 'bases/browser/AcceptLanguage.php';
require 'bases/browser/Language.php';
require 'bases/browser/LanguageDetector.php';
require 'bases/iputils.php';
require 'bases/ipcountry.php';
use Sinergi\BrowserDetector\Os;
use Sinergi\BrowserDetector\Language;

class Cloaker{
	var $os_white;
	var $country_white;
	var $lang_white;
	var $detect;
	var $tokens_black;
	var $url_should_contain;
	var $ua_black;
	var $ip_black_filename;
	var $ip_black_cidr;
	var $block_without_referer;
	var $referer_stopwords;
    var $block_vpnandtor;
    var $isp_black;
    var $result=[];
    var $whitelist_match = false;

	public function __construct($os_white,$country_white,$lang_white,$ip_black_filename,$ip_black_cidr,$tokens_black,$url_should_contain,$ua_black,$isp_black,$block_without_referer,$referer_stopwords,$block_vpnandtor){
		$this->os_white = $os_white;
		$this->country_white = $country_white;
		$this->lang_white=$lang_white;
		$this->ip_black_filename = $ip_black_filename;
        $this->ip_black_cidr = $ip_black_cidr;
		$this->tokens_black = $tokens_black;
		$this->url_should_contain= $url_should_contain;
		$this->ua_black = $ua_black;
		$this->isp_black = $isp_black;
		$this->block_without_referer = $block_without_referer;
		$this->referer_stopwords = $referer_stopwords;
		$this->block_vpnandtor = $block_vpnandtor;
		$this->detect();
	}

	public function detect(){
		$a['os']='Unknown';
		$a['country']='Unknown';
		$a['language']='Unknown';
		if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])){
			$a['referer']=$_SERVER['HTTP_REFERER'];
		}
		else if (isset($_COOKIE['referer']) && !empty($_COOKIE['referer']))
        {
			$a['referer']=$_COOKIE['referer'];
        }
		else{
			$a['referer']='';
		}

		$lang=new Language();
		$a['lang']=$lang->getLanguage();
		$os = new Os();
	    $a['os']=$os->getName();
		$a['ip'] = getip();
		$a['ua']=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'Not Found!';
		$a['country'] = getcountry($a['ip']);
		$a['isp'] = getisp($a['ip']);
		$this->detect = new stdClass();
$this->detect->os = $a['os'];
$this->detect->country = $a['country'];
$this->detect->language = $a['language'];
$this->detect->referer = $a['referer'];
$this->detect->lang = $a['lang'];
$this->detect->ip = $a['ip'];
$this->detect->ua = $a['ua'];
$this->detect->isp = $a['isp'];
	}

	private function blackbox($ip){
        $url = 'https://blackbox.ipinfo.app/lookup/';
        $res = file_get_contents($url . $ip);

        if(!is_string($res) || !strpos($http_response_header[0], "200")){
			return false;
        }

        if($res !== null && $res === 'Y'){
            return true;
        }

        return false;
    }

	public function check(){
		$result = 0;
		$current_ip = $this->detect->ip;
        
        // Debug para identificar o IP do cliente
        error_log("Verificando IP cliente: {$current_ip}");
        
        // Verificar primeiro se o IP está na whitelist
        if (file_exists(__DIR__."/bases/whitelist.txt")) {
            $whitelist_ips = file(__DIR__."/bases/whitelist.txt", FILE_IGNORE_NEW_LINES);
            // Filtrar comentários e linhas vazias
            $whitelist_ips = array_filter($whitelist_ips, function($line) {
                return trim($line) !== '' && substr(trim($line), 0, 1) !== '#';
            });
            
            if (!empty($whitelist_ips)) {
                $this->whitelist_match = IpUtils::checkIp($current_ip, $whitelist_ips);
                error_log("Whitelist verificada para IP {$current_ip}: " . ($this->whitelist_match ? "MATCH" : "NO MATCH"));
                
                if ($this->whitelist_match) {
                    error_log("IP {$current_ip} está na whitelist, enviando para black page diretamente");
                    $this->result[] = 'whitelist';
                    return 0; // Retorna 0 para direcionar para black page
                }
            } else {
                error_log("Arquivo whitelist.txt está vazio ou contém apenas comentários");
            }
        } else {
            error_log("Arquivo whitelist.txt não encontrado");
        }
        
        // Continua com as verificações normais
        $cidr = file(__DIR__."/bases/bots.txt", FILE_IGNORE_NEW_LINES);
		$checked = IpUtils::checkIp($current_ip, $cidr);

		if ($checked === true) {
            $result = 1;
			$this->result[] = 'ipbase';
            error_log("IP {$current_ip} identificado como bot");
        }

		if(!$checked &&
		   !empty($this->ip_black_filename) &&
		   file_exists(__DIR__."/bases/".$this->ip_black_filename)===true)
		{
			$ip_black_checker=false;
			$custom_base_path=__DIR__."/bases/".$this->ip_black_filename;
			if ($this->ip_black_cidr){
                $cbf = file($custom_base_path, FILE_IGNORE_NEW_LINES);
                $ip_black_checker=IpUtils::checkIp($current_ip, $cbf);
            }
			else{
                if(strpos(file_get_contents($custom_base_path),$current_ip) !== false) {
                    $ip_black_checker=true;
                }
            }

			if($ip_black_checker===true){
				$result=1;
				$this->result[]='ipblack';
			}
		}

		if ($this->block_vpnandtor){
            if ($this->blackbox($current_ip)===true){
				$result=1;
				$this->result[]='vnp&tor';
            }
        }

		if($this->ua_black!=[])
		{
			$ua=$this->detect->ua;
			foreach($this->ua_black as $ua_black_single){
				if(!empty(stristr($ua,$ua_black_single))){
					$result=1;
					$this->result[]='ua';
				}
			}
		}

		$os_white_checker = in_array($this->detect->os,$this->os_white);
		if(!empty($this->os_white) && $os_white_checker===false){
			$result=1;
			$this->result[]='os';
		}

		$country_white_checker = in_array($this->detect->country,$this->country_white);
		if($this->country_white!=[] &&
			in_array('WW',$this->country_white)===false &&
			$country_white_checker===false){
			$result=1;
			$this->result[]='country';
		}

		$lang_white_checker = in_array($this->detect->lang,$this->lang_white);
		if($this->lang_white!==[] &&
			in_array('any',$this->lang_white)===false &&
			$lang_white_checker===false){
			$result=1;
			$buf=strtoupper($this->detect->lang);
			$this->result[]='language:'.$buf;
		}

		if($this->block_without_referer===true &&$this->detect->referer===''){
			$result=1;
			$this->result[]='referer';
		}

		if($this->referer_stopwords!==[] &&$this->detect->referer!==''){
			foreach($this->referer_stopwords AS $stop){
				if ($stop==='')continue;
				if (stripos($this->detect['referer'],$stop)!==false){
					$result=1;
					$this->result[]='refstop:'.$stop;
					break;
				}
			}
		}

		if($this->tokens_black!==[]){
			foreach($this->tokens_black AS $token){
				if ($token==='')continue;
				if (strpos($_SERVER['REQUEST_URI'],$token)!==false){
					$result=1;
					$this->result[]='token:'.$token;
					break;
				}
			}
		}

		if($this->url_should_contain!==[]){
			foreach($this->url_should_contain AS $should){
				if ($should==='') continue;
				if (strpos($_SERVER['REQUEST_URI'],$should)===false){
					$result=1;
					$this->result[]='url:'.$should;
					break;
				}
			}
		}

		if(!empty($this->isp_black))
		{
			$isp=$this->detect->isp;
			foreach($this->isp_black as $isp_black_single){
				if(!empty(stristr($isp,$isp_black_single))){
					$result=1;
					$this->result[]='isp';
				}
			}
		}

		return $result;
	}

}
?>
