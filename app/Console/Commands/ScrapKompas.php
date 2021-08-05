<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;

class ScrapKompas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:kompas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $list_portal = \App\Models\Scrap\Portal::with(
            [
                'kanal',
                'kanal.subkanal'

            ]
        )->find(3);

        $this->info('Memulai');

        foreach($list_portal->kanal as $kan)
        {
            if($kan->type == "indeks")
            {
                $this->info('Menarik Data == '.$kan->url_kanal);

                $client = new Client();
                $url = $kan->url_kanal;
                $crawler = $client->request('GET', $url);

                $title = array();
                $crawler->filter('a.article__link')->each(function($node) use(&$title){
                    // dump($node->text());
                    $title[]= $node->text();
                });

                $konten=array();
                    $crawler->filter('.article__subtitle')->each(function($node) use(&$konten){
                    // dump($node->text());
                    $konten[]=$node->text();
                });

                $list_url = array();
                $crawler->filter('a.article__link')->each(function($node) use(&$list_url){
                    // dump($node->attr('href')); 
                    $list_url[]= $node->attr("href");
                });

                $tanggal = array();
                $crawler->filter('.article__date')->each(function($node) use(&$tanggal){
                    $tanggal[]= $node->text();
                });

                if(count($title) == count($tanggal))
                {
                    foreach($title as $s=>$t)
                    {
                        $cek = \App\Models\Scrap\ScrapParam::where('judul_artikel',$t)
                            ->orWhere('link_artikel',$list_url[$s])
                            ->first();

                        if($cek == null)
                        {
                            $param = new \App\Models\Scrap\ScrapParam;
                            $param->tanggal = date('Y-m-d');
                            $param->jam = date('H:i:s');

                            // $this->info('URLNYA == '.$kan->url_kanal .'kategorinya'.$konten[$s]);


                            //cek artikel masuk kategori apa
                            $cat = \App\Models\Scrap\LinkKategori::where('portal_id',$list_portal->id)
                                    ->where('name_link_kategori', $konten[$s])
                                    ->first();

                                    if($cat == null){
                                        $param->kategori_id = 0;
                                    }  else{
                                        $param->kategori_id = $cat['kategori_id'];
                                    }

                            //cek artikel masuk subkategori apa
                            $sub = \App\Models\Scrap\LinkSubKategori::where('portal_id',$list_portal->id)
                                    ->where('name_link_subkategori', $konten[$s])
                                    ->first();
                                   if($sub == null){
                                        $param->subkategori_id = 0;
                                   }  else{
                                        $param->subkategori_id = $sub['subkategori_id'];
                                   }

                            $param->kanal_id = $kan->id;
                            $param->judul_artikel = $t;
                            $param->link_artikel = $list_url[$s];
                            $param->tanggal_publish = $tanggal[$s];
                            $param->konten = $konten[$s];


                            $word="WIB";

                            if(strpos($tanggal[$s], $word) !== false){
                                //cari karakter WIB di no berapa
                                $cek=strpos($tanggal[$s],"WIB",0)-1;
                                // tanggal
                                $tgl=date('Y-m-d');
                                // hilangkan karakter wib
                                $wib=substr($tanggal[$s],0,$cek);
                                //ambil 5 karakter terkahir dari kalimat untuk ambil jam
                                $jam=substr($wib,-5);
                                $param->portal_publish=$tgl." ".$jam.":00";
                            } else {
                                //cek panjang karakter
                                $cek=strlen($tanggal[$s]);
                                // tanggal
                                $tgl=date('Y-m-d');
                                // ambil jam
                                $jam=substr($tanggal[$s],-8);
                                $param->portal_publish=$tgl." ".$jam;
                            }


                            $simpanparam = $param->save();

                            if($simpanparam){
                                $cek_kanal_parameter = \App\Models\Scrap\Kanalparameter::where('parameter_id', $param->id)
                                    ->where('kanal_id', $kan->id)
                                    ->count();

                                if($cek_kanal_parameter == 0)
                                {
                                    $p = new \App\Models\Scrap\Kanalparameter;
                                    $p->parameter_id = $param->id;
                                    $p->kanal_id = $kan->id;
                                    $p->portal_id = $list_portal->id;
                                    $p->kategori_id = $param->kategori_id;
                                    $p->subkategori_id = $param->subkategori_id;
                                    $p->save();
                                }
                            }
                        }else{
                            $cek_kanal_parameter = \App\Models\Scrap\Kanalparameter::where('parameter_id', $cek->id)
                                    ->where('kanal_id', $kan->id)
                                    ->count();
                            
                            if($cek_kanal_parameter == 0)
                            {
                                $p = new \App\Models\Scrap\Kanalparameter;
                                $p->parameter_id = $cek->id;
                                $p->kanal_id = $kan->id;
                                $p->portal_id = $list_portal->id;
                                $p->kategori_id = $cek->kategori_id;
                                $p->subkategori_id = $cek->subkategori_id;
                                $p->save();
                            }
                        }
                    }
                }
            }
            else
            {

                $this->info('Menarik Data Kanal Populer == '.$kan->url_kanal);

                $client = new Client();
                $url = $kan->url_kanal;
                $crawler = $client->request('GET', $url);

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
                            });
                        });

                        $detail->filter('.breadcrumb__wrap')->each(function ($dt) use(&$konten, &$subkonten){
                            $konten[]=$dt->text();
                            $subkonten[]=$konten[count($konten) - 1];
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

                    $list=array(
                        'title'=>$title,
                        'url'=>$list_url,
                        'tanggal'=>$tanggal,
                        'subkonten'=>$tesya,
                        'dibaca'=>$dibaca
                    );

                    dump($list);

                    if(count($title) == count($tanggal))
                    {
                        foreach($title as $s=>$t)
                        {
                            $cek = \App\Models\Scrap\ScrapParam::where('judul_artikel',$t)
                            ->orWhere('link_artikel',$list_url[$s])
                            ->first();

                            if($cek == null)
                            {

                                $param = new \App\Models\Scrap\ScrapParam;
                                $param->tanggal = date('Y-m-d');
                                $param->jam = date('H:i:s');

                                //cek artikel masuk kategori apa
                                $cat = \App\Models\Scrap\LinkKategori::where('portal_id',$list_portal->id)
                                        ->where('name_link_kategori', $tesya[$s])
                                        ->first();

                                        if($cat == null){
                                            $param->kategori_id = 0;
                                        }  else{
                                            $param->kategori_id = $cat['kategori_id'];
                                        }

                                //cek artikel masuk subkategori apa
                                $sub = \App\Models\Scrap\LinkSubKategori::where('portal_id',$list_portal->id)
                                        ->where('name_link_subkategori', $tesya[$s])
                                        ->first();
                                       if($sub == null){
                                            $param->subkategori_id = 0;
                                       }  else{
                                            $param->subkategori_id = $sub['subkategori_id'];
                                       }




                                $param->kanal_id = $kan->id;
                                $param->judul_artikel = $t;
                                $param->link_artikel = $list_url[$s];
                                $param->tanggal_publish = $tanggal[$s];
                                $param->konten = $tesya[$s];


                                $word="WIB";

                                if(strpos($tanggal[$s], $word) !== false){
                                    //cari karakter WIB di no berapa
                                    $cek=strpos($tanggal[$s],"WIB",0)-1;
                                    // tanggal
                                    $tgl=date('Y-m-d');
                                    // hilangkan karakter wib
                                    $wib=substr($tanggal[$s],0,$cek);
                                    //ambil 5 karakter terkahir dari kalimat untuk ambil jam
                                    $jam=substr($wib,-5);
                                    $param->portal_publish=$tgl." ".$jam.":00";
                                } else {
                                    //cek panjang karakter
                                    $cek=strlen($tanggal[$s]);
                                    // tanggal
                                    $tgl=date('Y-m-d');
                                    // ambil jam
                                    $jam=substr($tanggal[$s],-8);
                                    $param->portal_publish=$tgl." ".$jam;
                                }

                                $simpanparam = $param->save();

                                if($simpanparam){
                                    $cek_kanal_parameter = \App\Models\Scrap\Kanalparameter::where('parameter_id', $param->id)
                                    ->where('kanal_id', $kan->id)
                                    ->count();

                                    if($cek_kanal_parameter == 0)
                                    {
                                        $p = new \App\Models\Scrap\Kanalparameter;
                                        $p->parameter_id = $param->id;
                                        $p->kanal_id = $kan->id;
                                        $p->portal_id = $list_portal->id;
                                        $p->save();
                                    }
                                }
                            }else{
                                $cek_kanal_parameter = \App\Models\Scrap\Kanalparameter::where('parameter_id', $cek->id)
                                    ->where('kanal_id', $kan->id)
                                    ->count();

                                if($cek_kanal_parameter == 0)
                                {
                                    $p = new \App\Models\Scrap\Kanalparameter;
                                    $p->parameter_id = $cek->id;
                                    $p->kanal_id = $kan->id;
                                    $p->portal_id = $list_portal->id;
                                    $p->kategori_id = $cek->kategori_id;
                                    $p->subkategori_id = $cek->subkategori_id;
                                    $p->save();
                                }
                            }

                        }
                    }
                });
            }
        }

        $this->info('Selesai');
    }
}
