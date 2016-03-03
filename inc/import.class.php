<?php
class Import
{
    function __construct()
    {
    }

    public $users; //Eetlijst
    public $saldos; //Eetlijst
    public $emails; 
    public $WBWbankList;

    function rest_helper($url, $params = null, $verb = 'GET', $format = 'json')
    {
        $cparams = array(
            'http' => array(
                'method' => $verb,
                'ignore_errors' => true,
                'header' => "Content-type: application/x-www-form-urlencoded\r\n"
            )
        );
        if ($params !== null) {
            $params = http_build_query($params);
            $cparams['http']['content'] = $params;
        }

        $context = stream_context_create($cparams);
        $fp = fopen($url, 'rb', false, $context);
        if (!$fp) {
            $res = false;
        } else {
            // If you're trying to troubleshoot problems, try uncommenting the
            // next two lines; it will show you the HTTP response headers across
            // all the redirects:
            //$meta = stream_get_meta_data($fp);
            //var_dump($meta['wrapper_data']);
            $res = stream_get_contents($fp);
        }

        if ($res === false) {
            throw new Exception("$verb $url failed: $php_errormsg");
        }
        return $res;
    }

    function getInnerSubstring($start,$end,$string){
        // "foo a foo" becomes: array(""," a ","")
        $string = explode($start, $string, 3); // also, we only need 2 items at most
        if(!isset($string[1])) return '';
        $string = explode($end, $string[1], 3); // also, we only need 2 items at most
        // we check whether the 2nd is set and return it, otherwise we return an empty string
        return isset($string[0]) ? $string[0] : '';
    }

    function getSaldosEetlijst($username,$password){
        $html = $this->rest_helper("http://eetlijst.nl/login.php?login=".$username."&pass=".$password);

        $htmlTitle = $this->getInnerSubstring("<title>",'</title>',$html);
		//var_dump($htmlTitle);
        if($htmlTitle != 'Fout bij inloggen'){
            $sessionId = $this->getInnerSubstring("logout.php?session_id=",'">',$html);

            $htmlkost = $this->rest_helper("http://eetlijst.nl/kosten.php?session_id=".$sessionId);

            $denamen = $this->getInnerSubstring('<th width="80">Bedrag</th>','<th rowspan="',$htmlkost);
            $denamen = str_replace('</th>', '|', $denamen); // zet | tussen iedere naam
            $denamen = strip_tags($denamen); // stript html tags
            $denamen = trim($denamen); // haalt spaties weg
            $denamen = substr_replace($denamen ,"",-1); // haalt laatste | weg
            $denamen = explode('|',$denamen); // maakt array van string
			$denamen = array_map('trim',$denamen); //trim every item
			
            $dekosten = $this->getInnerSubstring('<td class="l" colspan="2"><b>Totaal</b></td>','</tr>',$htmlkost);
            $dekosten = str_replace('</b>', '|', $dekosten);
            $dekosten = strip_tags($dekosten);
            $dekosten = trim($dekosten);
            $dekosten = substr_replace($dekosten ,"",-1);
            $dekosten = explode('|',$dekosten);
            array_shift($dekosten); // haalt het totaal bedrag weg
			$dekosten = array_map('trim',$dekosten); //trim every item

            //var_dump($denamen);
            //var_dump($dekosten);
            $this->users = $denamen;
            $this->saldos = $dekosten;
			return true;
        }
        else{
            return false;
        }
    }
	
	function testEetlijst()
	{
		$this->getSaldosEetlijst("abba","amsterdam");
		if(	$this->users[0] == "Persoon0" &&
			$this->users[1] == "Persoon1" &&
			$this->users[2] == "Persoon2" &&
			$this->saldos[0] == "9,24" &&
			$this->saldos[1] == "-0,87" &&
			$this->saldos[2] == "-8,38") {
				return true;
			}
			return false;
	}

    function getSaldosWieBetaaltWat($username,$password){
        if(empty($username) || empty($password))
        {
            echo "WBW:error|";
        }
        else
        {
            $html = $this->rest_helper("https://www.wiebetaaltwat.nl/index.php",
                array(
                    'action' => urlencode('login'),
                    'username' => $username,
                    'password' => $password,
                    'login_submit' => urlencode('login')
                ), 'POST'
            );

            $lijst = explode("index.php?lid=",$html);
            array_shift($lijst);

            if(count($lijst) == 0)
            {
                echo "WBW:error|UP";
            }
            else
            {
                $groups = array();
                foreach($lijst as $tempid)
                {
                    $bankList = new BankList();

                    $tempids = explode("&page=balance",$tempid);
                    $bankList->ListID = $tempids[0];

                    $listName = explode("\">",$tempids[1]);
                    $listName = explode("</a>",$listName[1]);
                    $bankList->ListName = $listName[0];

                    $htmlBankItems = $this->rest_helper("https://www.wiebetaaltwat.nl/index.php?lid=".$bankList->ListID."&page=members",
                        array(
                            'action' => urlencode('login'),
                            'username' => $username,
                            'password' => $password,
                            'login_submit' => urlencode('login')
                        ), 'POST'
                    );

                    $htmlBankItems = $this->getInnerSubstring('<th colspan="3">Deelnemers beheren</th>','<h3>Groepeer deelnemers</h3>',$htmlBankItems);
                    $users = explode('title=\'Deelnemer',$htmlBankItems);
					array_shift($users); //delete first item
                    $naam = null;
                    $saldo = null;
                    $email = null;

                    foreach($users as $u){
                        $naam[] = trim(strip_tags($this->getInnerSubstring('ellipsis\'>','<',$u)));
                        $saldo[] = trim(strip_tags($this->getInnerSubstring('&euro;&nbsp;','</strong>',$u)));
                        $email[] = trim(strip_tags($this->getInnerSubstring('<input type="hidden" name="email" value="','">',$u)));
                    }
                    $bankList->UserNames = $naam;
                    $bankList->UserAmounts = $saldo;
                    $bankList->UserEmails = $email;

                    $groups[] = $bankList;
                }
                $this->WBWbankList = $groups;
            }
        }
    }
	
	function testWieBetaaltWat()
	{
		$this->getSaldosWieBetaaltWat("arwin1993@gmail.com","123456");
		if(	$this->WBWbankList[0]->UserNames[0] == "Arwin" &&
			$this->WBWbankList[0]->UserNames[1] == "Edwoud" &&
			$this->WBWbankList[0]->UserNames[3] == "Monique" &&
			$this->WBWbankList[0]->UserAmounts[0] == "9,12" &&
			$this->WBWbankList[0]->UserAmounts[1] == "1,33" &&
			$this->WBWbankList[0]->UserAmounts[3] == "-6,95" &&
			$this->WBWbankList[0]->UserEmails[0] == "arwinvandervelden@hotmail.com" &&
			$this->WBWbankList[0]->UserEmails[1] == "arwin1993@gmail.com" &&
			$this->WBWbankList[0]->UserEmails[3] == "c1202586@rmqkr.net") {
				return true;
			}
			return false;
	}
}

class BankList
{
    public $ListName;
    public $ListID;
    public $BankItems;
    public $UserNames;
    public $UserAmounts;
    public $UserEmails;
    public $UserFactors;
}