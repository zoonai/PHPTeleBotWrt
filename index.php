<?php
require_once __DIR__ . "/src/PHPTelebot.php";
require_once __DIR__ . "/src/xc.php";
error_reporting(E_ALL); ini_set('display_errors', 1);
$banner = file_get_contents("src/plugins/banner");
$options = ["parse_mode" => "html", "reply" => true];

// Read token & username
function readToken($input)
{
    $data = file_get_contents("databot");
    $raw = explode("\n", $data);
    return $input == "token" ? $raw[0] : $raw[1];
}

// token user
$bot = new PHPTelebot(readToken("token"), readToken("username"));

// random messages
$ads = [
		"<span class='tg-spoiler'>Keep PHPTeleBotWrt up-to-date.</span>",
		"<span class='tg-spoiler'>Read PHPTeleBotWrt wiki and information <a href='https://www.helmiau.com/blog/phptelebotwrt'>here</a>.</span>",
		"<span class='tg-spoiler'>PHPTeleBotWrt Devs : <a href='https://github.com/radyakaze/phptelebot'>radyakaze</a>, <a href='https://github.com/OppaiCyber/XppaiWRT'>OppaiCyber-XppaiWRT</a>, <a href='https://github.com/xentolopx/eXppaiWRT'>xentolopx-eXppaiWRT</a> and <a href='https://helmiau.com/'>Helmi Amirudin</a>.</span>",
		"<span class='tg-spoiler'>Make sure your device always connected to network.</span>",
    ];
$randAds = $ads[array_rand($ads)];

// Ping Command
$bot->cmd("/ping", function () {
    $start_time = microtime(true);
	Bot::sendMessage("Pinging...", $GLOBALS["options"]);
    $end_time = microtime(true);
    $diff = round(($end_time - $start_time) * 1000);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"Ping time taken: " . $diff . "ms"
		. "\n\n" . $GLOBALS["randAds"]
		,$GLOBALS["options"]);
});

// start bot
$bot->cmd("/start", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"Welcome to PHPTeleBotWrt!\nRun /cmdlist to see all available comands.\n\n Source: https://github.com/helmiau/PHPTeleBotWrt\n Donate: https://helmiau.com/pay"
		. "\n\n" . $GLOBALS["randAds"]
		,$GLOBALS["options"]);
});

// list of commands
$bot->cmd("/cmdlist", function () {
    $check_cron_stat = shell_exec("grep -c 'PHPTeleBotWrt' '/etc/crontabs/root'");
    if ($check_cron_stat === 0) {
        $cron_stat = "NOT ACTIVE";
    } else {
        $cron_stat = "ACTIVE";
    }
	unset($check_cron_stat);
    $check_boot_stat = shell_exec("grep -c 'PHPTeleBotWrt' '/etc/rc.local'");
    if ($check_boot_stat === 0) {
        $boot_stat = "NOT ACTIVE";
    } else {
        $boot_stat = "ACTIVE";
    }
	unset($check_boot_stat);
	Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
"## ZonaWRT Bot ##
 • /botup : Update bot binaries
 • /botas : +/- Bot Auto Start on Boot [$boot_stat]
 • /botcr : +/+ Bot Cron Job [$cron_stat]
 
 ## Aria2 Commands ##
 • /aria2add : Add Task
 • /aria2stats : Aria2 Status
 • /aria2pause : Pause All
 • /aria2resume : Resume All
 
## OpenClash Commands ##
 • /oc : OC Information
 • /ocst : Start/Restart Openclash
 • /ocsp : Stop Openclash
 • /ocpr : Proxies status 
 • /ocrl : Rule list 
 • /ocup : Update Openclash App Only
 • /ocua : Update Openclash and All Cores

## MyXL Commands ##
 • /myxl : Bandwidth Usage 
 • /setxl 087 : Set Default Number

## File Manager ##
 • /ul : Upload a File to OpenWrt
 • /dl : +/- a File from OpenWrt
 • /cp : Copy a File to Another Folder
 • /mv : Move a File to Another Folder
 • /rm : Delete a file

## System  ##
 • /sysinfo : System Information
 • /memory : Memory Status 
 • /sh [ Terminal ] : Run custom command
 • /rs ls : List of Compatible App Restart
 • /rs appname : Restart App in init.d
 
## Power System ##
 • /reboot : Reboot OpenWrt
 • /turnoff : Turn Off OpenWrt
 
## Network Information ##
 • /netcl : Lists Client Devices
 • /fwlist : Firewall Lists
 • /ifcfg interface : List Device Interface 
 • /vnstat : Bandwidth Usage 
 • /vnstati : Better Bandwidth Usage 
 • /myip : Get IP Details 
 • /speedtest : Speedtest Ookla
 • /ping : Ping bot"
		. "\n\n" . $GLOBALS["randAds"]
		,$GLOBALS["options"]);
	unset($boot_stat);
	unset($cron_stat);
});

