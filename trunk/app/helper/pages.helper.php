<?php
//pages_class.php
//

class helper_pages{

var $page_set=20 ;
var $page_count=0 ;
var $item_count=0;
var $start_item=0;
var $end_item=0;
var $prev_item=0;
var $next_item=0;
var $pagesize=2;
var $prepage=5;
var $nextpage=5;

function __construct($item_count,$pageset=0)
{
   if(!empty($pageset)) $this->page_set=$pageset;
   //$item_count=$this->get_articles_items_count($query);
   $this->page_count=ceil($item_count/ $this->page_set);
   $this->item_count= $item_count;
}


//******************************************************************************
//get sql without "Limit ..."
//return sql with  "Limit ..."
function sqladdlimit($cpage=1)
{
   if($cpage<1||$cpage>$this->page_count)$cpage=1;
   $cpage--;
   $query=" Limit ".($cpage * $this->page_set).",".$this->page_set ;
   return $query;
}

//******************************************************************************

//page navigation
function page_navigation($cpage=1,$url)
{
  if($cpage<1||$cpage>$this->page_count) $cpage=1;
  if($cpage>1)
  {
      $before=$cpage-1;
      $str="<a href='".$url.$before." '>Previous</a>";
  }
  if($cpage<$this->page_count){
     $next=$cpage+1;
	$str.=" <a href='".$url.$next." '>Next</a> ";
  }  
  $ib=($cpage-$this->prepage)>1?($cpage-$this->prepage):1 ;
  $ie=($cpage + $this->nextpage)<=$this->page_count ? ($cpage + $this->nextpage):$this->page_count; 
   for($i=$ib;$i<=$ie;$i++){
      if($i== $cpage)
        $str .= "&nbsp; <a href='".$url.$i."'><font color=red>".$i."</font></a>&nbsp;";
      else{
        $str .= "&nbsp; <a href='".$url.$i."'>".$i."</a>&nbsp;";
      }
   }
  $str.='&nbsp;Total Records:'.$this->item_count;
return $str;

}//end function print_page_navigation

//page navigation
function page_navigation2($cpage=1,$url)
{
  if($cpage<1||$cpage>$this->page_count) $cpage=1;
  if($cpage>1)
  {
      $before=$cpage-1;
      $str="<a href='".str_replace("{page}",$before,$url)." '>Previous</a>";
  }
  if($cpage<$this->page_count){
	$next=$cpage+1;
	$str.=" <a href='".str_replace("{page}",$next,$url)." '>Next</a> ";
  }
  $ib=($cpage-$this->prepage)>1?($cpage-$this->prepage):1 ;
  $ie=($cpage + $this->nextpage)<=$this->page_count ? ($cpage + $this->nextpage):$this->page_count; 
   for($i=$ib;$i<=$ie;$i++){
      if($i== $cpage)
        $str .= "&nbsp; <a href='".str_replace("{page}",$i,$url)."'><font color=red>".$i."</font></a>&nbsp;";
      else{
        $str .= "&nbsp; <a href='".str_replace("{page}",$i,$url)."'>".$i."</a>&nbsp;";
      }
   }
  $str.='&nbsp;Total Records:'.$this->item_count;
return $str;

}//end function print_page_navigation



//page navigation
//共 245 条主题 首页 上一页 下一页 尾页 页次：1/406页 8条主题/页 转到第页
function page_navigation3($cpage=1,$url)
{
  if($cpage<1||$cpage>$this->page_count) $cpage=1;
//form 跳转 
	$redirectUrl=str_replace("{page}","'+this.value+'",$url);
  	$strPageForm=<<<EOF
	转到第
	<Select name="page" onChange="redirect('$redirectUrl')">
EOF;
	for($i=1;$i<=$this->page_count;$i++){
		$selected="";
		if($i==$cpage){
			$selected="selected='True'";
		}
		$strPageForm.="<option value='".$i."' ".$selected." >".$i."</option>";
	}
	$strPageForm.="</Select>页";
  
  if($cpage>1)
  {
      $before=$cpage-1;
      $strBefore="<a href='".str_replace("{page}",$before,$url)." '>上一页 </a>";
  }
  if($cpage<$this->page_count){
	$next=$cpage+1;
	$strNext="<a href='".str_replace("{page}",$next,$url)." '>下一页</a> ";
  }
  
  $ib=($cpage-$this->prepage)>1?($cpage-$this->prepage):1 ;
  $ie=($cpage + $this->nextpage)<=$this->page_count ? ($cpage + $this->nextpage):$this->page_count; 
  
  $strFirst="<a href='".str_replace("{page}",$ib,$url)." '>首页</a> ";
  $strEnd="<a href='".str_replace("{page}",$ie,$url)." '>尾页</a> ";
  
  
  
  $str='共: <b>'.$this->item_count ."</b> 条主题  ".$strFirst.$strBefore.$strNext.$strEnd.
  		" 页次：<span class='STYLE14'>".$cpage."</span>/<b>".$this->page_count."</b> 页 ".
		" <b>".$this->page_set."</b> 条主题/页" .$strPageForm;
return $str;

}//end function print_page_navigation
   
    function html_pageset($url){
        $pagesets=array(20,35,50,75,100);
        $str="<select onchange='location.replace(\"$url\"+this.value)'>" ;
            foreach($pagesets as $v){
                $str.="<option value='$v' ".(($this->page_set==$v)?'selected=true':'').">$v</option>";
            }
        $str.='</select>';
        return  $str;
    }
}
?>
