<?php
/**
 * 忘了何時寫的東西，主要是記算fb like數量 from fb graphic
 * 但single thread要讀大量資料出來就造成memory not enough的問題
 * 故想出這招，取max最高id，再分批處理，如果加上multiple thread，會處理更快
 */
class FBStatProcessor 
{
    public function countPoiFacebookLiked($args) 
    {

        if (!isset($args[0])) {
            echo "The command requires at least one argument.\n";
            exit(0);
        }

        $webUiServerUrl = rtrim($args[0], "/"); // remove the ending slash if any

        $poisAR = App::db->run('select max(id) as id from poi');
        $maxID = $poisAR->id;

        $logMsg = '';
        $executeCnt = $maxID / EXECUTE_CNT;
        $cnt = 0;

        while ($maxID > 0) {
            $endID = $maxID;
            $maxID = $maxID - EXECUTE_CNT;
            $startID = $maxID;
            $logMsg .= "Processing POIs from ".$startID." to ".$endID."\n";

            $cnt = $this->_countPoiFacebookLiked($startID, $endID, $webUiServerUrl);
            $logMsg .= = "Done ".$cnt. "\n";
        }
    }

    private function _countPoiFacebookLiked($startID, $endID, $webUiServerUrl) 
    {
        $poisAR = App::db->getAll('poi');
        $cnt = 0;

        if (!is_null($poisAR)) {

            foreach ($poisAR as $pIdx => $poiObj) {
                $pid = $poiObj->id;
                $gid = $poiObj->ownerGID;

                $actUrl = $webUiServerUrl . "/pois/$pid?gid=$gid";

                // Replace url to be a real production cases for testing only.
                if (DEBUG)
                    $actUrl = "http://www.goyourlife.com/pois/431253?gid=79089";

                $fbStatusUrl = "http://api.facebook.com/restserver.php?method=links.getStats&urls=".urlencode($actUrl);
                $xml = file_get_contents($fbStatusUrl);

                $dom = new DOMDocument;
                $dom->loadXML($xml);
                $likeElements = $dom->getElementsByTagName('total_count');

                if (isset($likeElements->item(0)->nodeValue)) {
                    $like = $likeElements->item(0)->nodeValue;
                    $poiStatsAR = ProfilePoiGpsDataStats::model()->find('id=:id', array(":id" => $pid));
                    if (!is_null($poiStatsAR)) {
                        $poiStatsAR->facebookLiked = $like;
                        $poiStatsAR->save();
                        if (DEBUG)
                            echo '.';
                        $cnt++;
                    }
                }
            }
        }
        unset($poisAR);
        return $cnt;
    }
}    
