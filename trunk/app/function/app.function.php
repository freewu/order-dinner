<?php


    function sql_insert($table,$data){
        $sql="Insert into $table ";
        $sql_keys='';
        $sql_vals='';
        foreach($data as $k=>$v){
            $sql_keys.="".$k.",";
            $sql_vals.="'".s($v)."',";
        }
        $sql_keys=rtrim($sql_keys,',');
        $sql_vals=rtrim($sql_vals,',');
        return $sql.' ('.$sql_keys.') values('.$sql_vals.');';
    }
    
    function sql_update($table,$data,$whereData){
        $sql="Update $table";
        $sql_set='';
        foreach($data as $k=>$v){
            $sql_set.="".$k."='".s($v)."',";
        }
        $sql_where='';
        foreach($whereData as $k=>$v){
            $sql_where.="".$k."='".$v."' And ";
        }
        return $sql.' set '.rtrim($sql_set,',').' Where '.rtrim($sql_where,'And ');
    }

    class orderHelper{
      public static $items;
      //取一行订单的文字　信息
      static function getOrderTxt($oitem){
        if(!is_array($oitem)) return false;
            if($oitem['status']==1){
                  $oitem['status_txt']='已结算';
            }else{
                $oitem['status_txt']='预算中';
            }
            $oitem['book_txt']=get_var('select title from books where bookid='.$oitem['bookid']);
            $payer=get_line('select * from users where userid='.$oitem['payerid']);
            $oitem['payer_txt']=$payer['usercn'].','.$payer['useren'];
            $restaurant=get_line('select * from restaurants where restaurantid='.$oitem['restaurantid']);
            $oitem['restaurant_txt']=$restaurant['title'].','.$restaurant['tel'];
            $detail_arr=json_decode($oitem['detail']);
            $items=self::getItems($oitem['restaurantid']);
            $oitem['detail_txt']=self::getNumTxt($detail_arr,$items);
            $oitem['users_txt']=self::getUserTxt(json_decode($oitem['users_detail']));
         return $oitem;   
      }
      static function getNumTxt($numArr,$items){
            if(!$numArr) return '';
            $r='<table class="menutable"><tr><th class="title"></th><th class="price"></th></tr>';
            foreach($numArr as $itemid=>$num){
                $r.='<tr><td>'. $items[$itemid]['title'].' x '. $num .' </td><td>'.$num*$items[$itemid]['price'] .'</td></tr>' ;
            }
            $r.='</table>';
            return $r;
      }
      //取餐厅菜单　
      static function getItems($restaurantid=0){
        if($restaurantid){
          $t=get_data("select * from items where restaurantid=$restaurantid");
          foreach($t as $d){
              $items[$d['itemid']]=$d;
          }
          return $items;
        }
        return false;
      }

      static function getUserTxt($oUsers){
         $usersData=self::getUsers();
         $rUsers='';
         foreach($oUsers as $userid){
            //$oUsers[$userid]=$usersData[$userid];
            $rUsers.=$usersData[$userid]['usercn']."(".$usersData[$userid]['useren'].")"." ;";
         }
         return $rUsers;
      }

      static function getUsers($oUsers){
         $users=get_data('select * from users ');
         $usersData=array();
         foreach($users as $k=>$userItem){
            $usersData[$userItem['userid']]=$userItem;
         }
         unset($users);
         return $usersData;

      }
    }
    /***
     * 用户登录相关
     */
    class userHelper{
        //是否登录 并有权限
        static function islogin($level=1){
             if(ss('userid')&&(ss('level')&$level)) return true;
             return false;
        }
        
        static function logout(){
             session_destroy();
        }
    }
    /***
     *  菜单
     */
    class menuHelper{
        static function menuStatistic($new,$old=null){
            if(empty($new)) return 0;
            if($new==$old) return 0;
            if($new){
                $new_arr=json_decode($new,1);
            }
            if($old){
                $old_arr=json_decode($old,1);
                foreach($old_arr as $ok=>$ov){
                    if(isset($new_arr[$ok])){
                        unset($new_arr[$ok]);
                        unset($old_arr[$ok]);
                    }
                }
            }
            $new_items_used=get_data("select itemid,used from items where itemid in (".implode(',',array_keys($new_arr)).")");
            foreach($new_items_used as $nd){
                run_sql("update items set used=".($nd['used']+1)." where itemid='".$nd['itemid']."'");
            }
            foreach($old_arr as $ok =>$ov){
                run_sql("update items set used=used-1 where itemid='".$ok."'");
            }
        }
        
    }