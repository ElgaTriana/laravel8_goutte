<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Goutte\Client;

use Carbon\Carbon;

use Symfony\Component\HttpClient\HttpClient;

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
        $url = "https://news.okezone.com/infografis";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $title=array();
        $url=array();
        $tanggal=array();
        $konten=array();
        $kontenlast=array();

        $crawler->filter('.content-hardnews h3 a')->each(function($node) use(&$title, &$url, &$tanggal, &$konten, &$kontenlast){
            $url[]=$node->link()->getUri();

            // dump($url);

            $client = new Client();
            $detail = $client->request('GET', $node->link()->getUri());

            $detail->filter('.container-bodyhome-left div.title h1')->each(function ($node_detail) use(&$title){
                $title[]=$node_detail->text();
            });

            $detail->filter('.container-bodyhome-left div.container-top div.namerep b')->each(function ($node_detail) use(&$tanggal){
                $tanggal[]=$node_detail->text();
            });

            $detail->filter('.container-bodyhome-left div.breadcrumb ul')->each(function ($node_detail) use(&$konten, &$kontenlast){
                $kontenlast=explode(" ",$node_detail->text());
                $konten[]=array_pop($kontenlast);
            });
        });

        return array(
            'title'=>$title,
            'url'=>$url,
            'tanggal'=>$tanggal,
            'konten'=>$konten,
        );
    }

    public function sindonews(Request $request){
        $url = "https://index.sindonews.com/video";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $title=array();
        $crawler->filter('.grid-news-title a')->each(function($node) use(&$title){
            $title[]=$node->text();
        });

        $list_url=array();
        $crawler->filter('.grid-news-title a')->each(function($node) use(&$list_url){
            $list_url[]=$node->attr('href');
        });

        $tanggal=array();
        $crawler->filter('.grid-news-rows div.grid-news-time')->each(function($node) use(&$tanggal){
            $tanggal[]=$node->text();
        });

        return array(
            'title'=>$title,
            'url'=>$list_url,
            'tanggal'=>$tanggal,
        );
    }

    public function inewsid(Request $request){
        $url = "https://www.inews.id/indeks/photo/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $title=array();
        $crawler->filter('h3.title-news-update')->each(function($node) use(&$title){
            $title[]=$node->text();
        });

        $konten=array();
        $crawler->filter('.news-excerpt div.date strong')->each(function($node) use(&$konten){
            $konten[]=$node->text();
        });
        
        $list_url=array();
        $crawler->filter('ul.list-unstyled li a')->each(function($node) use(&$list_url){
            $list_url[]=$node->attr('href');
        });
        
        $tanggal=array();
        $crawler->filter('.news-excerpt div.date')->each(function($node) use(&$tanggal){
            $tanggal[]=$node->text();
        });

        return array(
            'title'=>$title,
            'konten'=>$konten,
            'url'=>$list_url,
            'tanggal'=>$tanggal,
        );
    }

    public function tes(Request $request){
        $url = "https://www.liputan6.com/popular";
        $client = new Client();
        $crawler = $client->request('GET', $url);   

        $title = array();
        $crawler->filter('h4.articles--iridescent-list--text-item__title')->each(function($node) use(&$title){
            // dump($node->text());
            $title[]= $node->text();
        });

        $url = array();
        $crawler->filter('h4.articles--iridescent-list--text-item__title a')->each(function($node) use(&$url){
            $url[]= $node->attr("href");
        });

        $tanggal = array();
        $crawler->filter('span.articles--iridescent-list--text-item__datetime')->each(function($node) use(&$tanggal){
            $tanggal[]= $node->text();
        });

        return array(
            'title'=>$title,
            'url'=>$url,
            'tanggal'=>$tanggal
        );
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

    public function getdataidntimes(Request $request){
        $var =\DB::connection('mysql3')->table('scrap_portal_parameter_dummy as a')->selectRaw('a.*')
        ->whereDate('a.tanggal', Carbon::today())
        ->get();

        $title=array();
        $url=array();
        $tanggal=array();

        foreach($var as $i=>$val){
            $title[]=$val->judul_artikel;
            $url[]=$val->link_artikel;
            $tanggal[]=$val->tanggal_publish;
        }

        return array(
            'title'=>$url,
            'url'=>$title, 
            'tanggal'=>$tanggal
        );
    }

    public function liputan6(Request $request)
    {
        $url = "https://www.liputan6.com/global/indeks";
        $client = new Client();
        $crawler = $client->request('GET', $url);   

        $title = array();
        $crawler->filter('h4.articles--rows--item__title')->each(function($node) use(&$title){
            // dump($node->text());
            $title[]= $node->text();
        });

        $url = array();
        $crawler->filter('a.articles--rows--item__title-link')->each(function($node) use(&$url){
            $url[]= $node->attr("href");
        });

        $tanggal = array();
        $crawler->filter('.articles--rows--item__time')->each(function($node) use(&$tanggal){
            $tanggal[]= $node->text();
        });

        return array(
            'title'=>$title,
            'url'=>$url,
            'tanggal'=>$tanggal
        );
    }

    public function tokped(Request $request){
        $url = "https://www.tokopedia.com/hansaplast";
        $client = new Client();

        // $client = new Client(HttpClient::create(array(
        //     'headers' => array(
        //         'user-agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:73.0) Gecko/20100101 Firefox/73.0', // will be forced using 'Symfony BrowserKit' in executing
        //         'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        //         'Accept-Language' => 'en-US,en;q=0.5',
        //         // 'Referer' => 'https://www.tokopedia.com/kamarmandi',
        //         'Upgrade-Insecure-Requests' => '1',
        //         'Save-Data' => 'on',
        //         'Pragma' => 'no-cache',
        //         'Cache-Control' => 'no-cache',
        //     ),
        // )));

        $client->setServerParameter('HTTP_USER_AGENT', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:73.0) Gecko/20100101 Firefox/73.0');


        $crawler = $client->request('GET', $url);


        $nama_toko=array();
        $crawler->filter('#zeus-root h1')->each(function($node) use(&$nama_toko){
            $nama_toko[]=$node->text();
        });

        $respon=array();
        $lokasi_toko=array();
        $crawler->filter('#zeus-root .css-drikti ul.css-1vb8f2>div.css-k008qs>li.css-ui6bwv>p.css-dxunmy-unf-heading')->each(function($node) use(&$respon, &$lokasi_toko){
            $respon[]=explode(', ', $node->text());
            $lokasi_toko[]=explode(';}', $node->text());
        });

        $produk_terjual=array();
        $crawler->filter('#zeus-root .css-drikti div.css-1pzufdd>div.css-8rzf0c>div.css-drikti>div.css-15n97o5>h2')->each(function($node) use(&$produk_terjual){
            $produk_terjual[]=$node->text();
        });

        $ulasan=array();
        $crawler->filter('#zeus-root .css-drikti div.css-1pzufdd>div.css-8rzf0c>div.css-drikti>div.css-10oyrqa>h6.css-1s96mum-unf-heading')->each(function($node) use(&$ulasan){
            $ulasan[]=$node->text();
        });

        $followers=array();
        $crawler->filter('#zeus-root .css-drikti ul.css-1vb8f2>div.css-k008qs>li.css-ui6bwv>h6')->each(function($node) use(&$followers){
            $followers[]=$node->text();
        });

        $rating=array();
        $crawler->filter('#zeus-root .css-drikti div.css-1pzufdd>div.css-8rzf0c>div.css-drikti>div.css-10oyrqa>h2')->each(function($node) use(&$rating){
            $rating[]=$node->text();
        });

        $point=array();
        $crawler->filter('div[data-testid="pdpFlexWrapperContainer"]>div.css-1p0pkw3>div.css-165xs4l>div.css-ypd15i-unf-tooltip>div')->each(function($node) use(&$point){
            $point[]=explode('""', $node->text());
        });        

        // $crawler->filterXPath('//div[@class="css-ais6tt">button.css-7ynl8q-unf-btn>span]')->each(function($node) use(&$followers){
        //     dump($node->text());
        // });

        // $crawler->filterXPath('//div[@class="css-1lq0eva"]')->each(function($node) use(&$followers){
        //     dump($node->text());
        // });        

        // return array(
        //     'nama_toko'=>$nama_toko,
        //     'lokasi_toko'=>$lokasi_toko[1][2],
        //     'respon'=>$respon[0],
        //     'point'=>$point[1],
        //     'produk_terjual'=>$produk_terjual, 
        //     'ulasan'=>$ulasan, 
        //     'followers'=>$followers,
        //     'rating_toko'=>$rating
        // );
    }

    public function tokped_detail_produk(Request $request){
        $url = "https://www.tokopedia.com/hansaplast/hansaplast-junior-fun-10-s-best-value";
        $client = new Client();

        $client->setServerParameter('HTTP_USER_AGENT', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:73.0) Gecko/20100101 Firefox/73.0');

        $crawler = $client->request('GET', $url);

        $nama_produk=array();
        $crawler->filter('#zeus-root h1')->each(function($node) use(&$nama_produk){
            $nama_produk[]=$node->text();
        });

        $harga_jual=array();
        $crawler->filter('#pdp_comp-product_content>div.css-jmbq56>div.css-32gaxy>div.price')->each(function($node) use(&$harga_jual){
            $harga_jual[]=$node->text();
        });

        $diskon=array();
        $crawler->filter('#pdp_comp-product_content>div.css-jmbq56>div.css-32gaxy>div.css-70qvj9>div.css-1wkmcys>span[data-testid="lblPDPDetailDiscountPercentage"]')->each(function($node) use(&$diskon){
            $diskon[]=$node->text();
            // $diskon[]=$node->text();
        });

        $harga_sebelum_diskon=array();
        $crawler->filter('#pdp_comp-product_content>div.css-jmbq56>div.css-32gaxy>div.css-70qvj9>div.original-price>span[data-testid="lblPDPDetailOriginalPrice"]')->each(function($node) use(&$harga_sebelum_diskon){
            $harga_sebelum_diskon[]=$node->text();
        });

        $deskripsi_produk=array();
        $crawler->filter('div.css-1gp8p6u div[data-testid="lblPDPDescriptionProduk"]')->each(function($node) use(&$deskripsi_produk){
            $deskripsi_produk[]=$node->text();
        });

        $crawler->filter('div.css-drikti>div.css-1b08guo>div.css-xi606m>h5[data-testid="txtRatingScore"]')->each(function($node){
            dump($node->attr("data-emotion-css"));
        });

        // return array(
        //     'nama_produk'=>$nama_produk,
        //     'harga_jual'=>$harga_jual,
        //     'diskon'=>$diskon,
        //     'harga_sebelum_diskon'=>$harga_sebelum_diskon,
        //     'deskripsi_produk'=>$deskripsi_produk
        // );     
    }

    public function tokped_produk(Request $request){

        $url = "https://www.tokopedia.com/betadineofficial?source=universe&st=product";
        $client = new Client();

        $client->setServerParameter('HTTP_USER_AGENT', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:73.0) Gecko/20100101 Firefox/73.0');

        $crawler = $client->request('GET', $url);

        $link_produk=array();
        $crawler->filter('div.css-twtylv>div.css-8atqhb')->each(function($node) use(&$point){
            dump($node);
        });
    }

    public function homepagedetik(Request $request){
        $url = "https://www.detik.com/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        // Terkini
        $judul=array();
        $crawler->filter('article.ph_newsfeed_d')->each(function($node) use(&$judul){
            $judul[]=$node->text();
        });

        $link_artikel=array();
        $crawler->filter('article.ph_newsfeed_d')->each(function($node) use(&$link_artikel){
            $link_artikel[]=$node->attr('i-link');
        });

        $tanggal_artikel=array();
        $crawler->filter('article.ph_newsfeed_d')->each(function($node) use(&$tanggal_artikel){
            $tanggal_artikel[]=explode('"', $node->attr('i-info'))[3];
            // dump($node->attr('i-info'));
        });

        return array(
            'judul'=>$judul,
            'link_artikel'=>$link_artikel,
            'tanggal_artikel'=>$tanggal_artikel
        );
        // End Terkini
    }

    public function homepagekompas(Request $request){
        $url = "https://www.kompas.com/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $judul=array();
        $crawler->filter('div.article__list div.article__list__title h3')->each(function($node) use(&$judul){
            $judul[]=$node->text();
        });

        $link_artikel=array();
        $crawler->filter('div.article__list div.article__list__title h3 a')->each(function($node) use(&$link_artikel){
            $link_artikel[]=$node->attr('href');
        });

        $tanggal_artikel=array();
        $crawler->filter('div.article__list div.article__list__info')->each(function($node) use(&$tanggal_artikel){
            $tanggal_artikel[]=$node->text();
        });

        return array(
            'judul'=>$judul,
            'link_artikel'=>$link_artikel,
            'tanggal_artikel'=>$tanggal_artikel
        );
    }

    public function homepagetribun(Request $request){
        $url = "https://www.tribunnews.com/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $judul=array();
        $crawler->filter('li.p1520 div.mr140 h3')->each(function($node) use(&$judul){
            $judul[]=$node->text();
        });

        $link_artikel=array();
        $crawler->filter('li.p1520 div.mr140 h3 a')->each(function($node) use(&$link_artikel){
            $link_artikel[]=$node->attr('href');
        });

        $tanggal_artikel=array();
        $crawler->filter('li.p1520 div.mr140 div.pt5 time')->each(function($node) use(&$tanggal_artikel){
            $tanggal_artikel[]=$node->attr('title');
        });

        return array(
            'judul'=>$judul,
            'link_artikel'=>$link_artikel,
            'tanggal_artikel'=>$tanggal_artikel
        );
    }

    public function homepageliputan6(Request $request){
        $url = "https://www.liputan6.com/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $judul=array();
        $crawler->filter('article.articles--iridescent-list--item')->each(function($node) use(&$judul){
            $judul[]=$node->text();
        });

        $link_artikel=array();
        $crawler->filter('article.articles--iridescent-list--item h4.articles--iridescent-list--text-item__title a')->each(function($node) use(&$link_artikel){
            $link_artikel[]=$node->attr('href');
        });

        $tanggal_artikel=array();
        $crawler->filter('article.articles--iridescent-list--item span.articles--iridescent-list--text-item__datetime time')->each(function($node) use(&$tanggal_artikel){
            $tanggal_artikel[]=$node->attr('datetime');
        });

        return array(
            'judul'=>$judul,
            'link_artikel'=>$link_artikel,
            'tanggal_artikel'=>$tanggal_artikel
        );
    }

    public function homepageinewsid(Request $request){
        $url = "https://www.inews.id/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $judul=array();
        $crawler->filter('ul.list-unstyled li h3')->each(function($node) use(&$judul){
            $judul[]=$node->text();
        });

        $link_artikel=array();
        $crawler->filter('ul.list-unstyled li a')->each(function($node) use(&$link_artikel){
            $link_artikel[]=$node->attr('href');
        });

        $tanggal_artikel=array();
        $crawler->filter('ul.list-unstyled li div.news-excerpt div.date')->each(function($node) use(&$tanggal_artikel){
            $tanggal_artikel[]=$node->text();
        });

        return array(
            'judul'=>$judul,
            'link_artikel'=>$link_artikel,
            'tanggal_artikel'=>$tanggal_artikel
        );
    }

    public function homepagesindonews(Request $request){
        $url = "https://www.sindonews.com/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $judul=array();
        $crawler->filter('div.news-content div.news-title')->each(function($node) use(&$judul){
            $judul[]=$node->text();
        });

        $link_artikel=array();
        $crawler->filter('div.news-content div.news-title a')->each(function($node) use(&$link_artikel){
            $link_artikel[]=$node->attr('href');
        });

        $tanggal_artikel=array();
        $crawler->filter('div.news-content div.news-channel div.news-date')->each(function($node) use(&$tanggal_artikel){
            $tanggal_artikel[]=$node->text();
        });

        return array(
            'judul'=>$judul,
            'link_artikel'=>$link_artikel,
            'tanggal_artikel'=>$tanggal_artikel
        );
    }

    public function homepageokezone(Request $request){
        $url = "https://www.okezone.com/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $judul=array();
        $crawler->filter('div.content-hardnews h3 a')->each(function($node) use(&$judul){
            $judul[]=$node->attr('title');
        });

        $link_artikel=array();
        $crawler->filter('div.content-hardnews h3 a')->each(function($node) use(&$link_artikel){
            $link_artikel[]=$node->attr('href');
        });

        $tanggal_artikel=array();
        $crawler->filter('div.content-hardnews span.mh-clock')->each(function($node) use(&$tanggal_artikel){
            $tanggal_artikel[]=$node->text();
        });

        return array(
            'judul'=>$judul,
            'link_artikel'=>$link_artikel,
            'tanggal_artikel'=>$tanggal_artikel
        );
    }

    public function detik(Request $request){
        $url = "https://www.detik.com/terpopuler/wolipop";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $crawler->filter('.list-content')->each(function ($node) use(&$list, &$kan, &$val){
            $title=array();
            $node->filter('.media__title')->each(function($t) use(&$title){
                                    // dump($t->text());
                $title[]=$t->text();
            });

            $list_url=array();
            $node->filter('.media__link')->each(function($t) use(&$list_url){
                $list_url[]=$t->link()->getUri();
            });
            $list_url = array_values(array_unique($list_url));
            $list_url2 = array_unique($list_url);
            
            $tes=count($list_url);

            $konten=array();
            foreach ($list_url2 as $key => $value) {
                // $konten[]=explode('/',$value)[3];
                dump(explode('/',$value)[3]);
            }


            $tes2=count($konten);


            $tanggal = array();
            $node->filter('.media__date > span')->each(function($t) use(&$tanggal){
                $tanggal[]= $t->attr("title");
            });

            $list=array(
                'title'=>$title,
                'url'=>$list_url,
                'konten'=>$konten,
                'tanggal'=>$tanggal,
                'dibaca'=>array()
            );
        });

        // $list=array(
        //     'title'=>$title,
        //     'url'=>$list_url,
        //     'tanggal'=>$tanggal,
        //     'dibaca'=>array()
        // );

        // return $list;
    }

    public function kompas(Request $request){

        $url = "https://lifestyle.kompas.com/";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $konten=array();

        $crawler->filter('.most')->each(function ($node) use(&$list, &$kan, &$list_portal){

            $title=array();
            $node->filter('h4.most__title')->each(function($t) use(&$title){
                $title[]=$t->text();
            });

            $list_url=array();
            $tanggal = array();
            $konten= array();
            $subkonten= array();

            $node->filter('.most__link')->each(function($t) use(&$list_url, &$tanggal, &$konten, &$subkonten){
                $list_url[]= $t->link()->getUri();

                $client = new Client();
                $detail = $client->request('GET', $t->link()->getUri());
                $detail->filter('.js-read-article')->each(function ($dt) use(&$tanggal){
                    $dt->filter('.read__time')->each(function($tl) use(&$tanggal){
                        $tanggal[]=$tl->text();
                        // dump($tanggal);
                    });
                });

                $detail->filter('.breadcrumb__wrap')->each(function ($dt) use(&$konten, &$subkonten){
                    $konten[]=$dt->text();
                    $subkonten[]=$konten[count($konten) - 1];
                    // dump($subkonten);
                });

            });

            $list_url = array_values(array_unique($list_url));

            $dibaca = array();
            $node->filter('.most__read')->each(function($t) use(&$dibaca){
                $dibaca[]= $t->text();
            });

            $tesya=array();

            foreach($subkonten as $char){
                $exploded_array = explode(" ", $char);
                $yohoho=end($exploded_array);
                array_push($tesya, $yohoho);
            }

            dump($subkonten);

            // dump($tesya);

            $list=array(
                'title'=>$title,
                'url'=>$list_url,
                'tanggal'=>$tanggal,
                'subkonten'=>$tesya,
                'dibaca'=>$dibaca
            );
        });

        // $url = "https://indeks.kompas.com/?site=homey";
        // $client = new Client();
        // $crawler = $client->request('GET', $url);

        // $title = array();
        // $crawler->filter('a.article__link')->each(function($node) use(&$title){
        //             // dump($node->text());
        //     $title[]= $node->text();
        // });

        // $konten=array();
        // $crawler->filter('.article__subtitle')->each(function($node) use(&$konten){
        //             // dump($node->text());
        //     $konten[]=$node->text();
        // });

        // $list_url = array();
        // $crawler->filter('a.article__link')->each(function($node) use(&$list_url){
        //             // dump($node->attr('href')); 
        //     $list_url[]= $node->attr("href");
        // });

        // $tanggal = array();
        // $crawler->filter('.article__date')->each(function($node) use(&$tanggal){
        //     $tanggal[]= $node->text();
        // });

        // return array(
        //     'title'=>$title,
        //     'konten'=>$konten,
        //     'list_url'=>$list_url,
        //     'tanggal'=>$tanggal
        // );

    }

    public function idxchannel(Request $request){
        
        $url = "https://www.idxchannel.com/ecotainment";
        
        $client = new Client();
        
        $crawler = $client->request('GET', $url);

        $title=array();
        $crawler->filter('.bt-con .tab--content .title-capt')->each(function($node) use(&$title){
            $title[]=$node->text();
        });

        $konten=array();
        $crawler->filter('.bt-con .tab--content> div.headline-date a')->each(function($node) use(&$konten){
            $konten[]=$node->text();
        });

        $list_url=array();
        $tanggal=array();
        $crawler->filter('.container-news div.tab--content div.title-capt a')->each(function($node) use(&$list_url, &$tanggal){
            $list_url[]=$node->link()->getUri();

            $client = new Client();
            $detail = $client->request('GET', $node->link()->getUri());

            $detail->filter('.article--creator')->each(function ($node_detail) use(&$tanggal){
                $tanggallast=explode(",", $node_detail->text());
                $tanggal[]=array_pop($tanggallast);
            });
        });

        return array(
            'title'=>$title,
            'konten'=>$konten,
            'url'=>$list_url,
            'tanggal'=>$tanggal,
        );
    }

    public function bola(Request $request){

        $url = "https://www.bola.com/indonesia/indeks/2021/08/10";
        
        $client = new Client();
        
        $crawler = $client->request('GET', $url);

        $title=array();
        $crawler->filter('.articles--rows--item__title-link')->each(function ($node) use(&$title) {
            // dump($node->attr('title')); 
            $title[]=$node->attr('title');
        });

        $list_url=array();
            $crawler->filter('.articles--rows--item__title-link')->each(function($node) use(&$list_url){
            // dump($node->attr('href')); 
            $list_url[]= $node->attr("href");
        });

        $tanggal=array();
        $crawler->filter('.articles--rows--item__time')->each(function($node) use(&$tanggal){
            // dump($node->text());
            $tanggal[]=$node->text();
        });

        $konten=array();
        $crawler->filter('.articles--rows--item__category')->each(function($node) use(&$konten){
            // dump($node->text());
            $konten[]=$node->text();
        });

        return array(
            'title'=>$title,
            'konten'=>$konten,
            'url'=>$list_url,
            'tanggal'=>$tanggal,
        );
    }

    public function cnbc(Request $request){

        $url = "https://www.cnbcindonesia.com/video";
        
        $client = new Client();
        
        $crawler = $client->request('GET', $url);

        // Video
        $title=array();
        $crawler->filter('.list li article h2')->each(function ($node) use(&$title) {
            $title[]=$node->text();
        });

        $list_url=array();
        $crawler->filter('.list li article a')->each(function ($node) use(&$list_url) {
           $list_url[]=$node->attr('href');
        });

        $konten=array();
        $crawler->filter('.list li article span.subjudul')->each(function ($node) use(&$konten) {
            $konten[]=$node->text();
        });

        $tanggal=array();
        $crawler->filter('.list li article a')->each(function($node) use(&$tanggal){
            $client = new Client();
            $detail = $client->request('GET', $node->link()->getUri());

            $detail->filter('.detail_box div.date')->each(function ($node_detail) use(&$tanggal){
                $tanggal[]=$node_detail->text();
            });
        });

        // FOTO
        // $title=array();
        // $crawler->filter('.nhl_foto h2')->each(function ($node) use(&$title) {
        //     $title[]=$node->text();
        // });

        // $list_url=array();
        // $crawler->filter('.nhl_foto div.col_mob_9 a')->each(function ($node) use(&$list_url) {
        //     $list_url[]= $node->attr('href');
        //     // dump($list_url);
        // });

        // $tanggal=array();
        // $crawler->filter('.nhl_foto article a')->each(function($node) use(&$tanggal){
        //     $client = new Client();
        //     $detail = $client->request('GET', $node->link()->getUri());

        //     $detail->filter('.detail_box div.date')->each(function ($node_detail) use(&$tanggal){
        //         // dump($node_detail->text());
        //         $tanggal[]=$node_detail->text();
        //     });
        // });
        
        // ARTIKEL
        // $crawler = $client->request('GET', $url."?date=".date('Y/m/d'));
        // $title=array();
        // $crawler->filter('.lm_content ul.list li h2')->each(function ($node) use(&$title) {
        //     // dump($node->text()); 
        //     $title[]=$node->text();
        // });

        // $tanggal=array();
        // $konten=array();
        // $list_url=array();
        //     $crawler->filter('.lm_content ul.list li a')->each(function($node) use(&$list_url, &$tanggal, &$konten){
        //     $list_url[]= $node->link()->getUri();

        //     $client = new Client();
        //     $detail = $client->request('GET', $node->link()->getUri());

        //     $detail->filter('.detail_box div.date')->each(function ($node_detail) use(&$tanggal){
        //         $tanggal[]=$node_detail->text();
        //     });

        //     $detail->filter('.detail_box div.author span.label')->each(function ($node_detail) use(&$konten){
        //         $konten[]=explode(" ", $node_detail->text())[0];
        //     });
        // });

        return array(
            'title'=>$title,
            'konten'=>$konten,
            'url'=>$list_url,
            'tanggal'=>$tanggal,
        );
    }

    public function celebritis(Request $request){
        $url = "https://www.celebrities.id/entertainment";
        
        $client = new Client();
        
        $crawler = $client->request('GET', $url);

        // // Foto & Video
        // $title=array();
        // $crawler->filter('.item div.title a')->each(function ($node) use(&$title) {
        //     $title[]=$node->text();
        // });

        // $list_url=array();
        // $tanggal=array();
        // $crawler->filter('.item div.title a')->each(function ($node) use(&$list_url, &$tanggal) {
            
        //     $list_url[]=$node->link()->getUri();

        //     $client = new Client();
        //     $detail = $client->request('GET', $node->link()->getUri());

        //     $detail->filter('.info div.content-left div.date')->each(function ($node_detail) use(&$tanggal){
        //         $tanggal[]=$node_detail->text();
        //     });
        // });

        // $konten=array();
        // $crawler->filter('.item div.caption a.category ')->each(function ($node) use(&$konten) {
        //     $konten[]=strtoupper($node->text());
        // });

        // Artikel
        $title=array();
        $crawler->filter('.item div.caption a.title')->each(function ($node) use(&$title) {
            $title[]=$node->text();
        });

        $list_url=array();
        $tanggal=array();
        $konten=array();
        $crawler->filter('.item div.caption a.title')->each(function ($node) use(&$list_url, &$tanggal, &$konten) {
            
            $list_url[]=$node->link()->getUri();

            $client = new Client();
            $detail = $client->request('GET', $node->link()->getUri());

            $detail->filter('.info div.content-left div.date')->each(function ($node_detail) use(&$tanggal){
                $tanggal[]=$node_detail->text();
            });

            $detail->filter('.item.active')->each(function ($node_detail) use(&$konten){
                $konten[]=$node_detail->text();
                // dump($node_detail->text());
            });
        });

        return array(
            'title'=>count($title),
            'konten'=>count($konten),
            'url'=>count($list_url),
            'tanggal'=>count($tanggal),
        );
    }

    public function kapanlagi(Request $request){
        $url = "https://video.kapanlagi.com/";
        
        $client = new Client();
        
        $crawler = $client->request('GET', $url);

        $title=array();
        
        $tanggal=array();
        
        $list_url=array();

        $konten=array();

        $crawler->filter('#v6-tags-populer #tagli img')->each(function ($node) use(&$title) {
            $title[]=$node->attr('alt');
        });

        $crawler->filter('#v6-tags-populer #tagli div.desc a')->each(function ($node) use(&$list, &$list_url, &$konten, &$tanggal, &$title) {
            // dump($node->attr('href'));
            $list_url[]=$node->link()->getUri();

            if (($key = array_search("https://video.kapanlagi.com/", $list_url)) !== false) {
                unset($list_url[$key]);
            }

            $client = new Client();
            $detail = $client->request('GET', $node->link()->getUri());

            $detail->filter('.headline-detail-video div.col-dt-headline span.date-post')->each(function ($node_detail) use(&$tanggal) {
                $tanggal[]=$node_detail->text();
            });

            $list=array(
                'title'=>$title,
                'url'=>array_merge($list_url),
                'tanggal'=>$tanggal,
                'dibaca'=>array()
            );
        });
        return $list;
        
        // return array(
        //     'title'=>count($title),
        //     'konten'=>count($konten),
        //     'url'=>count($list_url),
        //     'tanggal'=>count($tanggal)
        // );
    }
}
