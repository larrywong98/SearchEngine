<?php
include "SpellCorrector.php";

// header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers:POST, GET, OPTIONS');
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
ini_set ('memory_limit', -1);

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;

$results = false;
$customid=1;

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
	$base_url = "https://";   
else  
	$base_url = "http://";
$base_url.= ($_SERVER['HTTP_HOST']); 



if ($query)
{

  $spell_check_enabled = false;

  $old_query = $query;
	$new_query ="";
	foreach(explode(" ", $query) as $word):
		$new_query .= SpellCorrector::correct($word) . " ";
	endforeach;
  
	$new_query = rtrim($new_query);
	
	// if (!isset($_GET['SpellCheckOff']))
	// {
	// 	if ($old_query != $new_query)
	// 	{
	// 		$spell_check_enabled = True;
	// 		$query = $old_query;
	// 	}
	// }

  require_once('Apache/Solr/Service.php');
  $solr = new Apache_Solr_Service('127.0.0.1', 8983, '/solr/csci572hw4');
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  $usePageRank=0;
  $rankmethod="";
  if($_REQUEST['rank']==0){
    $rankmethod="Lucene";
    $usePageRank="";
  }else{
    $rankmethod="Pagerank";
    $usePageRank="pageRankFile desc";
  }
  $additionalParameters=array(
    "sort"=>$usePageRank
  );
  try
  {
    $results = $solr->search($query, 0, $limit,$additionalParameters);
  }
  catch (Exception $e)
  {
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js" integrity="sha256-6XMVI0zB8cRzfZjqKcD01PBsAy3FlDASrlC8SxCpInY=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
  </head>
  <body>
    <div class="bg-light bg-darken-md">
      <div class="row">
        <div class="w-50 text-center col-md-8 offset-md-3">
          <p class="fs-1">Search Engine Ranking Algorithms Compare</p>
          <form accept-charset="utf-8" method="get">
            <div class="mb-2">
              <p class="fs-4" for="search">What do you want to search</p>
              <input type="text" name="q" class="form-control" id="q" placeholder="*:*" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
              <script>
                var suggestions = [];
                var query = "";
                $(function() {
                    $("#q").autocomplete({
                        minLength:1,   
                        delay:500,   
                        source: function(request, response) {
                            response(suggestions);
                        }
                    });
                });
                $("#q").keyup(function(){
                  suggestions = [];
                  query = document.getElementById("q").value;
                  // querystr=escape(query);
                  // alert(query.indexOf(' '));
                  // if(querystr.indexOf(' ')>=0){
                  //   alert(querystr.indexOf(' '));
                  // }
                  var strURL = "";
                  var strURL2="";
                  if(query.indexOf(' ')>=0){
                    strURL = "http://127.0.0.1:8983/solr/csci572hw4/suggest?q=".concat(query.substring(0,query.indexOf(" ")),"&wt=json&rows=6");
                    strURL2="http://127.0.0.1:8983/solr/csci572hw4/suggest?q=".concat(query.substring(query.indexOf(" ")+1,query.length),"&wt=json&rows=6");
                  }else{
                    strURL = "http://127.0.0.1:8983/solr/csci572hw4/suggest?q=".concat(query,"&wt=json&rows=6");
                    strURL2="http://127.0.0.1:8983/solr/csci572hw4/suggest?q=&wt=json&rows=6";
                  }
                  var xmlHttpReq;
                  xmlHttpReq = new XMLHttpRequest();
                  xmlHttpReq.open('GET',strURL, true);
                  xmlHttpReq.send(null);
                  xmlHttpReq.onreadystatechange = function() {
                    if (xmlHttpReq.readyState == 4 && xmlHttpReq.status == 200) {

                      // alert(strURL2);
                      //second request
                      var xmlHttpReq1;
                      xmlHttpReq1 = new XMLHttpRequest();
                      xmlHttpReq1.open('GET',strURL2, true);
                      xmlHttpReq1.send(null);
                      xmlHttpReq1.onreadystatechange = function() {
                        if (xmlHttpReq1.readyState == 4 && xmlHttpReq1.status == 200) {
                          rsp=JSON.parse(xmlHttpReq.responseText);
                          rsp2=JSON.parse(xmlHttpReq1.responseText);
                          if(rsp2["suggest"]["suggest"][query.substring(query.indexOf(" ")+1,query.length)]!=undefined){
                            var suggestion_object = rsp["suggest"]["suggest"][query.substring(0,query.indexOf(" ")).trim()]["suggestions"];
                            var suggestion_object2= rsp2["suggest"]["suggest"][query.substring(query.indexOf(" ")+1,query.length)]["suggestions"];
                            var i=0;
                            var f=0;
                            
                            while(i<5)
                            {
                              if(suggestion_object2.length==0){
                                suggestions.push(suggestion_object[i].term);
                              }else{
                                if(suggestion_object2[i].term=="y8aj3r"){
                                  suggestions.push(suggestion_object[f].term+" yieldmo");
                                }else{
                                  suggestions.push(suggestion_object[f].term+" "+suggestion_object2[i].term);
                                }
                               }
                              i++;
                            }
                          }else{
                            query=query.trim();
                            var suggestion_object = rsp["suggest"]["suggest"][query]["suggestions"];
                            suggestion_object.forEach(function (item, index) {
                                if(index!=suggestion_object.length-1){
                                  // suggestion_object.length-1){
                                  
                                  suggestions.push(item.term);
                                }
                            });
                          }
                          
                        }
                      }

                      
                    }
                  }
                });
              

		        </script>
            </div>
            <div class="mb-4">
              <p class="fs-4">Ranking method</p>
              <select name="rank" class="form-control" id="rank">
                <option value="0">Lucene</option>
                <option value="1">PageRank</option>
              </select>
            </div>
            <div class="mb-4">
              <input type="submit" class="btn btn-primary"></input>
            </div>
          </form>
        </div>
      </div>
     
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
<?php
	// if ($spell_check_enabled)
	// {    
	// 	$spell_check_on_url = $base_url . $_SERVER['PHP_SELF'] . "?q=".$query."&rank=".$_GET['rank'];
	// 	$spell_check_off_url = $base_url . $_SERVER['REQUEST_URI']. "&SpellCheckOff=True";
	// 	echo ("<div class='ui-text text-center fs-4'> 
  //           Showing Results for <b> <a class='link' href='".$spell_check_on_url."'>". $query ."</a> </b> <br>
  //           <div class='mt-2 mb-2'>
  //             Search Instead of <b><a class='link' href='".$spell_check_off_url."'>". $old_query ."</a> </b> 
  //           </div>
	// 		    </div>");
	// }
	// else 
  if($total == 0 and $new_query)
	{
		$spell_check_on_url = $base_url . $_SERVER['PHP_SELF'] . "?q=".$new_query."&rank=".$_GET['rank'];
		echo ("<div class='ui-text text-center fs-4'> 
				No Results found. Did you mean: <b> <a class='link' href='".$spell_check_on_url."'>". $new_query ."</a> </b> </div>");
	}
?>
    <div>
      <div class="row">
        <div class="w-50 text-center col-md-8 offset-md-3">
          <p class="fs-3">
          <?php 
          if($total!=0){
            echo 'Searched:"'.$_REQUEST['q'].'" using ';
            if($_REQUEST['rank']==0){
              echo "Lucene";}
            else{
              echo "Pagerank";
            }
            echo ' ranking method';
          }
          ?></p>
          <p class="fs-5">
            <?php 
              if($total!=0){
                echo 'Results '.$start.' - '.$end.' of '.$total.' :';
              }
            ?></p>
       </div>
      </div>
    </div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
    $outputfile=fopen("output.txt","a");
    $urlname=$doc->og_url;
    if(is_null($doc->og_url)){
      $mapfile=fopen("URLtoHTML_nytimes_news.csv","r");
      while(!feof($mapfile)){
        list($name,$url)=explode(',',fgets($mapfile));
        if(strpos($doc->id,$name)){
          $urlname=$url;
          break;
        }
      }
      fclose($mapfile);
    }
    $urlname=
    fwrite($outputfile,$urlname."\n");
    fclose($outputfile);
    
?>
    <div>
      <div class="w-50 text-center col-md-8 offset-md-3">
        <table class="table table-borderless">
<?php
    // iterate document fields / values
    // foreach ($doc as $field => $value)
    // {
      // if($doc->og_url){

      // }
?>
          <tbody>
            <tr scope="row">
              <th scope="col" colspan="2" rowspan="5"><?php echo $customid; $customid=$customid+1; ?></th>
              <td rowspan="5">
                <tr scope="row">
                  <th scope="col"><?php echo htmlspecialchars("ID", ENT_NOQUOTES, 'utf-8'); ?></th>
                  <td><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></td>
                </tr>
                <tr>
                  <!-- Title -->
                  <th scope="col"><?php echo htmlspecialchars("Title", ENT_NOQUOTES, 'utf-8'); ?></th>
                  <td><a href="<?php  
                          $urlname="";
                          $urlname=$doc->og_url;
                          if(is_null($doc->og_url)){
                            $mapfile=fopen("URLtoHTML_nytimes_news.csv","r");
                            while(!feof($mapfile)){
                              list($name,$url)=explode(',',fgets($mapfile));
                              if(strpos($doc->id,$name)){
                                $urlname=$url;
                                break;
                              }
                            }
                            fclose($mapfile);
                          }
                          echo htmlspecialchars($urlname, ENT_NOQUOTES, 'utf-8'); 
                      ?>">
                        <?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8');?> 
                      </a></td>
                </tr>
                <tr>
                  <!-- URL -->
                  <th scope="col"><?php echo htmlspecialchars("URL", ENT_NOQUOTES, 'utf-8'); ?></th>
                  <td><a href="<?php  
                          $urlname="";
                          $urlname=$doc->og_url;
                          if(is_null($doc->og_url)){
                            $mapfile=fopen("URLtoHTML_nytimes_news.csv","r");
                            while(!feof($mapfile)){
                              list($name,$url)=explode(',',fgets($mapfile));
                              if(strpos($doc->id,$name)){
                                $urlname=$url;
                                break;
                              }
                            }
                            fclose($mapfile);
                          }
                          echo htmlspecialchars($urlname, ENT_NOQUOTES, 'utf-8'); 
                      ?>">
                          <?php 
                            $urlname="";
                            $urlname=$doc->og_url;
                            if(is_null($doc->og_url)){
                              $mapfile=fopen("URLtoHTML_nytimes_news.csv","r");
                              while(!feof($mapfile)){
                                list($name,$url)=explode(',',fgets($mapfile));
                                if(strpos($doc->id,$name)){
                                  $urlname=$url;
                                  break;
                                }
                              }
                              fclose($mapfile);
                            }
                            echo htmlspecialchars($urlname, ENT_NOQUOTES, 'utf-8');
                            // echo htmlspecialchars($urlname, ENT_NOQUOTES, 'utf-8'); 
                          ?>
                      </a></td>
                </tr>
                <tr>
                  <!-- Description -->
                  <th scope="col"><?php echo htmlspecialchars("Description", ENT_NOQUOTES, 'utf-8'); ?></th>
                  <td><?php
                        if(is_null($doc->og_description)){
                          echo htmlspecialchars('N/A', ENT_NOQUOTES, 'utf-8');
                        }else{
                          echo htmlspecialchars($doc->og_description, ENT_NOQUOTES, 'utf-8');
                        }
                        
                      ?></td>
                </tr>
              </td>
            </tr>
            <hr></hr>
          </tbody>
            
          
<?php
      
    // }
?>
        </table>
      </div>
    </div>
<?php
  }
?>
    </ol>
<?php
}
?>
    </div>
  </body>
</html>
