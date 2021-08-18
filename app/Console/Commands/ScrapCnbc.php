<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Goutte\Client;

class ScrapCnbc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:cnbc';

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
        )->find(10);
                
        $this->info('Memulai');
            
        foreach($list_portal->kanal as $kan)
        {
            if($kan->type_kanal=="Foto"){
                $this->info("Menarik Data Kanal Foto ".$kan->url_kanal);

                $client = new Client();
                $url = $kan->url_kanal;
                $crawler = $client->request('GET', $url);

                $title=array();
                $crawler->filter('.nhl_foto h2')->each(function ($node) use(&$title) {
                    $title[]=$node->text();
                });

                $list_url=array();
                $crawler->filter('.nhl_foto div.col_mob_9 a')->each(function ($node) use(&$list_url) {
                    $list_url[]= $node->attr('href');
                    // dump($list_url);
                });
        
                $tanggal=array();
                $crawler->filter('.nhl_foto article a')->each(function($node) use(&$tanggal){
                    $client = new Client();
                    $detail = $client->request('GET', $node->link()->getUri());
        
                    $detail->filter('.detail_box div.date')->each(function ($node_detail) use(&$tanggal){
                        // dump($node_detail->text());
                        $tanggal[]=$node_detail->text();
                        
                    });
                });

                $tanggal_unik=array_values(array_unique($tanggal));

                if(count($title) == count($tanggal_unik))
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
                                    ->where('name_link_kategori', "Foto")
                                    ->first();

                                    if($cat == null){
                                        $param->kategori_id = 0;
                                    }  else{
                                        $param->kategori_id = $cat['kategori_id'];
                                    }

                            //cek artikel masuk subkategori apa
                            $sub = \App\Models\Scrap\LinkSubKategori::where('portal_id',$list_portal->id)
                                    ->where('name_link_subkategori', "Foto")
                                    ->first();
                                    if($sub == null){
                                        $param->subkategori_id = 0;
                                    }  else{
                                        $param->subkategori_id = $sub['subkategori_id'];
                                    }


                            $param->kanal_id = $kan->id;
                            $param->judul_artikel = $t;
                            $param->link_artikel = $list_url[$s];
                            $param->tanggal_publish = $tanggal_unik[$s];
                            $param->konten = "Foto";

                            $cek=strlen($tanggal_unik[$s]);

                            $tgl=date('Y-m-d');

                            $jam=substr($tanggal_unik[$s],-5);

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
                $url = $kan->url_kanal;
                $crawler = $client->request('GET', $url);

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

                $this->info('Selesai Menarik Data Kanal Video');
            }else if($kan->type_kanal=="Artikel"){
                $this->info("Memulai Kanal Artikel");
                if($kan->type == "indeks"){

                    $this->info("Menarik Data Kanal Artikel Index ". $kan->url_kanal);

                    $client = new Client();
                    
                    $url = $kan->url_kanal;
                    
                    $crawler = $client->request('GET', $url."?date=".date('Y/m/d'));

                    $title=array();
                    $crawler->filter('.lm_content ul.list li h2')->each(function ($node) use(&$title) {
                        // dump($node->text()); 
                        $title[]=$node->text();
                    });
            
                    $tanggal=array();
                    $konten=array();
                    $list_url=array();
                        $crawler->filter('.lm_content ul.list li a')->each(function($node) use(&$list_url, &$tanggal, &$konten){
                        $list_url[]= $node->link()->getUri();
            
                        $client = new Client();
                        $detail = $client->request('GET', $node->link()->getUri());
            
                        $detail->filter('.detail_box div.date')->each(function ($node_detail) use(&$tanggal){
                            $tanggal[]=$node_detail->text();
                        });
            
                        $detail->filter('.detail_box div.author span.label')->each(function ($node_detail) use(&$konten){
                            $konten[]=explode(" ", $node_detail->text())[0];
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
                    
                    $this->info('Selesai Menarik Data Kanal Artikel Index');
                }
            }
        }
    }
}
