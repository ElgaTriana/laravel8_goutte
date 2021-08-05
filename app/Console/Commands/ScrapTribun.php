<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;

class ScrapTribun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:tribun';

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
        )->where('id',4)
        ->get();


        $this->info('Memulai');

        foreach($list_portal as $key=>$val)
        {

            // Untuk Menyimpan tags
            $tags = array();
            $this->info('Tag == '.$val->name_portal);
            
            $client = new Client();
            $crawler = $client->request('GET', $val->url_portal);

            $crawler->filter('.pt20')->each(function ($node) use(&$tags){
                $node->filter('h5.tagcloud')->each(function($t) use(&$tags){
                    $tags[]=$t->text();
                });
            });

            foreach($tags as $s=>$v)
            {
                $cekTag = \App\Models\Scrap\Tag::where('portal_id', $val->id)
                    ->where('tanggal', date('Y-m-d'))
                    ->where('tag', $v)
                    ->count();

                if($cekTag == 0)
                {
                    $tg = new \App\Models\Scrap\Tag;
                    $tg->portal_id = $val->id;
                    $tg->tanggal  = date('Y-m-d');
                    $tg->jam = date('H:i:s');
                    $tg->tag = $v;
                    $tg->save();
                }
            }

            foreach($val->kanal as $kan)
            {
                $client = new Client();
                $url = $kan->url_kanal;

                $this->info($url);
                $this->info('Kanal === '.$kan->kanal_name);

                $list = array();
                if($kan->bisa_scrap == "Y")
                {
                    if($kan->type_kanal == "Artikel")
                    {
                        $crawler = $client->request('GET', $url);

                            if($kan->type == "terpopuler")
                            {
                                $crawler->filter('.populer')->each(function ($node) use(&$list, &$kan, &$val){
                                    $title=array();
                                    $node->filter('ul > li.art-list h3')->each(function($t) use(&$title){
                                        $title[]=$t->text();
                                    });
        
                                    $list_url = array();
                                    $node->filter('ul > li.art-list a')->each(function($t) use(&$list_url){
                                        $list_url[]= $t->link()->getUri();
                                    });
                                    $list_url = array_values(array_unique($list_url));
        
                                    $tanggal = array();
                                    $node->filter('ul > li.art-list time')->each(function($t) use(&$tanggal){
                                        $tanggal[]= $t->attr("title");
                                    });
        
                                    $list = array(
                                        'title'=>$title,
                                        'url'=>$list_url,
                                        'tanggal'=>$tanggal,
                                        'dibaca'=>array()
                                    );
        
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
                                            } else{
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
                                                    $p->portal_id = $val->id;
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
                                });
                            }else if($kan->type == "indeks")
                            {
                                $title=array();
                                $crawler->filter('h3.fbo > a')->each(function ($node) use(&$title) {
                                    $title[]=$node->text();
                                });

                                $list_url = array();
                                $crawler->filter('h3.f16 > a')->each(function ($node) use(&$list_url){
                                    $list_url[]=$node->attr("href");
                                });

                                $tanggal=array();
                                $crawler->filter('ul.lsi > li.ptb15 > .grey')->each(function ($node) use(&$tanggal){
                                    $tanggal[]=$node->text();
                                });

                                $subkanal=array();
                                $crawler->filter('h4.f14 > a')->each(function ($node) use(&$subkanal){
                                    $subkanal[]=array(
                                        'text'=>$node->text(),
                                        'url'=>$node->attr("href")
                                    );
                                });

                                $list=array(
                                    'title'=>$title,
                                    'url'=>$list_url,
                                    'tanggal'=>$tanggal,
                                    'subkanal'=>$subkanal,
                                    'dibaca'=>array()
                                );

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
                                        } else{
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
                                                $p->portal_id = $val->id;
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
                            }
                    }elseif($kan->type_kanal == "Video")
                    {
                        
                    }
                }
            }
           
        }

        $this->info('Update Summary Portal');
        $set_all_parameter = \DB::connection('mysql4')
            ->select("select c.name_portal,c.id as portal_id, a.*
            from scrap_portal_parameter a 
            left join scrap_portal_kanal b on b.id=a.kanal_id
            left join scrap_portal c on c.id=b.portal_id
            WHERE c.name_portal IS NOT NULL 
            AND DATE_FORMAT(a.tanggal,'%Y-%m-%d')=curdate()
            GROUP BY a.link_artikel");

        $list = array();
        $hasil = array();

        $set_tanggal = array();

        foreach($set_all_parameter as $key=>$val)
        {
            $name_portal[]=$val->name_portal;
            
            $set_tanggal[] = array(
                'name_portal'=>$val->name_portal,
                'tanggal'=>$val->tanggal
            );
        }

        $name_portal = array_unique($name_portal);
        usort($set_tanggal, function($a, $b) {
            return $a['tanggal'] <=> $b['tanggal'];
        });

        $final_tanggal = array();
        foreach($set_tanggal as $tg)
        {
            $final_tanggal [] =$tg['tanggal'];
        }

        $final_tanggal = array_unique($final_tanggal);

        foreach($name_portal as $key=>$val){
            foreach($final_tanggal as $fin)
            {
                $jumlah = 0;

                foreach($set_all_parameter as $param)
                {
                    if($param->name_portal == $val)
                    {
                        if($param->tanggal == $fin)
                        {
                            $jumlah ++;
                        }
                    }
                }

                $list[]= array(
                    'name_portal'=>$val,
                    'tanggal'=>date('d M Y', strtotime($fin)),
                    'jumlah'=>$jumlah
                );

                $cek_dulu= \DB::connection('mysql4')
                    ->table('scrap_portal_summary')
                    ->where('name_portal',$val)
                    ->where('tanggal', date('Y-m-d', strtotime($fin)))
                    ->count();

                if($cek_dulu > 0)
                {
                    \DB::connection('mysql4')
                        ->table('scrap_portal_summary')
                        ->where('name_portal',$val)
                        ->where('tanggal', date('Y-m-d', strtotime($fin)))
                        ->update(
                            [
                                'jumlah'=>$jumlah
                            ]
                        );
                }else{
                    \DB::connection('mysql4')
                        ->table('scrap_portal_summary')
                        ->insert(
                            [
                                'name_portal'=>$val,
                                'tanggal'=>date('Y-m-d', strtotime($fin)),
                                'jumlah'=>$jumlah
                            ]
                        );
                }
            }
        }


        $this->info('Selesai');
    }
}
