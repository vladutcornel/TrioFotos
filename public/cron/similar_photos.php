<?php
/**
 * Description of similar_photos
 *
 * @author cornel
 */
class similar_photos {
    function main(){
        $query = 'SELECT * FROM votes ORDER BY photo, user';
        $votes = trio\db\Model::$db->query($query, false);
        // populate item matrix
        $items = array();
        foreach ($votes as $vote){
            if (!isset($items[$vote->photo]))
                $items[$vote->photo] = array();
            $items[$vote->photo][$vote->user] = $vote->vote;
        }
        // populate average array
        $avgs = array();
        $similar = array();
        foreach ($items as $photo=>$row){
            $sum = 0;
            foreach ($row as $vote){
                $sum+= $vote;
            }
            
            $avgs[$photo] = $sum/count($row);
            $similar[$photo] = array();
        }
        
        // calculate similarities for each photo pair
        $len = count($items);
        $keys = array_keys($items);
        $queryes = array();
        for ($i = 0; $i < $len; $i++)
        {
            for ($j = $i+1; $j < $len; $j++)
            {
                $f1 = $items[$keys[$i]];
                $f2 = $items[$keys[$j]];
                
                $sum = 0;
                $sum_x = 0;
                $sum_y = 0;
                foreach ($f1 as $user=>$vote){
                    if (!isset($f2[$user])){
                        continue;
                    }
                    $x = ($vote - $avgs[$keys[$i]]);
                    $y = ($f2[$user] - $avgs[$keys[$j]]);
                    $sum+= $x  * $y ;
                    $sum_x+= $x*$x;
                    $sum_y+= $y*$y;
                    //echo 'Bazinga!', $sum_x, $sum_y;
                }
                
                if ($sum_x == 0 || $sum_y == 0){
                    $r = -1;
                } else {
                    $r = (float)$sum/(sqrt($sum_x) * sqrt($sum_y));
                }
                
                $similar[$keys[$i]][$keys[$j]] = $r;
                $similar[$keys[$j]][$keys[$i]] = $r;
                $queryes[]=sprintf('(%d,%d,%f)',
                        $keys[$i],$keys[$j], $r);
                $queryes[]=sprintf('(%d,%d,%f)',
                        $keys[$j],$keys[$i], $r);
                
            }
        }
        $query = 'REPLACE INTO similar_photos(photo1, photo2,score) VALUES '.implode(',', $queryes);
        trio\db\Model::$db->query($query);
        //var_dump($items,$avgs, $similar);
    }
}