// when file uploaded
$bot->on('document', function() {
    $message = Bot::message();
	$fileName = $message['document']['file_name'];
    Bot::sendMessage(
		"File <code>$fileName</code> uploaded to Telegram server. Reply uploaded file with command <code>/ul /folder/folder_dest</code> to upload it to that folder. Change <code>/folder/folder_dest</code> to your own destination folder.". "\n\n" .
		"File <code>$fileName</code> telah diunggah ke server Telegram. Balas file yang sudah di unggah dengan perintah <code>/ul /folder/folder_dest</code> untuk mengunggahnya ke folder tersebut. Ubah <code>/folder/folder_dest</code> dengan folder tujuan anda."
		,$GLOBALS["options"]);
 });

//upload cmd
$bot->cmd("/ul", function ($filedir) {
    $token = readToken("token");
    $message = Bot::message();
	$filePath = $filedir;
	$fileInfo = $message['reply_to_message']['document'];
    $fileName = $fileInfo['file_name'];
    $fileId = $fileInfo['file_id'];
    $raw = json_decode(Bot::getFile($fileId),true);
    $file_server_path = $raw['result']['file_path'];
	if (!is_null($filePath) && is_dir($filedir) && isset($fileName) && isset($file_server_path)) {
		$wget = shell_exec("wget -O \"$filedir/$fileName\" \"https://api.telegram.org/file/bot$token/$file_server_path\"");
		$pesan_upf = "File <code>$fileName</code> uploaded to <code>$filedir</code> successfully!." . "\n\n" .
		"File <code>$fileName</code> berhasil diunggah ke folder <code>$filedir</code>!.";
	} else {
		$pesan_upf = "Directory<code>$filedir</code> is invalid!." . "\n" .
		"Folder<code>$filedir</code> tidak valid!." . "\n\n" .
		"<strong><u>OpenWrt File Uploader</u></strong>\n- Upload a file to this chat first.\n- Then reply uploaded file with command <code>/ul /folder/folder_dest</code> to upload it to that folder. Change <code>/folder/folder_dest</code> to your own destination folder.\n- Only support single file upload." . "\n\n" .
		"<strong><u>Pengunggah Berkas OpenWrt</u></strong>\n- Unggah file ke chat ini terlebih dahulu.\n- Lalu balas file yang sudah di unggah dengan perintah <code>/ul /folder/folder_dest</code> untuk mengunggahnya ke folder tersebut. Ubah <code>/folder/folder_dest</code> ke folder tujuan anda.\n- Hanya mendukung upload satu file saja.";
	}
	
	Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"$pesan_upf"
		. "\n\n" . $GLOBALS["randAds"]
		,$GLOBALS["options"]);
	
	unset($token);
	unset($message);
	unset($filePath);
	unset($fileInfo);
	unset($fileName);
	unset($fileId);
	unset($raw);
	unset($file_server_path);
});

//download/retrieve file from openwrt cmd
// curl -F document=@\"/filepath/filename\" \"https://api.telegram.org/bot5227493446:AAGN1BeLV0I_7KIAyq_4aE6BZfH_fXq9yGQ/sendDocument?chat_id=236082523\"
$bot->cmd("/dl", function ($filedir) {
    $token = readToken("token");
    $message = Bot::message();
    $chat_dest = $message['from']['id'];
	if (file_exists($filedir)) {
		$curled = shell_exec("curl -F document=@\"$filedir\" \"https://api.telegram.org/bot$token/sendDocument?chat_id=$chat_dest\"");
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"File <code>$filedir</code> retrieved successfully!.\n\nFile <code>$filedir</code> telah diterima."
			. "\n\n" . $GLOBALS["randAds"]
			,$GLOBALS["options"]);
	} else {
		Bot::sendMessage(
		"Please input correct command. Example: <code>/dl /folder1/filename.ext</code>.\n Or file doesn't exists on the server.\n\nTulis perintah dengan benar. Contoh: <code>/dl /folder1/filename.ext</code>\n Atau mungkin file tidak ada di server."
		,$GLOBALS["options"]);
	}
	unset($token);
	unset($message);
	unset($chat_dest);
});

