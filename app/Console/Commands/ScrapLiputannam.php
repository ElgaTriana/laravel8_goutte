<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;

class ScrapLiputannam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:liputannamnew';

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
        )->find(5);

        $this->info('Memulai');

        foreach($list_portal->kanal as $kan)
        {
            if ($kan->url_kanal!='https://www.liputan6.com/hot/indeks'){

            if($kan->type == "indeks")
            {
                $this->info('Menarik Data yaaaa== '.$kan->url_kanal);

                $client = new Client();
                $url = $kan->url_kanal;
                $crawler = $client->request('GET', $url);

                $title = array();
                $crawler->filter('h4.articles--rows--item__title')->each(function($node) use(&$title){
                    // dump($node->text());
                    $title[]= $node->text();
                });

                $konten = array();
                $crawler->filter('.articles--rows--item__category')->each(function($node) use(&$konten){
                    // dump($node->text());
                    $konten[]= $node->text();
                });

                $urlnya = array();
                $crawler->filter('.articles--rows--item__category')->each(function($node) use(&$urlnya){
                    // dump($node->attr('href'));    
                    $urlnya[]= $node->attr("href");
                });

                $list_url = array();
                $crawler->filter('a.articles--rows--item__title-link')->each(function($node) use(&$list_url){
                    $list_url[]= $node->attr("href");
                });

                $tanggal = array();
                $crawler->filter('.articles--rows--item__time')->each(function($node) use(&$tanggal){
                    $tanggal[]= $node->text();
                });

                if(count($title) == count($tanggal))
                {
                    foreach($title as $s=>$t)
                    {   

                        $cari=json_encode($t);
                        $cek = \App\Models\Scrap\Parameter::where('judul_artikel',$cari)
                            ->orWhere('link_artikel',$list_url[$s])
                            ->first();

                        if($cek == null)
                        {
                            $param = new \App\Models\Scrap\Parameter;
                            $param->tanggal = date('Y-m-d');
                            $param->jam = date('H:i:s');

                            //cek artikel masuk kategori apa
                            $cat = \App\Models\Scrap\LinkKategori::where('portal_id',5)
                                    ->where('name_link_kategori', $konten[$s])
                                    ->first();
                                   if($cat == null){
                                        $param->kategori_id = 0;
                                   }  else{
                                        $param->kategori_id = 10;
                                   }

                            //cek artikel masuk subkategori apa
                            $sub = \App\Models\Scrap\LinkSubKategori::where('portal_id',5)
                                    ->where('name_link_subkategori', $konten[$s])
                                    ->first();
                                   if($sub == null){
                                        $param->subkategori_id = 0;
                                   }  else{
                                        $param->subkategori_id = $sub['subkategori_id'];
                                   }
                            $param->konten=$konten[$s];
                            $param->kanal_id = $kan->id;
                            $param->judul_artikel = $t;
                            $param->link_artikel = $list_url[$s];
                            $param->tanggal_publish = $tanggal[$s];

                            $cek=strlen($tanggal[$s]);

                            $tgl=date('Y-m-d');

                            $jam=substr($tanggal[$s],-5);

                            $param->portal_publish=$tgl." ".$jam.":00";

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
            }else{

                $this->info('Menarik Data Kanal Populer == '.$kan->url_kanal);

                $client = new Client();
                $url = $kan->url_kanal;
                $crawler = $client->request('GET', $url);

                $title = array();
                $crawler->filter('h4.articles--iridescent-list--text-item__title')->each(function($node) use(&$title){
            // dump($node->text());
                    $title[]= $node->text();
                });

                $konten = array();
                $crawler->filter('.articles--iridescent-list--text-item__category')->each(function($node) use(&$konten){
                    // dump($node->text());
                    $konten[]= $node->text();
                });

                $urlnya = array();
                $crawler->filter('.articles--iridescent-list--text-item__category')->each(function($node) use(&$urlnya){
                    // dump($node->attr('href'));    
                    $urlnya[]= $node->attr("href");
                });

                $list_url = array();
                $crawler->filter('h4.articles--iridescent-list--text-item__title a')->each(function($node) use(&$list_url){
                    $list_url[]= $node->attr("href");
                });

                $tanggal = array();
                $crawler->filter('span.articles--iridescent-list--text-item__datetime')->each(function($node) use(&$tanggal){
                    $tanggal[]= $node->text();
                });

                if(count($title) == count($tanggal))
                {
                    foreach($title as $s=>$t)
                    {
                        $cari=json_encode($t);
                        $cek = \App\Models\Scrap\Parameter::where('judul_artikel',$cari)
                            ->orWhere('link_artikel',$list_url[$s])
                            ->first();

                        if($cek == null)
                        {
                            $param = new \App\Models\Scrap\Parameter;
                            $param->tanggal = date('Y-m-d');
                            $param->jam = date('H:i:s');
                            $param->kanal_id = $kan->id;
                            
                            // //cek artikel masuk kategori apa
                            // $cat = \App\Models\Scrap\LinkKategori::where('portal_id',5)
                            //         ->where('name_link_kategori', $konten[$s])
                            //         ->first();
                                    
                            //     if($cat == null){
                            //         $param->kategori_id = 0;
                            //     }  else{
                            //         $param->kategori_id = $cat['kategori_id'];
                            //     }

                            // //cek artikel masuk subkategori apa
                            // $sub = \App\Models\Scrap\LinkSubKategori::where('portal_id',5)
                            //         ->where('name_link_subkategori', $konten[$s])
                            //         ->first();
                                    
                            //     if($sub == null){
                            //          $param->subkategori_id = 0;
                            //     }  else{
                            //          $param->subkategori_id = $sub['subkategori_id'];
                            //     }

                            $param->judul_artikel = $t;
                            $param->link_artikel = $list_url[$s];
                            $param->tanggal_publish = $tanggal[$s];

                            $cek=strlen($tanggal[$s]);
                            $tgl=date('Y-m-d');
                            $jam=substr($tanggal[$s],-5);
                            $param->portal_publish=$tgl." ".$jam.":00";
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
                                    // $p->kategori_id = $param->kategori_id;
                                    // $p->subkategori_id = $param->subkategori_id;
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
                                // $p->kategori_id = $cek->kategori_id;
                                // $p->subkategori_id = $cek->subkategori_id;
                                $p->save();
                            }
                        }
                    }
                }
            }
        }
    }
        $this->info('Selesai');
    }
}
