<?php
header("Content-Type: text/html; charset=utf-8");

if (!isset($_SESSION)) {  @session_start(); }
              switch ($_SERVER['HTTP_ORIGIN']) {
                  //case 'http://pro.sppp.com':
                  case 'https://xaviorhsu.github.io/':
                        header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
                        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
                        header('Access-Control-Max-Age: 1000');
                        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
                  break;
              }
/** ------------------------------------------------------------------------------------------------------------------------------------- **/
function sendmail($to,$email_subject,$email_message,$from,$cc=null) {
  $charset = "utf-8";
  $from = '=?'.$charset.'?B?'.base64_encode($from).'?='.'<postmaster@localhost>';

  $headers = "From: $from\r\nMIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8\r\nContent-Transfer-Encoding: base64\r\n";

  if ($cc) {  $headers .="CC: $cc\r\n"; }

  $email_subject = '=?'.$charset.'?B?'.base64_encode(str_replace("\r", '', str_replace("\n", '', $email_subject))).'?=';
  $email_message = chunk_split(base64_encode(str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $email_message)))))));

  //$ret= true;      
  $ret=mail($to, $email_subject ,$email_message, $headers);
  return $ret;
}
/** ------------------------------------------------------------------------------------------------------------------------------------- **/
function chk_act_pws($atps){        //chk act$pws of input data
  /** $name="";
  $nam21="5989滄華,5989文秀,9541亞琛,9541筱婷,9117瓊惠,";
  $nam22="1835淵華,6210春嬌,1835珮琦,1835明璋,1835宇琛,";
  $nam23="9443慧華,9443固宇,9443兆立,9443旭立,";
  $nam41="4351扶英,7160立彥,7180家章,0210立美,3400文俊,";
  $nam42="1452仁華,1452楊華,1452士翔,1357立潔,1357炳森,1452煥凱,1452璧玲,";
  $nam43="6569ginger,6569alex,6569ivy,6569jeff,";
  $nam44="7153ines,7153wesley,";
  $nam45="1239永華,1068台珍,9061立倫,";
  $nam46="6135adnil,6135hope,1104立豪,0127立玲,9604立凡,3161立奇,6135立昀,";
  $nam47="8888guestVIP";
  $name=$nam21.$nam22.$nam23.$nam41.$nam42.$nam43.$nam44.$nam45.$nam46.$nam47;
  $straa=urlencode($name);  echo($name);   echo($straa);  $load_item = explode(",",$name); **/
  $stritems  = urldecode(file_get_contents("hitems.txt"));
  $load_item = explode(",",$stritems);
  $_SESSION["act_name"] = 0 ;
  foreach($load_item as $key => $value)
      { if($atps==$value) { $_SESSION["act_name"] = substr($value,4);
                            if(empty($_COOKIE["hitems"])) { setcookie("hitems","true",time()+5);  }       //設定已登入
                            return $_SESSION["act_name"]; }                                          
      }
  return $_SESSION["act_name"];
}
/** ================================================================================================================== **/
if (isset($_REQUEST["opt"])) {
    $sw=$_REQUEST["opt"]; 
    switch($sw)  {
        case "act_chk" :   //act_chk data $pws=aa(2)+mm(2)+pws(4)+dd(2)
              $act_name=$_REQUEST["name"]; $pws=$_REQUEST["pass"]; $act_pws=substr($pws,2,4);
              $msg ="0,【輸入帳密錯誤！】";
              if(strlen($pws)==8&&substr($pws,0,2)==date('m')&&substr($pws,6)==date('d'))                      
                    { 
                      $actpws=$act_pws.$act_name;
                      $result=chk_act_pws($actpws);
                      if ($result) $msg = "1,".$result;
                      else $msg.="~";
                    }
              if(isset($_REQUEST["js_cb"]))
                   {
                      $arr1 = array ('Ans'=>$msg);
                      //header("Content-Type: application/json");
                      //組成 $_GET['callback']({key:value})
                      print $_REQUEST['js_cb'].'('.json_encode($arr1,JSON_NUMERIC_CHECK).')';
                   }
              else {  print ($msg);  }
              exit;            
        break;
        case "upl_fil" :              
              $fmsg='0,【檔案上傳中！】'; $outmsg=""; $filename=$_REQUEST["fn"];
              if (isset($_FILES[$filename])) {                            //儲存上傳的檔案
                  $fmsg='&nbsp;《上傳檔案資訊》<br/>';
                  $fmsg.='<div align="left">檔 名：'.$_FILES[$filename]["name"].'<br/>';
                  $fmsg.='種 類：'.$_FILES[$filename]["type"].'<br/>';
                  $fmsg.='尺 寸：'.round($_FILES[$filename]["size"]/1024).' KB</div>';
                  //if (copy($_FILES["file"]["tmp_name"],"uploads/".$_FILES["file"]["name"]))
                  if (move_uploaded_file($_FILES[$filename]["tmp_name"],"uploads/".date(ds).$_FILES[$filename]["name"]))
                        { $outmsg='【檔案傳錄成功！】<br/>';  $fmsg="4,".$outmsg.$fmsg; }
                  else  { $outmsg='【檔案傳成錄敗！】\n';  $fmsg="5,".$outmsg.$fmsg; }
              }
              //else $fmsg='0,【檔案上傳未成】<br/>　notset ($_FILES['.$filename.'])';
              else $fmsg='5,【檔案未上傳成！】\n';              
              $arr1 = array ('Ans'=>$fmsg); print (json_encode($arr1,JSON_NUMERIC_CHECK));
              exit;            
        break;
        case "snd_mal" :   //send email chk include photo upload
              if ($_REQUEST["item"]=="1") {$fn="問題內容"; $fg=nl2br($_REQUEST["tt"]); }
              if ($_REQUEST["item"]=="2") {$fn="相片內容"; $fg=$_REQUEST["tt"]; }
              
              $body = <<<EOT
<table  width="508" border="1" bordercolor="#99CCCC" align="left" cellpadding="0" cellspacing="0" style="font-size:0.87em; float:left">	
            <tr bgcolor="#99CCCC"><td align="center" height="23" colspan="5" style="font-size: 14px;color:darkred;">有關的問題與增修個人資料表</td></tr>
            <tr>
              <td width="18%" height="23">&nbsp;您的姓名：</td>
              <td width="19%">{$_REQUEST['nam2']}</td>
              <td width="10%">&nbsp;輩份：</td>
              <td width="20%">&nbsp;{$_REQUEST['rank']}世(代)</td>
              <td width="33%" align="center">{$_REQUEST['Dd0']}</td>
            </tr>
            <tr>
              <td colspan="3" height="23">&nbsp;{$fn}：</td>              
              <td width="20%">&nbsp;<input type="checkbox" value="1" />連絡手機：</td>
              <td width="33%">{$_REQUEST['cellphone']}</td>
            </tr>            
            <tr>
              <td colspan="3" rowspan="4" valign="top">{$fg}</td>
              <td height="23">&nbsp;<input type="checkbox" value="2" />連絡市話：</td>
              <td>{$_REQUEST['telephone']}</td>
            </tr>
            <tr>
              <td height="23">&nbsp;<input type="checkbox" value="3" />【LINE】：</td>
              <td>{$_REQUEST['cellphon2']}</td>
            </tr>
            <tr>
              <td height="23" >&nbsp;<input type="checkbox" value="4" />電子信箱：</td>
              <td>{$_REQUEST['email']}</td>
            </tr>
            <tr>
              <td height="23">&nbsp;<input type="checkbox" value="5" />【ＦＢ】：</td>
              <td>{$_REQUEST['emai2']}</td>
            </tr>
            <tr bgcolor="#99CCCC"><td height="23" colspan="5"><hr size="2" style="color:orange;" noshade="noshade" /></td></tr>
</table>
EOT;
              $resp=0;
              if($_REQUEST['cellphone']||$fg||$_REQUEST['telephone']||$_REQUEST['cellphon2']||$_REQUEST['email']||$_REQUEST['emai2'])
                { $resp=sendmail('adnil.hsu@gmail.com','【徐氏歷代子孫在台族譜】增修資料',$body,'【徐氏十九世辛貴公后歷代子孫在台族譜】網站',''); }
              $outmsg2='2,【資料已寄出，我們將會為您服務盡速處理！】';
              $outmsg3='3,【資料寄送有誤, 請直接聯絡人員處理！】';
              $responseText=($resp)?$outmsg2:$outmsg3;
              if(isset($_REQUEST["js_cb"]))
                   {
                      $arr1 = array ('Ans'=>$responseText);
                          //header("Content-Type: application/json");
                          //組成 $_GET['callback']({key:value})
                      print $_REQUEST['js_cb'].'('.json_encode($arr1,JSON_NUMERIC_CHECK).')';
                   }
              else {  print ($responseText);  }
              exit;
        break;
    } 
}
?>