//copy file cmd
$bot->cmd("/cp", function ($cpold, $cpnew) {
    if (file_exists($cpold) && !file_exists($cpnew)) {
		$copied = shell_exec("cp \"$cpold\" \"$cpnew\"");
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"File <code>$cpold</code> copied to <code>$cpnew</code>!.\nFile <code>$cpold</code> telah dipindah ke <code>$cpnew</code>!."
			. "\n\n" . $GLOBALS["randAds"]
			,$GLOBALS["options"]);
    } else {
		Bot::sendMessage(
		"Please input correct command. Example: <code>/cp /oldfolder/file.txt /newfolder/file.txt</code>.\n Or file source/destination doesn't exists on the server.\n\nTulis perintah dengan benar. Contoh: <code>/cp /oldfolder/file.txt /newfolder/file.txt</code>\n Atau mungkin file asal/tujuan tidak ada di server."
		,$GLOBALS["options"]);
    }
	unset($cpold);
	unset($cpnew);
});

//move file cmd
$bot->cmd("/mv", function ($mvold, $mvnew) {
    if (file_exists($mvold) && !file_exists($mvnew)) {
		$copied = shell_exec("cp \"$mvold\" \"$mvnew\" && rm -f \"$mvold\"");
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"File <code>$mvold</code> moved to <code>$mvnew</code>!.\nFile <code>$mvold</code> telah dipindah ke <code>$mvnew</code>!."
			. "\n\n" . $GLOBALS["randAds"]
			,$GLOBALS["options"]);
    } else {
		Bot::sendMessage(
		"Please input correct command. Example: <code>/mv /oldfolder/file.txt /newfolder/file.txt</code>.\n Or file source/destination doesn't exists on the server.\n\nTulis perintah dengan benar. Contoh: <code>/mv /oldfolder/file.txt /newfolder/file.txt</code>\n Atau mungkin file asal/tujuan tidak ada di server."
		,$GLOBALS["options"]);
    }
	unset($mvold);
	unset($mvnew);
});

//delete file cmd
$bot->cmd("/rm", function ($rmfile) {
    if (file_exists($rmfile)) {
		$copied = shell_exec("rm -f \"$rmfile\"");
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"File <code>$rmfile</code> deleted!.\nFile <code>$rmfile</code> telah dihapus!."
			. "\n\n" . $GLOBALS["randAds"]
			,$GLOBALS["options"]);
    } else {
		Bot::sendMessage(
		"Please input correct command. Example: <code>/rm /folder/file.txt</code>.\n Or file source/destination doesn't exists on the server.\n\nTulis perintah dengan benar. Contoh: <code>/rm /folder/file.txt</code>\n Atau mungkin file asal/tujuan tidak ada di server."
		,$GLOBALS["options"]);
    }
	unset($rmfile);
});

//restart init file cmd
$bot->cmd("/rs", function ($app = 'ls') {
    $appPath = "/etc/init.d/$app";
	if ($app === 'ls' && !file_exists($appPath)) {
		//$dtIX = shell_exec("ls -l /etc/init.d | awk '{print$9}'");
		$dtIX = shell_exec("src/plugins/getinitapp.sh > listInit && cat listInit");
		Bot::sendMessage(
			"This command allow you to restart an app listed below." . "\n" .
			"Example: <code>/rs openclash</code>" . "\n" .
			"List of supported apps:" . "\n" .
			"###########" . "\n" .
			"<code>" . $dtIX . "</code>..." . "\n" .
			"###########" 
			,$GLOBALS["options"]);
		unset($dtIX);
    } else {
		$grepST = shell_exec("grep -c restart $appPath");
		if ($grepST === 0) {
			$rextat = shell_exec("$appPath start >/dev/null 2>&1 &");
		} else {
			$rextat = shell_exec("$appPath restart >/dev/null 2>&1 &");
		}
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"Restarting <code>" . $app . "</code>..." . "\n\n" .
			"Run <code>/rs ls</code> to see listed supported apps"
			. "\n\n" . $GLOBALS["randAds"]
			,$GLOBALS["options"]);
		unset($grepST);
    }
});

