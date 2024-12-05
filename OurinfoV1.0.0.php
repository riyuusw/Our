<?php
error_reporting(0);

const
title = "ourinfo",
versi = "1.0.0",
class_require = "1.0.5",
host = "https://ourinfo.top/",
refflink = "https://ourinfo.top/?r=1429",
youtube = "https://youtu.be/XX4kVx-80Vw";

function DownloadSc($server) {
	$colors = [
		"\033[48;5;16m",  // Black
		"\033[48;5;24m",  // Dark blue
		"\033[48;5;34m",  // Green
		"\033[48;5;44m",  // Blue
		"\033[48;5;54m",  // Light blue
		"\033[48;5;64m",  // Violet
		"\033[48;5;74m",  // Purple
		"\033[48;5;84m",  // Purple-Blue
		"\033[48;5;94m",  // Light purple
		"\033[48;5;104m"  // Pink
	];
	$text = "Proses Download Script...";
	$textLength = strlen($text);

	for ($i = 1; $i <= $textLength; $i++) {
		usleep(150000);  // Delay 150.000 mikrodetik = 0.15 detik
		$percent = round(($i / $textLength) * 100); 
		$bgColor = $colors[$i % count($colors)];
		$coloredText = substr($text, 0, $i);
		$remainingText = substr($text, $i);
		echo $bgColor . $coloredText . "\033[0m" . $remainingText . " {$percent}% \r";
		flush();
	}
	file_put_contents($server."\iewilofficial\class.php",file_get_contents("https://raw.githubusercontent.com/iewilmaestro/myFunctions/refs/heads/main/Class.php"));
	echo "\n\033[48;5;196mProses selesai!,jalankan ulang script\033[0m\n";
	exit;
}

$server = $_SERVER["TMP"];
if(!$server){
	$server = $_SERVER["TMPDIR"];
}

update:
if(!file_exists($server."\iewilofficial\class.php")){
	system("mkdir ".$server."\iewilofficial");
	DownloadSc($server);
}
require $server."\iewilofficial\class.php";

if(class_version < class_require){
	print "\033[1;31mVersi class sudah kadaluarsa\n";
	unlink($server."\iewilofficial\class.php");
	DownloadSc($server);
}

class Bot {
	public $cookie,$uagent;
	public function __construct(){
		$this->server = Functions::Server(title);
		if($this->server['data']['status'] != "online"){
			Display::Ban(title, versi);
			print Display::Error("Status Script is offline\n");
			exit;
		}
		$this->update = ($this->server['data']['version'] == versi)?false:true;
		Display::Ban(title, versi);
		if($this->update > null){
			print m."---[".p."^".m."]".h." Update sc Detect\n";
			print m."---[".p."version ".m."] ".p.$this->server['data']['version'].n;
			print m."---[".p."download".m."] ".p.$this->server['data']['link'].n;
			Display::Line();
		}
		cookie:
		if(empty(Functions::getConfig('cookie'))){
			Display::Cetak("Register",refflink);
			Display::Line();
		}
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		$this->scrap = new HtmlScrap();
		Functions::view(youtube);
		
		Display::Ban(title, versi);
		
		$r = $this->Dashboard();
		if($r['Logout']){
			Functions::removeConfig("cookie");
			Functions::removeConfig("user_agent");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		if($this->Claim()){
			Functions::removeConfig("user_agent");
			Functions::removeConfig("cookie");
			goto cookie;
		}
	}
	
	public function headers($data=0){
		$h[] = "Host: ".parse_url(host)['host'];
		if($data)$h[] = "Content-Length: ".strlen($data);
		$h[] = "User-Agent: ".$this->uagent;
		$h[] = "Cookie: ".$this->cookie;
		return $h;
	}
	
	public function Dashboard(){
		$r = Requests::get(host,$this->headers())[1];
		if(!preg_match('/Logout/',$r)){
			return ["Logout" => true];
		}else{
			return ["Logout" => false];
		}
	}

	public function Claim(){
		if(!$this->coins){
			$r = Requests::get(host,$this->headers())[1];
			preg_match_all('#https?:\/\/'.str_replace('.','\.',parse_url(host)['host']).'\/faucet\/currency\/([a-zA-Z0-9]+)#', $r, $matches);
			$this->coins = $matches[1];
		}
		while(true){
			$r = $this->Dashboard();
			if($r['Logout']){
				print Display::Error("Cookie Expired\n");
				Display::Line();
				return 1;
			}
			foreach($this->coins as $a => $coin){
				$r = Requests::get(host."faucet/currency/".$coin,$this->headers())[1];
				$scrap = $this->scrap->Result($r);
				
				if($scrap['firewall']){
					print Display::Error("Firewall Detect\n");
					exit;
					continue;
				}
				if($scrap['cloudflare']){
					print Display::Error(host."faucet/currency/".$coin.n);
					print Display::Error("Cloudflare Detect\n");
					Display::Line();
					return 1;
				}
				
				// Mesasge
				if(preg_match("/You don't have enough energy for Auto Faucet!/",$r)){exit(Error("You don't have enough energy for Auto Faucet!\n"));}
				if(preg_match('/Daily claim limit/',$r)){
					unset($this->coins[$a]);
					Display::Cetak($coin,"Daily claim limit");
					continue;
				}
				$status_bal = explode('</span>',explode('<span class="badge badge-danger">',$r)[1])[0];
				if($status_bal == "Empty"){
					unset($this->coins[$a]);
					Display::Cetak($coin,"Sufficient funds");
					continue;
				}
				
				// Delay
				$tmr = explode(";",explode('let timer = ',$r)[1])[0];
				if($tmr){
					Functions::Tmr($tmr);
				}
				
				// Exsekusi
				$data = $scrap['input'];
				if(!$data)continue;
				// CAPTCHA
				
				$data = http_build_query($data);
				$r = Requests::post(host."faucet/verify/".$coin,$this->headers(), $data)[1];
				
				$scrap2 = $this->scrap->Result($r);
				if($scrap2['firewall']){
					print Display::Error("Firewall Detect\n");
					exit;
					continue;
				}
				
				$ban = explode('</div>',explode('<div class="alert text-center alert-danger"><i class="fas fa-exclamation-circle"></i> Your account',$r)[1])[0];
				$ss = explode("title: 'Success!',",$r)[1];
				$wr = explode("'",explode("html: '",$r)[1])[0];
				if($ban){
					print Display::Error("Your account".$ban.n);
					exit;
				}
				if(preg_match('/invalid amount/',$r)){
					unset($this->coins[$a]);
					print Display::Error("You are sending an invalid amount of payment to the user\n");
					Display::Line();
				}
				if(preg_match('/Shortlink in order to claim from the faucet!/',$r)){
					print Display::Error(explode("'",explode("html: '",$r)[1])[0]);
					Display::Line();
					exit;
				}
				if(preg_match('/sufficient funds/',$r)){
					unset($this->coins[$a]);
					Display::Cetak($coin,"Sufficient funds");
					Display::Line();
					continue;
				}
				if($ss){
					Display::Cetak($coin,($scrap['faucet'][1][0]-1)."/".$scrap['faucet'][2][0]);
					print Display::Sukses(strip_tags(explode("',",explode("html: '",$ss)[1])[0]));
					Display::Line();
				}elseif($wr){
					print Display::Error(substr($wr,0,30));
					sleep(3);
					print "\r                  \r";
				}else{
					print Display::Error("Server Down\n");
					sleep(3);
					print "\r                  \r";
				}
			}
			if(!$this->coins){
				print Display::Error("All coins have been claimed\n");
				exit;
			}
			sleep(2);
		}
	}
}
new Bot();