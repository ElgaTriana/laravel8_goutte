<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Goutte\Client;

class WelcomeController extends Controller
{
    public function idntimes(Request $request){

        $url = "https://www.idntimes.com/news";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $crawler->filter('.container')->each(function($node) use(&$url){
            dump($node);
        });
    }

    public function idntimestahap2(Request $request){
        $url = "{{URL::to('scrapping/idntimes')}}";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $crawler->filter('.no-js')->each(function($node) use(&$url){
            dump($node);
        });       
    }

    public function antaranews(Request $request){
        $url = "https://www.antaranews.com/indeks";
        $client = new Client();
        $crawler = $client->request('GET', $url);
        $tglskrng = date("Y-m-d");

        $title=array();
        $crawler->filter('.simple-post h3')->each(function($node) use(&$title){
            $title[]=$node->text();
        });

        $link=array();
        $crawler->filter('.simple-post h3 a')->each(function($node) use(&$link){
            $link[]=$node->attr('href');
        });

        $kategori=array();
        $crawler->filter('.simple-share a')->each(function($node) use(&$kategori){
            $kategori[]=$node->attr('title');
        });

        $tglskrng1 = array();
        $crawler->filter('.simple-post .simple-share span')->each(function($node) use(&$tglskrng1){
            $tglskrng1[]= $node->text();
            // $tglskrng1[]=$tglskrng;
        });

        return array(
            'judul'=>$title,
            'link'=>$link,
            'kategori'=>$kategori,
            'tanggal_skrng'=>$tglskrng1
        );
    }

    public function okezone(Request $request){
        $url = "https://celebrity.okezone.com/indeks/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $title=array();
        $crawler->filter('.content-hardnews .c-celebrity a')->each(function($node) use(&$title){
            $title[]=$node->attr('title');
        });

        $url=array();
        $crawler->filter('.content-hardnews .c-celebrity a')->each(function($node) use(&$url){
            $url[]=$node->attr('href');
        });

        $tanggal=array();
        $crawler->filter('time.category-hardnews')->each(function($node) use(&$tanggal){
            $tanggal[]=$node->text();
        });

        return array(
            'title'=>$title,
            'url'=>$url,
            'tanggal'=>$tanggal,
        );
    }

    public function sindonews(Request $request){
        $url = "https://index.sindonews.com/index/612";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $title=array();
        $crawler->filter('.indeks-title')->each(function($node) use(&$title){
            $title[]=$node->text();
        });

        $url=array();
        $crawler->filter('.indeks-title a')->each(function($node) use(&$url){
            $url[]=$node->attr('href');
        });

        $tanggal=array();
        $crawler->filter('li p')->each(function($node) use(&$tanggal){
            $tanggal[]=$node->text();
        });

        return array(
            'title'=>$title,
            'url'=>$url,
            'tanggal'=>$tanggal,
        );
    }

    public function inewsid(Request $request){
        $url = "https://www.inews.id/indeks/sport";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $title=array();
        $crawler->filter('h3.title-news-update')->each(function($node) use(&$title){
            $title[]=$node->text();
        });

        $url=array();
        $crawler->filter('ul.list-unstyled li a')->each(function($node) use(&$url){
            $url[]=$node->attr('href');
        });

        $tanggal=array();
        $crawler->filter('.news-excerpt div.date')->each(function($node) use(&$tanggal){
            $tanggal[]=$node->text();
        });

        return array(
            'title'=>$title,
            'url'=>$url,
            'tanggal'=>$tanggal,
        );
    }

    public function tes(Request $request){
        $date="Kamis, 11 Februari 2021 - 06:52 WIB";

        $date1=explode(', ',$date);

        $tes2=preg_replace("/ WIB/","", $date1);

        $tes3=preg_replace("/ ,/","", $tes2);

        return $tes3;
    }

    public function suara(Request $request){
        $url = "https://www.suara.com/indeks";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $title=array();
        $crawler->filter('.item-content h4.post-title')->each(function($node) use(&$title){
            $title[]=$node->text();
        });

        $url=array();
        $crawler->filter('.item-content h4.post-title a.ellipsis2')->each(function($node) use(&$url){
            $url[]=$node->attr('href');
        });

        $jam=array();
        $crawler->filter('.suara-time')->each(function($node) use(&$jam){
            $jam[]=$node->text();
        });

        return array(
            'title'=>$title,
            'url'=>$url, 
            'jam'=>$jam
        );
    }
    
    public function deskripsiokezone(Request $request){
        $url = "https://www.inews.id/sport/soccer/gara-gara-bikin-tato-aubameyang-langgar-protokol-kesehatan";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $deskripsi=array();
        $crawler->filter('meta[property*="og:description"]')->each(function($node) use(&$deskripsi){
            // $deskripsi=$node->attr('content');
            dump($node->attr('content'));
        });
    }

    public function deskripsisindonews(Request $request){
        $url = "https://metro.sindonews.com/read/337950/171/sofyan-djalil-resmikan-kantor-perwakilan-bpn-wilayah-cileungsi-1613541729";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $deskripsi=array();
        $crawler->filter('meta[property*="og:description"]')->each(function($node) use(&$deskripsi){
            $deskripsi=$node->attr('content');
        });
    }

    public function deskripsiinewsid(Request $request){
        $url = "https://www.inews.id/travel/kuliner/makanan-khas-toraja";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $deskripsi=array();
        $crawler->filter('meta[property*="og:description"]')->each(function($node) use(&$deskripsi){
            $deskripsi=$node->attr('content');
        });
    }
}
