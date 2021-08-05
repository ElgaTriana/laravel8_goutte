<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Goutte\Client;

class ScrapOkezone extends Command
{
    /**
    * The name and git signature of the console command.
    *code 
    * @var string
    */
    protected $signature = 'scrap:okezone';
    
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
        )->find(8);
                
        $this->info('Memulai');
            
        foreach($list_portal->kanal as $kan)
        {
            if($kan->type_kanal=="Foto"){
                $this->info("Menarik Data Kanal Foto ".$kan->url_kanal);

                $client = new Client();
                $list_url = $kan->url_kanal;
                $crawler = $client->request('GET', $list_url);

                $title=array();
                $crawler->filter('.content-hardnews h2')->each(function($node) use(&$title){
                    $title[]=$node->text();
                });

                $list_url=array();
                $tanggal=array();
                $konten=array();
                $crawler->filter('.content-hardnews h2 a')->each(function($node) use(&$list_url, &$tanggal, &$konten){
                    $list_url[]=$node->link()->getUri();

                    $client = new Client();
                    $detail = $client->request('GET', $node->link()->getUri());

                    $detail->filter('.container-bodyhome-left div.reporter b')->each(function ($node_detail) use(&$tanggal){
                        $tanggal[]=$node_detail->text();
                    });

                    $detail->filter('.container-bodyhome-left div.breadcrumb ul')->each(function ($node_detail) use(&$konten){
                        $konten[]=substr($node_detail->text(), strpos($node_detail->text(), " ") + 1);
                    });
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

                            $cek=strpos($tanggal[$s],"WIB",0)-1;

                            $tgl=date('Y-m-d');;

                            $wib=substr($tanggal[$s],0,$cek);

                            $jam=substr($wib,-5);

                            $param->portal_publish = $tgl." ".$jam.":00";

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

                $this->info('Selesai Menarik Data Kanal Foto');
            }else if($kan->type_kanal=="Video"){
                $this->info("Menarik Data Kanal Video ".$kan->url_kanal);

                $client = new Client();
                $list_url = $kan->url_kanal;
                $crawler = $client->request('GET', $list_url);

                $title=array();
                $crawler->filter('.content-hardnews h2')->each(function($node) use(&$title){
                    $title[]=$node->text();
                });

                $list_url=array();
                $tanggal=array();
                $konten=array();
                $crawler->filter('.content-hardnews h2 a')->each(function($node) use(&$list_url, &$tanggal, &$konten){
                    $list_url[]=$node->link()->getUri();

                    $client = new Client();
                    $detail = $client->request('GET', $node->link()->getUri());

                    $detail->filter('.container-bodyhome-left div.reporter b')->each(function ($node_detail) use(&$tanggal){
                        $tanggal[]=$node_detail->text();
                    });

                    $detail->filter('.container-bodyhome-left div.breadcrumb ul')->each(function ($node_detail) use(&$konten){
                        $konten[]=substr($node_detail->text(), strpos($node_detail->text(), " ") + 1);
                    });
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

                            $cek=strpos($tanggal[$s],"WIB",0)-1;

                            $tgl=date('Y-m-d');;

                            $wib=substr($tanggal[$s],0,$cek);

                            $jam=substr($wib,-5);

                            $param->portal_publish = $tgl." ".$jam.":00";

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

                $this->info('Selesai Menarik Data Kanal Video');
            }else if($kan->type_kanal=="Artikel"){
                $this->info("Memulai Kanal Artikel");
                if($kan->type == "indeks"){
                    $this->info("Menarik Data Kanal Artikel Index ". $kan->url_kanal);

                    $client = new Client();
                    $url = $kan->url_kanal;
                    $crawler = $client->request('GET', $url);

                    $title=array();
                    $list_url=array();
                    if($url=="https://celebrity.okezone.com/indeks/"){
                        $konten=array();
                            $crawler->filter('.content-hardnews .c-celebrity a')->each(function($node) use(&$konten){
                            $konten[]=$node->text();
                        });

                        $crawler->filter('.content-hardnews .c-celebrity a')->each(function($node) use(&$title){
                            $title[]=$node->attr('title');
                        });

                        $crawler->filter('.content-hardnews .c-celebrity a')->each(function($node) use(&$list_url){
                            $list_url[]=$node->attr('href');
                        });
                    }else if($url=="https://techno.okezone.com/indeks"){
                        $konten=array();
                        $crawler->filter('.content-hardnews .c-techno a')->each(function($node) use(&$konten){
                            $konten[]=$node->text();
                        });

                        $crawler->filter('.content-hardnews .c-techno a')->each(function($node) use(&$title){
                            $title[]=$node->attr('title');
                        });

                        $crawler->filter('.content-hardnews .c-techno a')->each(function($node) use(&$list_url){
                            $list_url[]=$node->attr('href');
                        });
                    }
                    // else if($url=="https://infografis.okezone.com/indeks")
                    // {
                    //     $konten=array();
                    //     $kontenlast=array();
                    //     $crawler->filter('.content-hardnews h3 a')->each(function($node) use(&$title, &$url, &$tanggal, &$konten, &$kontenlast){
                    //         $url[]=$node->link()->getUri();
                
                    //         $client = new Client();
                    //         $detail = $client->request('GET', $node->link()->getUri());
                
                    //         $detail->filter('.container-bodyhome-left div.title h1')->each(function ($node_detail) use(&$title){
                    //             $title[]=$node_detail->text();
                    //         });
                
                    //         $detail->filter('.container-bodyhome-left div.container-top div.namerep b')->each(function ($node_detail) use(&$tanggal){
                    //             $tanggal[]=$node_detail->text();
                    //         });
                
                    //         $detail->filter('.container-bodyhome-left div.breadcrumb ul')->each(function ($node_detail) use(&$konten, &$kontenlast){
                    //             $kontenlast=explode(" ",$node_detail->text());
                    //             $konten[]=array_pop($kontenlast);
                    //         });
                    //     });
                    // }
                    else{

                        $konten=array();
                            $crawler->filter('.content-hardnews .c-news a')->each(function($node) use(&$konten){
                            $konten[]=$node->text();
                        });

                        $crawler->filter('.content-hardnews .c-news a')->each(function($node) use(&$title){
                            $title[]=$node->attr('title');
                        });

                        $crawler->filter('.content-hardnews .c-news a')->each(function($node) use(&$list_url){
                            $list_url[]=$node->attr('href');
                        });
                    }
                    
                    $tanggal=array();
                    $crawler->filter('time.category-hardnews')->each(function($node) use(&$tanggal){
                        $tanggal[]=$node->text();
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

                                $cek=strpos($tanggal[$s],"WIB",0)-1;

                                $tgl=date('Y-m-d');;

                                $wib=substr($tanggal[$s],0,$cek);

                                $jam=substr($wib,-5);

                                $param->portal_publish = $tgl." ".$jam.":00";

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
                    
                    $this->info('Selesai Menarik Data Kanal Artikel Index');
                }else{
                    $this->info("Menarik Data Kanal Artikel Populer ". $kan->url_kanal);

                    $client2 = new Client();
                    $url2 = $kan->url_kanal;
                    $crawler2 = $client2->request('GET', $url2);

                    $konten2=array();
                    $crawler2->filter('.content-hardnews span')->each(function($node) use(&$konten2){
                        $konten2[]=$node->text();
                    });

                    $title2=array();
                    $crawler2->filter('.content-hardnews h4 a')->each(function($node2) use(&$title2){
                        $title2[]=$node2->text();
                    });

                    $url2=array();
                    $crawler2->filter('.content-hardnews h4 a')->each(function($node2) use(&$url2){
                        $url2[]=$node2->attr('href');
                    });

                    $tanggal2=array();
                    $crawler2->filter('time.category-hardnews')->each(function($node2) use(&$tanggal2){
                        $tanggal2[]=$node2->text();
                    });

                    if(count($title2) == count($url2)){
                        foreach($title2 as $s=>$t)
                        {
                            $cek = \App\Models\Scrap\ScrapParam::where('judul_artikel',$t)
                            ->orWhere('link_artikel',$url2[$s])
                            ->first();
                            
                            if($cek == null)
                            {
                                $param = new \App\Models\Scrap\ScrapParam;
                                $param->tanggal = date('Y-m-d');
                                $param->jam = date('H:i:s');

                                //cek artikel masuk kategori apa
                                $cat = \App\Models\Scrap\LinkKategori::where('portal_id',$list_portal->id)
                                    ->where('name_link_kategori', $konten2[$s])
                                    ->first();

                                    if($cat == null){
                                        $param->kategori_id = 0;
                                    }  else{
                                        $param->kategori_id = $cat['kategori_id'];
                                    }

                                //cek artikel masuk subkategori apa
                                $sub = \App\Models\Scrap\LinkSubKategori::where('portal_id',$list_portal->id)
                                    ->where('name_link_subkategori', $konten2[$s])
                                    ->first();
                                    
                                    if($sub == null){
                                         $param->subkategori_id = 0;
                                    }  else{
                                         $param->subkategori_id = $sub['subkategori_id'];
                                    }

                                $param->kanal_id = $kan->id;
                                $param->judul_artikel = $t;
                                $param->link_artikel = $url2[$s];
                                $param->tanggal_publish = $tanggal2[$s];
                                $param->konten = $konten2[$s];

                                $cek=strpos($tanggal2[$s],"WIB",0)-1;

                                $tgl=date('Y-m-d');;

                                $wib=substr($tanggal2[$s],0,$cek);

                                $jam=substr($wib,-5);

                                $param->portal_publish = $tgl." ".$jam.":00";
                                
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

                    $this->info('Selesai Menarik Data Kanal Artikel Populer');
                }
            }
        }
        
        $this->info('Selesai');
    }
}
        
        