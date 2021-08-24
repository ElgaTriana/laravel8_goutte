<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Goutte\Client;

class ScrapKapanlagi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:kapanlagi';

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
        $list_portal = \App\Models\Scrap\Portal::with([
            'kanal',
            'kanal.subkanal'
            ]
        )->find(13);
                
        $this->info('Memulai');
            
        foreach($list_portal->kanal as $kan)
        {
            if($kan->type_kanal=="Foto")
            {

                $this->info("Menarik Data Kanal Foto ".$kan->url_kanal);

                $client = new Client();
                
                $url = $kan->url_kanal;

                $crawler = $client->request('GET', $url);
                
                $title=array();
        
                $tanggal=array();
                        
                $list_url=array();
                
                $konten=array();
                        
                $crawler->filter('.element.trending-box ul li')->each(function ($node) use(&$title) {
                    $title[]=$node->text();
                });
        
                $crawler->filter('.element.trending-box ul li a')->each(function ($node) use(&$list_url, &$konten, &$tanggal) {
                    $list_url[]=$node->link()->getUri();
        
                    $client = new Client();
                    $detail = $client->request('GET', $node->link()->getUri());
        
                    $detail->filter('.headline-detail div.col-dt-left span.date-post')->each(function ($node_detail) use(&$tanggal) {
                        $tanggal[]=$node_detail->text();
                    });
        
                    $konten[]="FOTO";
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

                            $cek=strlen($tanggal[$s]);

                            $tgl=date('Y-m-d');

                            $jam=substr($tanggal[$s],-5);

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

                $this->info('BERHASIL MENARIK DATA KANAL FOTO');  
            }
            else if($kan->type_kanal=="Video")
            {
                $this->info("Menarik Data Kanal Video ".$kan->url_kanal);

                $client = new Client();
                
                $url = $kan->url_kanal;

                $crawler = $client->request('GET', $url);

                $title=array();
        
                $tanggal=array();
                
                $list_url=array();
        
                $konten=array();
                
                $crawler->filter('#v6-tags-populer #tagli img')->each(function ($node) use(&$title) {
                    $title[]=$node->attr('alt');
                });
        
                $crawler->filter('#v6-tags-populer #tagli div.desc a')->each(function ($node) use(&$list, &$list_url, &$konten, &$tanggal, &$title) {
                    $list_url[]=$node->link()->getUri();
        
                    if (($key = array_search("https://video.kapanlagi.com/", $list_url)) !== false) {
                        unset($list_url[$key]);
                    }
        
                    $client = new Client();
                    $detail = $client->request('GET', $node->link()->getUri());
        
                    $detail->filter('.headline-detail-video div.col-dt-headline span.date-post')->each(function ($node_detail) use(&$tanggal) {
                        $tanggal[]=$node_detail->text();
                    });                    
                }); 
                
                $this->info('TOTAL JDL = '.count($title));

                $this->info('TOTAL URL = '.count($list_url));

                $this->info('TOTAL TGL = '.count($tanggal));
                
                $list_urlnya=array_values($list_url);
 
                foreach($title as $s=>$t)
                {
                    $cek = \App\Models\Scrap\Parameter::where('judul_artikel',$t)
                    ->orWhere('link_artikel',$list_urlnya[$s])
                    ->first();

                    if($cek == null)
                    {
                        $param = new \App\Models\Scrap\Parameter;
                        
                        $param->tanggal = date('Y-m-d');
                        
                        $param->jam = date('H:i:s');

                        $param->kanal_id = $kan->id;
                        
                        $param->judul_artikel = $t;
                        
                        $param->link_artikel = $list_urlnya[$s];
                        
                        $param->tanggal_publish = $tanggal[$s];
                        
                        $param->konten = "VIDEO";

                        $kategori = \App\Models\Scrap\LinkKategori::where('portal_id',2)
                        ->where('name_link_kategori', "VIDEO")
                        ->first();

                        if($kategori == null)
                        {
                            $param->kategori_id = 0;
                        }  
                        else
                        {
                            $param->kategori_id = $kategori['kategori_id'];
                        }

                        $subkategori = \App\Models\Scrap\LinkSubKategori::where('portal_id',2)
                        ->where('name_link_subkategori', "VIDEO")
                        ->first();

                        if($subkategori == null)
                        {
                            $param->subkategori_id = 0;
                        }
                        else
                        {
                            $param->subkategori_id = $subkategori['subkategori_id'];
                        }

                        $cek=strlen($tanggal[$s]);

                        $tgl=date('Y-m-d');

                        $jam=substr($tanggal[$s],-5);

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
                            $p->portal_id = $val->id;
                            $p->save();
                        }
                    }
                }
                $this->info("Berhasil Menarik Kanal Video");
            }
            else if($kan->type_kanal=="Artikel")
            {
                $this->info("Memulai Kanal Artikel");
                if($kan->type == "indeks"){

                    $this->info("Menarik Data Kanal Artikel Index ". $kan->url_kanal);

                    $client = new Client();
                    
                    $url = $kan->url_kanal;
                    
                    $crawler = $client->request('GET', $url);

                    $title=array();

                    if($url === "https://www.kapanlagi.com/film/indonesia" || $url === "https://www.kapanlagi.com/film/internasional")
                    {
                        $list_url=array();

                        $tanggal=array();

                        $konten=array();

                        $title=array();

                        $crawler->filter('.entertainment-category-item h3 a')->each(function ($node) use(&$title) {
                            $title[]=$node->text();     
                        });
                        
                        $crawler->filter('.entertainment-category-item h3 a')->each(function ($node) use(&$list_url, &$tanggal, &$konten) {
                            $list_url[]=$node->link()->getUri();
                
                            $konten[]=strtoupper(explode("/", $node->attr('href'))[2]);
                
                            $client = new Client();
                            $detail = $client->request('GET', $node->link()->getUri());
                
                            $tanggal[]=$detail->filter('#newsdetail-right-new span.newsdetail-schedule-new')->first()->text();
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

                                    $cek=strlen($tanggal[$s]);

                                    $tgl=date('Y-m-d');

                                    $jam=substr($tanggal[$s],-5);

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

                        $this->info('BERHASIL MENARIK DATA KANAL FILM INDONESIA & INTERNASIONAL');
                    }
                    elseif($url === "https://www.kapanlagi.com/dangdut")
                    {
                        $title=array();
                        
                        $list_url=array();
                        
                        $tanggal=array();
                        
                        $konten=array();
                        
                        $crawler->filter('.col-vid-art p a')->each(function ($node) use(&$title) {
                            $title[]=$node->text();
                        });
                        
                        $crawler->filter('.col-vid-art p a')->each(function ($node) use(&$list_url, &$tanggal, &$konten) {
                            $list_url[]=$node->link()->getUri();

                            $client = new Client();
                            $detail = $client->request('GET', $node->link()->getUri());

                            $konten[]="KABAR DANGDUT";

                            $detail->filter('.col-dt-left span.date-post')->each(function ($node_detail) use(&$tanggal){
                                $tanggal[]=$node_detail->text();
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

                                    $cek=strlen($tanggal[$s]);

                                    $tgl=date('Y-m-d');

                                    $jam=substr($tanggal[$s],-5);

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

                        $this->info('BERHASIL MENARIK DATA KANAL DANGDUT');
                    }
                    else if($url === "https://www.kapanlagi.com/intermezzone"){
                        $title=array();
                        $crawler->filter('#newsdetail-left h1 a')->each(function ($node) use(&$title) {
                            $title[]=$node->text();
                        });

                        $list_url=array();
                        $tanggal=array();
                        $konten=array();

                        $crawler->filter('#newsdetail-left h1 a')->each(function ($node) use(&$list_url, &$tanggal, &$konten) {
                            $list_url[]=$node->link()->getUri();

                            $client = new Client();
                            $detail = $client->request('GET', $node->link()->getUri());

                            $konten[]=$detail->filter('#v5-navigation a')->last()->text();

                            $detail->filter('#newsdetail-right-new span.newsdetail-schedule-new')->each(function ($node_detail) use(&$tanggal){
                                $tanggal[]=$node_detail->text();
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

                                    $cek=strlen($tanggal[$s]);

                                    $tgl=date('Y-m-d');

                                    $jam=substr($tanggal[$s],-5);

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

                        $this->info('BERHASIL MENARIK DATA KANAL INTERMEZZONE');
                    }
                    else if($url === "https://www.kapanlagi.com/korea")
                    {
                        $title=array();
                        
                        $list_url=array();
                        
                        $tanggal=array();
                        
                        $konten=array();
                        
                        $crawler->filter('.klk-updates-list div.article div.article-description')->each(function ($node) use(&$title) {
                            $title[]=$node->text();
                        });
                
                        $crawler->filter('.klk-updates-list div.article div.article-description p a')->each(function ($node) use(&$list_url, &$tanggal, &$konten) {
                            $list_url[]=$node->link()->getUri();
                
                            $client = new Client();
                            $detail = $client->request('GET', $node->link()->getUri());
                
                            $konten[]="KOREA";
                            
                            $detail->filter('.col-dt-headline span.date-post')->each(function ($node_detail) use(&$tanggal){
                                $tanggal[]=$node_detail->text();
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

                                    $cek=strlen($tanggal[$s]);

                                    $tgl=date('Y-m-d');

                                    $jam=substr($tanggal[$s],-5);

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

                        $this->info('BERHASIL MENARIK DATA KANAL KOREA');
                    }
                    else if($url === "https://musik.kapanlagi.com/")
                    {
                        $title=array();
                        $crawler->filter('.musik-box div.music-artikel div.deskrip-music-artikel h2')->each(function ($node) use(&$title) {
                            $title[]=$node->text();
                        });

                        $tanggal=array();
                        $crawler->filter('.music-kategori-box')->each(function ($node) use(&$tanggal) {
                            $tanggal[]=$node->text();
                        });

                        $list_url=array();
                        $crawler->filter('.musik-box div.music-artikel div.deskrip-music-artikel a')->each(function ($node) use(&$list_url, &$konten) {

                            $list_url[]=$node->attr('href');

                            $konten[]="BERITA";

                            $client = new Client();

                            $detail = $client->request('GET', $node->link()->getUri());
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

                                    $cek=strlen($tanggal[$s]);

                                    $tgl=date('Y-m-d');

                                    $jam=substr($tanggal[$s],-5);

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

                        $this->info('BERHASIL MENARIK DATA KANAL MUSIK');
                    }
                    else if($url === "https://plus.kapanlagi.com/berita")
                    {
                        $title=array();

                        $tanggal=array();
                        
                        $konten=array();
                        
                        $list_url=array();
                        
                        $crawler->filter('.clearfix div.deskrip-klplus h6')->each(function ($node) use(&$title) {
                            $title[]=$node->text();
                        });
                        $crawler->filter('.clearfix div.deskrip-klplus h6 a')->each(function ($node) use(&$list_url, &$konten, &$tanggal) {
                            $list_url[]=$node->link()->getUri();
                            
                            $client = new Client();
                            $detail = $client->request('GET', $node->link()->getUri());
                
                            $konten[]=$detail->filter('#v5-navigation a')->last()->text();
                
                            $detail->filter('.col-dt-left span.date-post')->each(function ($node_detail) use(&$tanggal) {
                                $tanggal[]=$node_detail->text();
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

                                    $cek=strlen($tanggal[$s]);

                                    $tgl=date('Y-m-d');

                                    $jam=substr($tanggal[$s],-5);

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

                        $this->info('BERHASIL MENARIK DATA KANAL PLUS KAPANLAGI');
                    }
                    else if($url === "https://www.kapanlagi.com/showbiz/")
                    {
                        $title=array();
                        
                        $tanggal=array();
                        
                        $list_url=array();
                        
                        $crawler->filter('#entertainment-headline ul li div.thumbinfo')->each(function ($node) use(&$title) {
                            $title[]=$node->text();
                        });

                        $crawler->filter('#entertainment-headline ul li a')->each(function ($node) use(&$list_url, &$konten, &$tanggal) {
                            $list_url[]=$node->link()->getUri();

                            $client = new Client();
                            $detail = $client->request('GET', $node->link()->getUri());

                            if(explode("/", $node->link()->getUri())[4]==""){
                                $konten[]=explode("/", $node->link()->getUri())[3];
                            }else{
                                $konten[]=explode("/", $node->link()->getUri())[4];
                            }

                            $detail->filter('.col-dt-left span.date-post')->each(function ($node_detail) use(&$tanggal) {
                                $tanggal[]=$node_detail->text();
                            });
                        });

                        $list_url=array_values(array_unique($list_url));
                        $tanggal=array_values(array_unique($tanggal));

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

                                    $cek=strlen($tanggal[$s]);

                                    $tgl=date('Y-m-d');

                                    $jam=substr($tanggal[$s],-5);

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

                        $this->info('BERHASIL MENARIK DATA KANAL SHOWBIZ KAPANLAGI');
                    }
                }
            }
        }
    }
}
