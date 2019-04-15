<?php
namespace babirondo\REST;

class RESTCall
{
  private $username;
  private $password;

  function __construct($username="", $password=""){
    $this->username = $username;
    $this->password = $password;
  }



  function IdentaRetorno( $msg ){
    $linhas = explode("<quebralinha>", $msg)  ;

    $novaMsg = null;
    $tabs = 1;

    for ($i=0; $i<count($linhas);$i++){
      if (substr($linhas[$i],0,5) == "XTID:") $tabs++;

      $novaMsg .= str_repeat ( ">>>>" , $tabs ).  $linhas[$i];
    }

    return $novaMsg;//"<FONT color=red></font>";
  }

  function CallAPI($method, $url, $data = false,  $verbose='ERRO')
  {

    $decodeError[0]="JSON_ERROR_NONE";
    $decodeError[1]=" JSON_ERROR_DEPTH";
    $decodeError[2]=" JSON_ERROR_STATE_MISMATCH";
    $decodeError[3]=" JSON_ERROR_CTRL_CHAR";
    $decodeError[5]=" JSON_ERROR_UTF8";
    $decodeError[4]=" JSON_ERROR_SYNTAX";
    $decodeError[7]=" JSON_ERROR_INF_OR_NAN";
    $decodeError[6]=" JSON_ERROR_RECURSION";
    $decodeError[8]=" JSON_ERROR_UNSUPPORTED_TYPE";

      //$verbose = [NUNCA, SEMPRE, ERRO];
      $verbose = strtoupper($verbose);

      $curl = curl_init();
      $debug = null;

      //echo "<BR>$url $verbose $method" ;
      if ($data != false and !is_array(   json_decode($data,1)  )){
        echo "<BR><BR><font color=#ff0000>$method = $url =====  Argumento esperado tipo Array mas recebeu ".gettype($data)." ($data)</font>";
        return false;
      }
      $data_validado =  $data;

      if ($this->username != "" && $this->password != ""){
        curl_setopt($curl, CURLOPT_USERPWD, $this->username.":".$this->password);
      }


      switch ($method)
      {
          case "POST":
              curl_setopt($curl, CURLOPT_POST, 1);

              if ($data){
                  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
              }
              $debug.= "curl -H 'Content-Type: application/json' -X $method -d '$data' $url ";

          break;

          case "PUT":
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
              curl_setopt($curl, CURLOPT_POSTFIELDS,http_build_query(json_decode($data)));

              $debug .=  "curl -H 'Content-Type: application/json' -X $method -d ' $data' $url   ";
              break;

          case "DELETE":
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

              $debug .=  "curl -H 'Content-Type: application/json' -X $method -d '$data' $url  ";
              break;

          default:
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
              $debug.=  " $url   ";
            //  if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));

     }

      try {
          ini_set('display_errors', '1');

          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_TIMEOUT, 10);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

          $inicio1 = microtime(true);
          $result = curl_exec($curl);
          $total1 = microtime(true) - $inicio1;

          $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
          $teste_json_result = $result;

          $parseResposta = ((json_decode( $result , true))? "verdadeiro" : "falso" );
          //var_dump($result);exit;

          /*
          if ($http_code != 200 ||  $parseResposta == "falso" ){
            $debug = "
            HOST: ".gethostname ( )." <BR>
            Endpoint: $url <BR>
            Verb: $method<BR>
            HTTP CODE: <font color=red>$http_code</font> <BR>
            PARSE (<font color=red>$parseResposta</font>) <BR>
            Tempo Execucao: ($total1) <BR>
            Payload: <TxEXTAREA cols=90 rows=4>".   print_r ($data_validado,1)  ."</TEXTAREA> <BR>
            Retorno da API Call: <TxEXTAREA cols=90 rows=4>".print_r($result,1)."</TEXTAREA><BR>
            Comando: <TxEXTAREA cols=90 rows=1>$debug</TEXTAREA>";
          }
          else{
            $debug = "
            HOST: ".gethostname ( )." <BR>
            Endpoint: $url <BR>
            Verb: $method<BR>
            HTTP CODE: <font color=red>$http_code</font> <BR>
            PARSE (<font color=red>$parseResposta</font>) <BR>
            Tempo Execucao: ($total1) <BR>
            Payload: <TxEXTAREA cols=90 rows=4>".   print_r ($data_validado,1)  ."</TEXTAREA> <BR>
            Retorno da API Call: <TxEXTAREA cols=90 rows=4>".print_r($result,1)."</TEXTAREA><BR>
            Comando: <TxEXTAREA cols=90 rows=1>$debug</TEXTAREA>";
         }
         */

         $result_identado = $this->IdentaRetorno( $result );

         $xtid=uniqid();
         $debug = "
         <quebralinha>XTID:$xtid<BR>
         <quebralinha>Verbose: $verbose <BR>
         <quebralinha>Endpoint: $url <BR>
         <quebralinha>HOST: ".gethostname ( )." <BR>
         <quebralinha>Verb: $method<BR>
         <quebralinha>HTTP CODE: <font color=red>$http_code</font> <BR>
         <quebralinha>PARSE (<font color=red>$parseResposta</font>) <BR>
         <quebralinha>Tempo Execucao: ($total1) <BR>
         <quebralinha>Comando: <TxEXTAREA cols=90 rows=1>$debug</TEXTAREA><BR>
         <quebralinha>Payload: <TxEXTAREA cols=90 rows=4>".   print_r ($data_validado,1)  ."</TEXTAREA> <BR>
         <quebralinha>Retorno da API Call:<BR> <TxEXTAREA cols=90 rows=4>".print_r($result_identado,1)."</TEXTAREA>";



         if ( ($http_code != 200 ||  $parseResposta == "falso" && ($verbose == "ERRO" || $verbose == "SEMPRE" )) || $verbose == "SEMPRE" )
          echo "\n <BR><BR> $debug <BR>";

          if  (json_last_error() == JSON_ERROR_NONE){
             //SUCESSO
              curl_close($curl);
              $array_retorno_api = json_decode( $result , true);

              $array_retorno_api["babirondo/rest-api"]["http_code"]  = $http_code;
              $retorno  = $array_retorno_api ;
          }
          else{
            //DEU ERRO
              ECHO  "<br>$url => HTTP CODE $http_code -- PARSE $parseResposta  --- VERBOSE $verbose <BR>";
            //  $result["babirondo/rest-api"]["httpcode"] = $http_code . "erro";
              echo "XTID: $xtid -> DEU ERRO NA CHAMADA DA API ".$decodeError[json_last_error()]." $verbose $http_code $parseResposta ";

              curl_close($curl);
              $retorno  = $result ;
          }


          return $retorno;
      }
      catch (Exception $e) {
          echo $debug. $e->getMessage()." Exception Curl: HTTPD CODE:$http_code ";

          return false;
      }
  }

}
