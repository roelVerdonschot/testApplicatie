<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_INDEX'];
$_page_description = $lang['DEFAULT_DESCRIPTION'];
$_page_keywords = $lang['DEFAULT_KEYWORDS'];

require_once("inc/header.inc.php");

if(!Authentication_Controller::IsAuthenticated()) {
?>
<div class="splash-container">
	<ul class="rslides">
	  <li><img src="<?php echo $settings['site_url']; ?>images/slider.jpg" alt=""></li>
	  <li><img src="<?php echo $settings['site_url']; ?>images/slider2.jpg" alt=""></li>
	  <li><img src="<?php echo $settings['site_url']; ?>images/slider3.jpg" alt=""></li>
	</ul>
    <div class="splash">
		<!---Start-header----->		
		<div class="wrap">
			<div class="header-left">
				<label> </label>					
				<p>De tool voor al je gezamenlijke activiteiten</p>
				<p>Demo:</p>
				<div class="splash-head">
				
					<a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/demo-cost-management/" class="pure-button pure-button-primary home-btn" id="btn-costs"><span><i class="icon-euro"></i> <?php echo $lang['BTN_COST_INDEX'] ?></span></a>
					<a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/demo-dinner-list/" class="pure-button pure-button-primary home-btn" id="btn-dinnerplanner"><span><i class="icon-restaurant"></i> <?php echo $lang['BTN_DINNER_INDEX'] ?></span></a>
					<a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/demo-task-list/" class="pure-button pure-button-primary home-btn" id="btn-tasklist"><span><i class="icon-check"></i> <?php echo $lang['BTN_TASK_INDEX'] ?></span></a>
				
					
				</div>
				<p class="available"><small>Inclusief de gratis mobiele app voor:</small></p>
				<ul class="app-avialable">
					<li><a class="apple" href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/apps/"> </a></li>
					<li><a class="and" href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/apps/"> </a></li>
					<li><a class="win" href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/apps/"> </a></li>
					<div class="clear"> </div>
				</ul>
			</div>
			<div class="header-right">
				<span><a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/register/" class="pure-button pure-button-primary">Maak nu een account!</a></span>
			</div>
			<div class="clear"> </div>
		</div>
		<!---End-header----->		
    </div>
</div>


	
<section id="phone" style="display:none;">	        
	<form method="post" action="http://www.monipal.com/nl/login/">
		<fieldset>			
			<input type="text" name="username" placeholder="Email" />
			<input type="password" name="password" placeholder="Wachtwoord" />
			<a href="http://www.monipal.com/nl/reset-password/">Wachtwoord vergeten?</a>
			<a href="register.php.html">Register</a>
			<input type="submit" value="login" class="btn btn-red" />
		</fieldset>
	</form>
</section>
	
<div class="content-wrapper" id="info">
    <div class="content">
        <h2 class="content-head is-center">Hoe werkt het?</h2>
		<p class="is-center">Op Monipal.com kun je online lijsten bijhouden van je gezamenlijke activiteiten. <br />Iedere groep kan zelf kiezen welke gezamelijke functies ze gebruiken op de website.</p>

        <div class="pure-g">
            <div class="l-box pure-u-1-3 boxbg">

                <h3 class="content-subhead">
                    <i class="icon-euro"></i>
                    Kostenbeheer
                </h3>
                <p>
                    Hier worden alle uitgaven voor de groep verrekend en in een oogopslag kun jij zien wat de balans is tussen alle groepsleden.
                    Heb je samen gekookt, heb je huishoudelijke artikelen gekocht zoals wc-papier of ben je bijvoorbeeld gaan stappen.
                    Vul het in bij het kostenbeheer en het wordt automatisch over de groep verrekend.
					<!--<br><br><a href="#" class="ml">> Kostenbeheer demo</a>-->
                </p>
            </div>
            <div class="l-box pure-u-1-3 boxbg1">
                <h3 class="content-subhead">
                    <i class="icon-restaurant"></i>
                    Eetlijst
                </h3>
                <p>
                    Je kunt hier voor iedere gebruiker makkelijk aangeven of ze vanavond willen koken, mee eten of niet mee eten. Als je kookt kun je deze kosten na het koken makkelijk bij kostenbeheer invullen, deze worden daarna automatisch verdeeld over de groepsleden die mee aten.
					<!--<br><br><a href="#" class="ml">> Eetlijst demo</a>-->
                </p>
            </div>
            <div class="l-box pure-u-1-3 boxbg">
                <h3 class="content-subhead">
                    <i class="icon-check"></i>
                    Takenlijst
                </h3>
                <p>
                    Laat al je taken binnen de groep heel gemakkelijk verdelen over alle groepsleden via takenlijst. <br />
					Heb je je taak aan het einde van de week nog niet uitgevoerd dan krijg je van ons een reminder zodat je nooit meer vergeet een taak te doen.
					<!--<br><br><a href="#" class="ml">> Takenlijst demo</a>	-->				
                </p>
            </div>            
        </div>
		
		<!--<h2 class="content-head is-center" id="access">Wil jij toegang?</h2>
		<p class="is-center">Momenteel is het alleen mogelijk om op uitnodiging toegang te krijgen en met jou studentenhuis, sportteam of vriendengroep Monipal te gebruiken.<br />Vul hieronder je email adres in wel misschien krijg jij wel een uitnodiging!</p>
		
		<div class="pure-g is-center">
            <div class="l-box pure-u-1">				
				<form action="//monipal.us7.list-manage.com/subscribe/post?u=c86126b719e2bc789a7b8f012&amp;id=06daed3eda" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
					
				<div class="mc-field-group">
					<label for="mce-EMAIL">Emailadres </label>
					<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL" />
					<input type="submit" value="Verstuur" name="subscribe" id="mc-embedded-subscribe" class="btn btn-red btn-lg" style="margin:0;" />
				</div>
					<div id="mce-responses" class="clear">
						<div class="response" id="mce-error-response" style="display:none"></div>
						<div class="response" id="mce-success-response" style="display:none"></div>
					</div> 
					<div style="position: absolute; left: -5000px;"><input type="text" name="b_c86126b719e2bc789a7b8f012_06daed3eda" tabindex="-1" value=""></div>
				</form>
			</div>
		</div>
		-->
    </div>
</div>
<?php
} else {
	echo '<div class="normal-content">
	<div class="pure-g">';
    $user = Authentication_Controller::GetAuthenticatedUser();
    $instant = new Group();
    $myGroups = $instant->GetMyGroup($user->id);
    if(count($myGroups) > 1){
        echo '
		<div class="l-box pure-u-3-4">
        <h1> '.$lang['MY_GROUPS'].'</h1>
        <table class="index-table">
            <tr><th>'.$lang['GROUP'].'</th><th class="balance-column onepx">'.$lang['BALANCE'].'</th><th class="balance-column onepx">'.$lang['TODAY'].'</th><th class="onepx">'.$lang['TASK'].'</th></tr>'; //
            $rowBack = 0 ;
            foreach( $myGroups as $group )
            {
                echo '<tr><td '.($rowBack != 0 ? 'class="datacelltwo"' : '').'><a href="'.$settings['site_url'].'group/'.$group->id.'/"><h3>'.$group->name.'</h3></a>';
                if($group->modules == 0){
                    echo ' (<a href="'.$settings['site_url'].'settings-group/'.$group->id.'/">'.$lang['ACTIVATE_MODULES'].'</a>)';
                }
                echo '</td>'.'<td class="balance-column '.($rowBack != 0 ? ' datacelltwo' : '').'">';
                if($group->CheckCost()){
                    $saldo = $DBObject->GetUserSaldo($group->id,$user->id);
                    echo '<a href="'.$settings['site_url'].'cost/'.$group->id.'/">'.($saldo >=  0 ? '<span class="txtPositive">' : '<span class="txtNegative">').$group->currency.' '.$saldo.'</span></a>';
                } else { echo '-'; }
                echo '</td>'.'<td class="balance-column '.($rowBack != 0 ? ' datacelltwo' : '').'">';
                if($group->CheckDinner()){
                    $group->ShowDinnerByUserDate($group,$user);
                } else { echo '<a href="'.$settings['site_url'].'dinner/'.$group->id.'/" class="nothingimg">&nbsp;</a>'; }
                echo '</td>'.'<td '.($rowBack != 0 ? 'class="datacelltwo"' : '').'>';
                if($group->CheckTask()){
                    $mondaydate = date('Y-m-d', strtotime('last Monday + 0 week'));
                    $usertasks = $group->getUserTasksByWeek($mondaydate);
                    if(isset($usertasks[$user->id])){
                        echo '<a href="'.$settings['site_url'].'tasks/'.$group->id.'/"><span class="txtPositive">'.$usertasks[$user->id]->name.'</span></a>';
                    }
                    else{
                        echo '<a href="'.$settings['site_url'].'tasks/'.$group->id.'/"><span class="txtNegative">'.$group->GetTaskByUserWeek($user->id).'</span></a>';
                    }
                } else { echo '-'; }
                if($rowBack == 0){
                    $rowBack = 1;
                }
                else{
                    $rowBack = 0;
                }
                echo '</td></tr>';
            }
            ?>
        </table>
        </div>

        <aside class="l-box pure-u-1-4">
            <div class="box">
                <p><?php echo $lang['NEW_FREE_GROUP'].'</p>
                <a href="'.$settings['site_url'].'new-group/" class="btn btn-white">'.$lang['NEW_GROUP'].'</a>';
				if($user->surname == null || $user->address == null || $user->zipcode == null || $user->city == null){
					echo '<a href="'.$settings['site_url'].'settings/" class="btn btn-white">'.$lang['UPDATE_ACCOUNT'].'</a>';
				}
				?>
			</div>
        </aside>
    <?php
    }
    else if(COUNT($myGroups) == 1){
        header ('Location: '.$settings['site_url'].'group/'.$myGroups[0]->id.'/');
    }
    else if(COUNT($myGroups) == 0){

        echo '<div class="l-box pure-u-3-4">
            <h1>'.$lang['WELCOME'].' '.$user->firstName.'</h1>
            <p>'.$lang['CHOOSE_OPTIONS'].' <br />
             <a href="'.$settings['site_url'].'settings/">'.$lang['EDIT_ACCOUNT_NO_GROUPS'].'</a></p>


        </div>
        <aside class="l-box pure-u-1-4">
            <div class="box">
                <p>'.$lang['NEW_FREE_GROUP'].'</p>
                <a href="'.$settings['site_url'].'new-group/" class="btn btn-white">'.$lang['NEW_GROUP'].'</a>';
        if($user->surname == null || $user->address == null || $user->zipcode == null || $user->city == null){
            echo ' <a href="'.$settings['site_url'].'settings/" class="btn btn-white">'.$lang['UPDATE_ACCOUNT'].'</a>';
        }

            echo '</div>
        </aside>';
    }
	echo '	</div>
</div>';

}

require_once("inc/footer.inc.php");
?>