<?php
//
class __checkedItems{
    public static $checkedItemids; //array({'itemid'=>1},{'itemid'=>2})
    public static $items;

    static function setItems($restaurantid=0){
        if($restaurantid){
          $t=get_data("select * from items where restaurantid=$restaurantid");
          foreach($t as $d){
              $items[$d['itemid']]=$d;
          }
          self::$items=$items;
          return $items;
        }
        return false;
    }

    static function setCheckedItemids($checkedItemids){
         self::$checkedItemids=$checkedItemids;
    }

    static function showMenuList($ItemNum,$span="<br />",$itemids=null){
        $html='';
        if(!$itemids){$itemids=self::$checkedItemids; }
        foreach($itemids as $k=>$itemid){
             $html.=$items[$itemid]['title'].' x '. $ItemNum[$k] .' &nbsp;&nbsp;&nbsp;&nbsp;  '.$ItemNum[$k]*$items[$itemid]['price'] . $span ;
        }
        return $html;
    }
    

    
}