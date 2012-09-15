<?php
 
namespace Tom32i\UserBundle\Twig\Extension;
 
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use \Twig_Filter_Function;
use \Twig_Filter_Method;

class AppExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'tom32i_user_twigext';
    }
    
    public function getFilters()
    {
        return array(
            'ucfirst' => new Twig_Filter_Function('ucfirst'),
            'getParam' => new Twig_Filter_Method($this, 'getParam'),
            'typeOf' => new Twig_Filter_Method($this, 'typeOf'),
            'methods' => new Twig_Filter_Method($this, 'methods'),
            'truncate' => new Twig_Filter_Method($this, 'truncate'),
            'ago' => new Twig_Filter_Method($this, 'ago'),
            'placeholder' => new Twig_Filter_Method($this, 'placeholder'),
            /*'datebis' => new Twig_Filter_Function($this, 'date'),
            'fluo' => new Twig_Filter_Function($this, 'fluo'),*/
        );
    }
 
    public static function getParam($param)
    {
        return array_key_exists($param, $_REQUEST) ? $_REQUEST[$param] : false; 
    }
 
    public static function typeOf($param)
    {
        return get_class($param); 
    }
 
    public static function methods($param)
    {
        return print_r(get_class_methods($param), true); 
    }
    
    public static function truncate($text, $max)
    {
        if(strlen($text) > $max)
        {
            $text = substr($text, 0, $max-3);
            $white = strrpos($text, ' ');
            $text = substr($text, 0, $white).' ...';
        }
        
        return $text;
    }
    
    public static function placeholder($txt)
    {
        return preg_replace('#[":]#', '', $txt);
    }

    public static function ago($tm, $rcs = 0) 
    {
        $cur_tm = time(); $dif = $cur_tm-strtotime($tm);
        $pds = array('second','minute','hour','day','week','month','year','decade');
        $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
        for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);
       
        $no = floor($no); if($no <> 1) $pds[$v] .='s'; $x=sprintf("%d %s ",$no,$pds[$v]);
        if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
        return $x.' ago ';
    }
}
