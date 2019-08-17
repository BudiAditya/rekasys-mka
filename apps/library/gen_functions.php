<?php
/**
 * Created by PhpStorm.
 * User: Budi
 * Date: 22 Feb 12
 * Time: 10:56:53
 * To change this template use File | Settings | File Templates.
 */

    function right($str,$len)
     { $str = trim($str);
       $nln = strlen($str);
       $out = substr($str,$nln-$len,$len);
       return $out;
     }

     function left($str,$len)
     { $str = trim($str);
       $out = substr($str,0,$len);
       return $out;
     }

/**
 * Untuk membuat terbilang dari nominal angka. Tidak boleh lebih dari 10^12
 * NOTE: Recursive method calling. Awesome... ^_^
 *
 * @param $x
 * @return string
 * @throws Exception
 */
function terbilang($x) {
	$inWords = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
	if ($x < 12) {
		return " " . $inWords[$x];
	} elseif ($x < 20) {
		return terbilang($x - 10) . " belas";
	} elseif ($x < 100) {
		return terbilang($x / 10) . " puluh" . terbilang($x % 10);
	} elseif ($x < 200) {
		return " seratus" . terbilang($x - 100);
	} elseif ($x < 1000) {
		return terbilang($x / 100) . " ratus" . terbilang($x % 100);
	} elseif ($x < 2000) {
		return " seribu" . terbilang($x - 1000);
	} elseif ($x < 1000000) {
		return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
	} elseif ($x < 1000000000) {
		return terbilang($x / 1000000) . " juta" . terbilang($x % 1000000);
	} elseif ($x < 1000000000000) {
		return terbilang($x / 1000000000) . " milyar" . terbilang($x % 1000000000);
	} else {
		throw new Exception("Value too big ! Un-supported Yet !");
	}
}

    /*
    function terbilang($x)
    {
    // membentuk format bilangan XXX.XXX.XXX.XXX.XXX
    $x = number_format($x, 0, "", ".");

    // memecah kelompok ribuan berdasarkan tanda '.'
    $pecah = explode(".", $x);

    $string = "";

    // membentuk format terbilang '... trilyun ... milyar ... juta ... ribu ...'
    for($i = 0; $i <= count($pecah)-1; $i++)
    {
       if ((count($pecah) - $i == 5) && ($pecah[$i] != 0)) $string .= bilangRatusan($pecah[$i])."triliyun "; // membentuk kata '... trilyun'
       else if ((count($pecah) - $i == 4) && ($pecah[$i] != 0)) $string .= bilangRatusan($pecah[$i])."milyar "; // membentuk kata '... milyar'
       else if ((count($pecah) - $i == 3) && ($pecah[$i] != 0)) $string .= bilangRatusan($pecah[$i])."juta "; // membentuk kata '... juta'
       else if ((count($pecah) - $i == 2) && ($pecah[$i] == 1)) $string .= "seribu "; // kejadian khusus untuk bilangan dalam format 1XXX (yang mengandung kata 'seribu')
       else if ((count($pecah) - $i == 2) && ($pecah[$i] != 0)) $string .= bilangRatusan($pecah[$i])."ribu "; // membentuk kata '... ribu'
       else if ((count($pecah) - $i == 1) && ($pecah[$i] != 0)) $string .= bilangRatusan($pecah[$i]);
    }

    return $string;
    }
    */

    //ubah tanggal kedalam format indo(long date)//
    function long_date($tgl){
        $tanggal = substr($tgl,8,1) == 0 ? substr($tgl,9,2) : substr($tgl,8,2);
        $bulan = get_bulan(substr($tgl,5,2));
        $tahun =substr($tgl,0,4);
        return $tanggal.' '.$bulan.' '.$tahun;
    }

    function get_bulan($bln){
        switch ($bln){
            case 1:
                return "Januari";
                break;
            case 2:
                return "Februari";
                break;
            case 3:
                return "Maret";
                break;
            case 4:
                return "April";
                break;
            case 5:
                return "Mei";
                break;
            case 6:
                return "Juni";
                break;
            case 7:
                return "Juli";
                break;
            case 8:
                return "Agustus";
                break;
            case 9:
                return "September";
                break;
            case 10:
                return "Oktober";
                break;
            case 11:
                return "November";
                break;
            case 12:
                return "Desember";
                break;
        }
    }
?>
