<?php
//Включение отладочной информации
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//Конец включения отладочной информации
date_default_timezone_set('UTC');
require_once __DIR__ . "/db/Exceptions/IOException.php";
require_once __DIR__ . "/db/Exceptions/JsonException.php";
require_once __DIR__ . "/db/Classes/IoHelper.php";
require_once __DIR__ . "/db/SleekDB.php";
require_once __DIR__ . "/db/Store.php";
require_once __DIR__ . "/db/QueryBuilder.php";
require_once __DIR__ . "/db/Query.php";
require_once __DIR__ . "/db/Cache.php";
require_once __DIR__ . "/cookies.php";

use SleekDB\Store;

function add_white_click($data, $reason)
{
    $dataDir = __DIR__ . "/logs";
    $wclicksStore = new \SleekDB\Store("whiteclicks", $dataDir);

    $calledIp = $data->ip;
    $country = $data->country;
    $dt = new DateTime();
    $time = $dt->getTimestamp();
    $os = $data->os;
    $isp = str_replace(',', ' ', $data->isp);
    $user_agent = str_replace(',', ' ', $data->ua);

    $queryarr = [];
    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
        parse_str($_SERVER['QUERY_STRING'], $queryarr);
    }

    $click = [
        "time" => $time,
        "ip" => $calledIp,
        "country" => $country,
        "os" => $os,
        "isp" => $isp,
        "ua" => $user_agent,
        "reason" => $reason,
        "subs" => $queryarr
    ];

    try {
        $wclicksStore->insert($click);
    } catch (Exception $e) {
        error_log("Error in add_white_click: " . $e->getMessage());
        throw $e;
    }
}

function add_black_click($subid, $data, $preland, $land)
{
    $dataDir = __DIR__ . "/logs";
    $bclicksStore = new \SleekDB\Store("blackclicks", $dataDir);

    $calledIp = is_object($data) ? $data->ip : $data['ip'];
    $country = is_object($data) ? $data->country : $data['country'];
    $dt = new DateTime();
    $time = $dt->getTimestamp();
    $os = is_object($data) ? $data->os : $data['os'];
    $isp = is_object($data) ? str_replace(',', ' ', $data->isp) : str_replace(',', ' ', $data['isp']);
    $user_agent = is_object($data) ? str_replace(',', ' ', $data->ua) : str_replace(',', ' ', $data['ua']);
    $prelanding = empty($preland) ? 'unknown' : $preland;
    $landing = empty($land) ? 'unknown' : $land;

    $queryarr = [];
    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
        parse_str($_SERVER['QUERY_STRING'], $queryarr);
    }

    $click = [
        "subid" => $subid,
        "time" => $time,
        "ip" => $calledIp,
        "country" => $country,
        "os" => $os,
        "isp" => $isp,
        "ua" => $user_agent,
        "subs" => $queryarr,
        "preland" => $prelanding,
        "land" => $landing
    ];

    try {
        $bclicksStore->insert($click);
    } catch (Exception $e) {
        error_log("Error in add_black_click: " . $e->getMessage());
        throw $e;
    }
}

function add_lead($subid, $name, $phone, $status = 'Lead')
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);

    $fbp = get_cookie('_fbp');
    $fbclid = get_cookie('fbclid');
    if ($fbclid === '') $fbclid = get_cookie('_fbc');

    if ($status == '') $status = 'Lead';

    $dt = new DateTime();
    $time = $dt->getTimestamp();

    $land = get_cookie('landing');
    if (empty($land)) $land = 'unknown';
    $preland = get_cookie('prelanding');
    if (empty($preland)) $preland = 'unknown';

    $lead = [
        "subid" => $subid,
        "time" => $time,
        "name" => $name,
        "phone" => $phone,
        "status" => $status,
        "fbp" => $fbp,
        "fbclid" => $fbclid,
        "preland" => $preland,
        "land" => $land
    ];

    try {
        $leadsStore->insert($lead);
    } catch (Exception $e) {
        error_log("Error in add_lead: " . $e->getMessage());
        throw $e;
    }

    if (has_conversion_cookies($name, $phone)) {
        error_log("Conversion has already been done");
        return 0;
    }

    //добавляем в куку информацию о состоявшейся конверсии. Будем хранить 24 часа.
    ywbsetcookie('conversion',$name."||".$phone,(time()+86400));
    return 1;
}

function lead_exists($subid)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    $bclicksStore = new \SleekDB\Store("blackclicks", $dataDir);

    $clkinfo = $bclicksStore->findBy([["subid","=",$subid]], ["time" => "desc"]);
    $leadinfo = $leadsStore->findBy([["subid","=",$subid]], ["time" => "desc"]);

    return (!empty($leadinfo)&&!empty($clkinfo));
}

function update_lead_status($subid, $status)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);

    $lead = $leadsStore->findOneBy(["subid", "=", $subid]);

    if (!empty($lead)) {
        $lead['status'] = $status;
        return $leadsStore->update($lead);
    }
    return false;
}

function add_lpctr($subid, $preland, $land = '')
{
    $dataDir = __DIR__ . "/logs";
    $lpctrStore = new \SleekDB\Store("lpctr", $dataDir);
    $dt = new DateTime();
    $time = $dt->getTimestamp();

    $lpctr = [
        "time" => $time,
        "subid" => $subid,
        "preland" => $preland,
        "land" => $land
    ];
    $lpctrStore->insert($lpctr);
}

function update_lead($subid, $status, $payout = '')
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    $lead = $leadsStore->findOneBy([["subid", "=", $subid]]);
    if ($lead === null) {
        $bclicksStore = new \SleekDB\Store("blackclicks", $dataDir);
        $click = $bclicksStore->findOneBy([["subid", "=", $subid]]);
        if ($click === null) return false;
        $lead = add_lead($subid, '', '');
    }

    $lead["status"] = $status;
    if ($payout !== '') {
        $lead["payout"] = $payout;
    }
    
    try {
        $leadsStore->update($lead);
        return true;
    } catch (Exception $e) {
        error_log("Error in update_lead: " . $e->getMessage());
        return false;
    }
}

function email_exists_for_subid($subid)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    $lead = $leadsStore->findOneBy([["subid", "=", $subid]]);
    if ($lead === null) return false;
    if (array_key_exists("email", $lead)) return true;
    return false;
}

function add_email($subid, $email)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    $lead = $leadsStore->findOneBy([["subid", "=", $subid]]);
    if ($lead === null) return;
    $lead["email"] = $email;
    $leadsStore->update($lead);
}

//проверяем, есть ли в файле лидов subid текущего пользователя
//если есть, и также есть такой же номер - значит ЭТО ДУБЛЬ!
//И нам не нужно слать его в ПП и не нужно показывать пиксель ФБ!!
function lead_is_duplicate($subid, $phone)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    if ($subid != '') {
        $lead = $leadsStore->findOneBy([["subid", "=", $subid]]);
        if ($lead === null) return false;
        header("YWBDuplicate: We have this sub!");
        $phoneexists = ($lead["phone"] === $phone);
        if ($phoneexists) {
            header("YWBDuplicate: We have this phone!");
            return true;
        } else {
            return false;
        }
    } else {
        //если куки c subid у нас почему-то нет, то проверяем по номеру телефона
        $lead = $leadsStore->findOneBy([["phone", "=", $phone]]);
        if ($lead === null) return false;
        return true;
    }
}