//bash cmd custom command terminal
$bot->cmd("/sh", function ($bashXmd) {
	$tzX = "sht.sh";
	$crtFlX = shell_exec("echo \"$bashXmd\" > $tzX && chmod 0755 $tzX");
	$runsh = shell_exec("./$tzX > rpbXz && cat rpbXz");
	// return $runsh;
	
	Bot::sendMessage(
		$GLOBALS["banner"]
		,$GLOBALS["options"]);
	Bot::sendMessage(
		"<code>" . $runsh ."</code>"
		,$GLOBALS["options"]);
	Bot::sendMessage(
		$GLOBALS["randAds"]
		,$GLOBALS["options"]);

	$rmrunsh = shell_exec("rm rpbXz && rm $tzX");
});


// OpenWRT Command
// OpenClash Proxies
$bot->cmd("/ocpr", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . OpenClashProxies() . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
		,$GLOBALS["options"]);
});

// OpenClash Start
$bot->cmd("/ocst", function () {
	Bot::sendMessage(
		"Start/Restarting Openclash ... "
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . shell_exec("uci set openclash.config.enable=1 && uci commit openclash && /etc/init.d/openclash restart >/dev/null 2>&1 &") . "</code>"
		. "Openclash started successfully!."
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// OpenClash Stop
$bot->cmd("/ocsp", function () {
	Bot::sendMessage(
		"Stopping Openclash ... "
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . shell_exec("uci set openclash.config.enable=0 && uci commit openclash && /etc/init.d/openclash stop >/dev/null 2>&1 &") . "</code>"
		. "Openclash stopped successfully!."
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// OpenClash Update
$bot->cmd("/ocup", function () {
    $ocver = shell_exec("echo -e $(opkg status luci-app-openclash 2>/dev/null |grep 'Version' | awk -F 'Version: ' '{print$2}')");
	Bot::sendMessage(
		"Checking Openclash version update ... "
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		"<code>" . shell_exec("/usr/share/openclash/openclash_update.sh") . "</code>"
        ,$GLOBALS["options"]);
    $ocver2 = shell_exec("echo -e $(opkg status luci-app-openclash 2>/dev/null |grep 'Version' | awk -F 'Version: ' '{print$2}')");
	if ($ocver2 === $ocver) {
		$ocupinfo = "Openclash is already at latest version";
	} else {
		$ocupinfo = "Openclash updated to $ocver2";
	}
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"$ocupinfo"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// OpenClash Update All core
$bot->cmd("/ocua", function () {
	$oc_app_old = shell_exec("echo -e $(opkg status luci-app-openclash 2>/dev/null |grep 'Version' | awk -F 'Version: ' '{print$2}')");
	$core_old = shell_exec("echo -e $(/etc/openclash/core/clash -v 2>/dev/null |awk -F ' ' '{print $2}' 2>/dev/null)");
	$core_tun_old = shell_exec("echo -e $(/etc/openclash/core/clash_tun -v 2>/dev/null |awk -F ' ' '{print $2}' 2>/dev/null)");
	$core_meta_old = shell_exec("echo -e $(/etc/openclash/core/clash_meta -v 2>/dev/null |awk -F ' ' '{print $3}' 2>/dev/null)");
	
	Bot::sendMessage(
		"Checking Openclash and cores version update ... "
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		"<code>" . shell_exec("sh /usr/share/openclash/openclash_update.sh 'one_key_update' >/dev/null 2>&1 &") . "</code>"
        ,$GLOBALS["options"]);

	$oc_app_new = shell_exec("echo -e $(opkg status luci-app-openclash 2>/dev/null |grep 'Version' | awk -F 'Version: ' '{print$2}')");
	$core_new = shell_exec("echo -e $(/etc/openclash/core/clash -v 2>/dev/null |awk -F ' ' '{print $2}' 2>/dev/null)");
	$core_tun_new = shell_exec("echo -e $(/etc/openclash/core/clash_tun -v 2>/dev/null |awk -F ' ' '{print $2}' 2>/dev/null)");
	$core_meta_new = shell_exec("echo -e $(/etc/openclash/core/clash_meta -v 2>/dev/null |awk -F ' ' '{print $3}' 2>/dev/null)");

	if ($oc_app_new === $oc_app_old) {
		$oc_app_info = "Openclash App is already at latest version";
	} else {
		$oc_app_info = "Openclash updated to $oc_app_new";
	}
	if ($core_new === $core_old) {
		$core_new_info = "Dev core is already at latest version";
	} else {
		$core_new_info = "Dev core updated to $core_new";
	}
	if ($core_tun_new === $core_tun_old) {
		$core_tun_info = "TUN core is already at latest version";
	} else {
		$core_tun_info = "TUN core updated to $core_tun_new";
	}
	if ($core_meta_new === $core_meta_old) {
		$core_meta_info = "Meta core is already at latest version";
	} else {
		$core_meta_info = "Meta core updated to $core_meta_new";
	}
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"$oc_app_info" . "\n" .
		"$core_new_info" . "\n" .
		"$core_tun_info" . "\n" .
		"$core_meta_info"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// vnstat
$bot->cmd("/vnstat", function ($input) {
    $input = escapeshellarg($input);
    $output = shell_exec("vnstat $input 2>&1");
    if ($output === null) {
        Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"Invalid input or vnstat not found"
			. "\n" . $GLOBALS["randAds"]
			,$GLOBALS["options"]);
    } else {
        Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"<code>" . $output . "</code>"
			. "\n" . $GLOBALS["randAds"]
			,$GLOBALS["options"]);
    }
});

// vnstati
$bot->cmd("/vnstati", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" 
		. $GLOBALS["randAds"]
		,$GLOBALS["options"]);

    $image_files = [
        'summary' => 'vnstati -s -i br-lan -o summary.png',
        'hourly' => 'vnstati -h -i br-lan -o hourly.png',
        'daily' => 'vnstati -d -i br-lan -o daily.png',
        'monthly' => 'vnstati -m -i br-lan -o monthly.png',
        'yearly' => 'vnstati -y -i br-lan -o yearly.png',
        'top' => 'vnstati --top 5 -i br-lan -o top.png',
    ];
    
    foreach ($image_files as $image_file) {
        shell_exec($image_file);
    }
    
    foreach ($image_files as $file_name => $command) {
        Bot::sendPhoto($file_name . '.png');
    }
    
    shell_exec("rm *.png");
	
});


// Check RAM/Memory
$bot->cmd("/memory", function () {
    $meminfo = file("/proc/meminfo");
    $total = intval(trim(explode(":", $meminfo[0])[1])) / 1024;
    $free = intval(trim(explode(":", $meminfo[1])[1])) / 1024;
    $used = $total - $free;
    $percent = round(($used / $total) * 100);
    $bar = str_repeat("■", round($percent / 5));
    $bar .= str_repeat("□", 20 - round($percent / 5));
    $output =
		$GLOBALS["banner"] . "\n" .
        "<code>Memory usage: \nBar: " .
        $bar .
        "\nUsed: $used MB \nAvailable: $free MB \nTotal: $total MB \nUsage: $percent%</code>"
		. "\n\n" . $GLOBALS["randAds"];
    Bot::sendMessage($output, $GLOBALS["options"]);
});

// Systemm info
$bot->cmd("/sysinfo", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/sysinfo.sh -bw") . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// Reboot openwrt
$bot->cmd("/reboot", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "Rebooting Openwrt..." .
        "<code>" . shell_exec("reboot") . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// Turn off openwrt
$bot->cmd("/turnoff", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "Turning off Openwrt..." .
        "<code>" . shell_exec("halt && reboot -p") . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// Network clients info
$bot->cmd("/netcl", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        shell_exec("src/plugins/netcl.sh")
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// Firewall rule lists
$bot->cmd("/fwlist", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/fwlist.sh") . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// Ifconfig
$bot->cmd("/ifcfg", function ($iface) {
    if ($iface === null) {
        $ex_ifcfg = shell_exec("ifconfig");
        $pesan_ifcfg = "Viewing all of interfaces";
    } else {
        $ex_ifcfg = shell_exec("ifconfig $iface");
        $pesan_ifcfg = "Viewing info of $iface interface";
    }
	
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>$pesan_ifcfg\n\n$ex_ifcfg</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// OpenClash
$bot->cmd("/oc", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/oc.sh") . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// My IP Address info
$bot->cmd("/myip", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . myip() . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// OpenClash Rules
$bot->cmd("/ocrl", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . OpenClashRules() . "</code>"
		. "\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// Speedtest
$bot->cmd("/speedtest", function () {
    Bot::sendMessage("Speedtest on Progress... Please wait..", $GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] .
		"<code>" . Speedtest() . "</code>"
		. "\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
	$rmstrXq = shell_exec("rm result_SpeedTST");
});

//Myxl cmd
$bot->cmd("/setxl", function ($number) {
    if ($number == "") {
        Bot::sendMessage(
            "Masukan nomor yang mau di set sebagai default /setxl 087x",
            $GLOBALS["options"]
        );
    } else {
        shell_exec("echo '$number' > xl");
        Bot::sendMessage(
            "Nomer $number disetting sebagai default\nSilahkan gunakan cmd /myxl tanpa memasukkan nomor",
            $GLOBALS["options"]
        );
    }
});

$bot->cmd("/myxl", function ($number) {
    Bot::sendMessage("Checking number $number MyXL on progress...", $GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . MyXL($number) . "</code>"
		. "\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});
//Myxl cmd end

//adb cmd
$bot->cmd("/adb", function () {
    Bot::sendMessage("<code>ADB on Progress</code>", $GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . ADB() . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

//Aria2 cmd
$bot->cmd("/aria2add", function ($url) {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/add.sh $url") . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

$bot->cmd("/aria2stats", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/stats.sh") . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

$bot->cmd("/aria2pause", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/pause.sh") . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

$bot->cmd("/aria2resume", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/resume.sh") . "</code>"
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

//Aria2 cmd end

// phpbotmgr update
$bot->cmd("/botup", function () {
    Bot::sendMessage(
		"Updating PHPTeleBotWrt..."
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		"<code>" . shell_exec("chmod 0755 phpbotmgr && ./phpbotmgr u") . "</code>"
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"PHPTeleBotWrt updated..."
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
});

// phpbotmgr auto start
$bot->cmd("/botas", function () {
    $check_boot_stat = shell_exec("grep -c 'PHPTeleBotWrt' '/etc/rc.local'");
    if ($check_boot_stat === 0) {
        $boot_stat1 = "Activating";
        $boot_stat2 = "activated";
    } else {
        $boot_stat1 = "Deactivating";
        $boot_stat2 = "deactivated";
    }
	
    Bot::sendMessage(
		"$boot_stat1 PHPTeleBotWrt to/from auto start..."
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		"<code>" . shell_exec("chmod 0755 phpbotmgr && ./phpbotmgr a") . "</code>"
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"PHPTeleBotWrt auto start $boot_stat2..."
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);
		
	unset($boot_stat1);
	unset($boot_stat2);
});

// phpbotmgr cron
$bot->cmd("/botcr", function () {
    $check_cron_stat = shell_exec("grep -c 'PHPTeleBotWrt' '/etc/crontabs/root'");
    if ($check_cron_stat === 0) {
        $cron_stat1 = "Activating";
        $cron_stat2 = "activated";
    } else {
        $cron_stat1 = "Deactivating";
        $cron_stat2 = "deactivated";
    }
	
    Bot::sendMessage(
		"$cron_stat1 PHPTeleBotWrt to/from cronjob scheduled task..."
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		"<code>" . shell_exec("chmod 0755 phpbotmgr && ./phpbotmgr t") . "</code>"
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"PHPTeleBotWrt cronjob scheduled task $cron_stat2..."
		. "\n\n" . $GLOBALS["randAds"]
        ,$GLOBALS["options"]);

	unset($cron_stat1);
	unset($cron_stat2);
});

//inline command
$bot->on("inline", function ($cmd, $input) {
    if ($cmd == "proxies") {
        $results[] = [
            "type" => "article",
            "id" => "unique_id1",
            "title" => Proxies(),
            "parse_mode" => "html",
            "message_text" => "<code>" . Proxies() . "</code>",
        ];
    } elseif ($cmd == "rules") {
        $results[] = [
            "type" => "article",
            "id" => "unique_id1",
            "title" => Rules(),
            "parse_mode" => "html",
            "message_text" => "<code>" . Rules() . "</code>",
        ];
    } elseif ($cmd == "myxl") {
        $results[] = [
            "type" => "article",
            "id" => "unique_id1",
            "title" => MyXL($input),
            "parse_mode" => "html",
            "message_text" => "<code>" . MyXL($input) . "</code>",
        ];
    }

    $GLOBALS["options"] = [
        "cache_time" => 3600,
    ];

    return Bot::answerInlineQuery($results, $GLOBALS["options"]);
});

$bot->run();
