<?php
namespace babirondo\REST;

class RESTCall
{



    function CallAPI($method, $url, $data = false)
    {
      //  require_once("include/globais.php");
//        $Globais = new Globais();

        $curl = curl_init();
  //      if ($Globais->env =="local")
        $verbose= 1;
        $debug = null;
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data){
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                }
                if ($verbose) $debug.= " <BR><FONT COLOR='red'> curl -H 'Content-Type: application/json' -X $method -d '$data' $url </FONT>";

            break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS,http_build_query(json_decode($data)));

                if ($verbose) $debug .=  " <BR><FONT COLOR='green'> curl -H 'Content-Type: application/json' -X $method -d ' $data' $url </></FONT> ";
                break;

            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

                if ($verbose) $debug .=  " <BR><FONT COLOR='green'> curl -H 'Content-Type: application/json' -X $method -d '$data' $url </FONT> ";
                break;

            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
                if ($verbose) $debug.=  " <BR> <FONT COLOR='#9acd32'>   $url </FONT> ";
                if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));

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

            $debug = $debug."($total1)" ;

            if ($http_code != 200 ||  $parseResposta == "falso" ) echo $debug." <- Curl (HTTP CODE: <font color=red>$http_code</font>) PARSE (<font color=red>$parseResposta</font>) = <TEXTAREA>".var_export($result,1)."</TEXTAREA> ";
            else IF ($verbose == 1) echo $debug." <- Curl (HTTP CODE: $http_code): ";

            if  (json_last_error() == JSON_ERROR_NONE){
                curl_close($curl);
                $array_retorno_api = json_decode( $result , true);

                $array_retorno_api["babirondo/rest-api"]["http_code"]  = $http_code;
                return $array_retorno_api ;
            }
            else{
                $result["babirondo/rest-api"]["httpcode"] = $http_code . "erro";

                curl_close($curl);
                return  $result;

            }

        }
        catch (Exception $e) {
            echo $debug. $e->getMessage()." Exception Curl: HTTPD CODE:$http_code ";

            return false;
        }
    }

}
