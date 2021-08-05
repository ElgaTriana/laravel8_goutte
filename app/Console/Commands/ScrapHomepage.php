<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Goutte\Client;

class ScrapHomepage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:homepage';

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
        $list_portal = \App\Models\Scrap\Portal::select('id', 'name_portal', 'url_portal')
        ->whereNull('deleted_at')
        ->get();

        $this->info('Memulai');
        
        foreach($list_portal as $kan)
        {
            $this->info('Menarik Data == '.$kan->name_portal);

            if($kan->id == 2){

                $url = "https://www.detik.com/";
                $client = new Client();
                $crawler = $client->request('GET', $url);

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
                });

                if(count($judul) == count($tanggal_artikel)){

                    foreach ($judul as $key => $value) {
                        $cek = \App\Models\Scrap\Parameter_homepage::where('judul_artikel',$value)
                        ->orWhere('link_artikel',$link_artikel[$key])
                        ->first();   

                        if($cek==null){
                            $param = new \App\Models\Scrap\Parameter_homepage;
                            $param->tanggal = date('Y-m-d');
                            $param->jam = date("H:i:s");
                            $param->portal_id = $kan->id;
                            $param->judul_artikel = $value;
                            $param->link_artikel = $link_artikel[$key];
                            $param->tanggal_publish = $tanggal_artikel[$key];

                            $cek=strpos($tanggal_artikel[$key],"WIB",0)-1;
                            
                            $tgl=date('Y-m-d');

                            $wib=substr($tanggal_artikel[$key],0,$cek);

                            $jam=substr($wib,-5);
                            $param->portal_publish=$tgl." ".$jam.":00";
                            
                            $simpanparam = $param->save();

                            $this->info('Simpan Artikel = '. $value);
                        }
                    }
                }

                $this->info('===============================');

                $this->info('Simpan Homepage Detik Berhasil');
                
                $this->info('===============================');
            }else if($kan->id == 3){
                
                $this->info('===============================');

                $this->info('Simpan Homepage Kompas Berhasil');
                
                $this->info('===============================');

            }else if($kan->id == 4){
                $this->info('===============================');

                $this->info('Simpan Homepage Tribun Berhasil');
                
                $this->info('===============================');
            }else if($kan->id == 5){
                $this->info('===============================');

                $this->info('Simpan Homepage Liputan 6 Berhasil');

                $this->info('===============================');
            }else if($kan->id == 6){
                $this->info('===============================');
                
                $this->info('Simpan Homepage iNews.id Berhasil');
                
                $this->info('===============================');
            }else if($kan->id == 7){
                $this->info('===============================');

                $this->info('Simpan Homepage Sindonews Berhasil');
                
                $this->info('===============================');
            }else if($kan->id == 7){
                $this->info('===============================');

                $this->info('Simpan Homepage Okezone Berhasil');

                $this->info('===============================');
            }
        }
        $this->info('Data homepage sudah di update');
    }
}
