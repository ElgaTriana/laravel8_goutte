<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Goutte\Client;

class ScrapIdxchannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:idx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrapping idx channel';

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
        )->find(11);

        $this->info('Memulai');

        foreach($list_portal->kanal as $kan)
        {
            if($kan->type == "indeks")
            {
                $this->info('Menarik Data == '.$kan->url_kanal);
                
                $client = new Client();
                $url = $kan->url_kanal;
                $crawler = $client->request('GET', $url."&".date('d-m-Y'));

                $title=array();
                $crawler->filter('.bt-con .tab--content .title-capt')->each(function($node) use(&$title){
                    $title[]=$node->text();
                });

                $konten=array();
                $crawler->filter('.bt-con .tab--content> div.headline-date a')->each(function($node) use(&$konten){
                    $konten[]=$node->text();
                });

                $list_url=array();
                $crawler->filter('.bt-con .tab--content .title-capt a')->each(function($node) use(&$list_url){
                    $list_url[]=$node->attr('href');
                    // dump($node->attr('href'));
                });

                $tanggal=array();
                $crawler->filter('.bt-con .tab--content> div.headline-date span.mh-clock')->each(function($node) use(&$tanggal){
                    $tanggal[]=$node->text();
                });
                
                if(count($title) == count($tanggal))
                {
                    foreach($title as $s=>$t)
                    {
                        $cek = \App\Models\Scrap\Parameter::where('judul_artikel',$t)
                        ->orWhere('link_artikel',$list_url[$s])
                        ->first();
                        
                        if($cek == null)
                        {
                            $param = new \App\Models\Scrap\Parameter;
                            $param->tanggal = date('Y-m-d');
                            $param->jam = date('H:i:s');
                            $param->kanal_id = $kan->id;
                            $param->judul_artikel = $t;
                            $param->link_artikel = $list_url[$s];
                            $param->tanggal_publish = $tanggal[$s];
                            $param->konten = $konten[$s];

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

                            $cek=strpos($tanggal[$s],"WIB",0)-1;
                            
                            $tgl=date('Y-m-d');

                            $wib=substr($tanggal[$s],0,$cek);

                            $jam= date('H:i:s');

                            $param->portal_publish = $tgl." ".$jam;
                            
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
                                $p->save();
                            }
                        }
                    }
                }
            }
        }
        
        $this->info('Selesai');


    }
}
