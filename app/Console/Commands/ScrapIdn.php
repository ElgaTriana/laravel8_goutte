<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScrapIdntimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:idn';

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
        $this->info('Mulai');

        $var = \App\Models\Scrap\Parameter_dummy::where('tanggal', date('Y-m-d'))
            ->where('dibaca','N')
            ->get();

        foreach($var as $s=>$t)
        {
            $link_artikel = $t->link_artikel;
            $judul_artikel = $t->judul_artikel;

            $this->info($judul_artikel);

            $cek = \App\Models\Scrap\Parameter::where('judul_artikel',$t->judul_artikel)
                    ->orWhere('link_artikel',$t->link_artikel)
                    ->first();  
                    
            if($cek == null)
            {
                $param = new \App\Models\Scrap\Parameter;
                $param->tanggal = date('Y-m-d');
                $param->jam = date('H:i:s');

                if(stripos($t->link_artikel, 'https://www.idntimes.com/travel') !== FALSE){
                    $param->kanal_id = 210;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/health') !== FALSE){
                    $param->kanal_id = 209;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/life') !== FALSE){
                    $param->kanal_id = 208;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/hype') !== FALSE){
                    $param->kanal_id = 207;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/tech') !== FALSE){
                    $param->kanal_id = 206;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/sport') !== FALSE){
                    $param->kanal_id = 205;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/business') !== FALSE){
                    $param->kanal_id = 204;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/news') !== FALSE){
                        $param->kanal_id = 203;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/trending') !== FALSE){
                        $param->kanal_id = 218;
                }else if(stripos($t->link_artikel, 'https://jogja.idntimes.com/') !== FALSE){
                        $param->kanal_id = 220;
                }else if(stripos($t->link_artikel, 'https://sulsel.idntimes.com/') !== FALSE){
                        $param->kanal_id = 221;
                }else if(stripos($t->link_artikel, 'https://ntb.idntimes.com/') !== FALSE){
                        $param->kanal_id = 222;
                }else if(stripos($t->link_artikel, 'https://jabar.idntimes.com/') !== FALSE){
                        $param->kanal_id = 223;
                }else if(stripos($t->link_artikel, 'https://jatim.idntimes.com/') !== FALSE){
                        $param->kanal_id = 224;
                }else if(stripos($t->link_artikel, 'https://kaltim.idntimes.com/') !== FALSE){
                        $param->kanal_id = 225;
                }else if(stripos($t->link_artikel, 'https://banten.idntimes.com/') !== FALSE){
                        $param->kanal_id = 226;
                }else if(stripos($t->link_artikel, 'https://bali.idntimes.com/') !== FALSE){
                        $param->kanal_id = 227;
                }else if(stripos($t->link_artikel, 'https://sumsel.idntimes.com/') !== FALSE){
                        $param->kanal_id = 228;
                }else if(stripos($t->link_artikel, 'https://jateng.idntimes.com/') !== FALSE){
                        $param->kanal_id = 229;
                }else if(stripos($t->link_artikel, 'https://sumut.idntimes.com/') !== FALSE){
                        $param->kanal_id = 230;
                }else if(stripos($t->link_artikel, 'https://lampung.idntimes.com/') !== FALSE){
                        $param->kanal_id = 231;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/quiz') !== FALSE){
                    $param->kanal_id = 232;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/science') !== FALSE){
                        $param->kanal_id = 233;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/food') !== FALSE){
                        $param->kanal_id = 234;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/fiction') !== FALSE){
                        $param->kanal_id = 235;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/automotive') !== FALSE){
                        $param->kanal_id = 236;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/men') !== FALSE){
                        $param->kanal_id = 237;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/opinion') !== FALSE){
                        $param->kanal_id = 238;
                }else{
                    $param->kanal_id = 0;
                }

                $param->judul_artikel = $t->judul_artikel;
                $param->link_artikel = $t->link_artikel;
                $param->tanggal_publish = $t->tanggal_publish;
                $param->konten = $t->subkanal;

                $kategori = \App\Models\Scrap\LinkKategori::where('portal_id',9)
                ->where('name_link_kategori', $t->subkanal)
                ->first();

                if($kategori == null){
                    $param->kategori_id = 0;
                }  else{
                    $param->kategori_id = $kategori['kategori_id'];
                }

                $subkategori = \App\Models\Scrap\LinkSubKategori::where('portal_id',9)
                ->where('name_link_subkategori', $t->subkanal)
                ->first();

                if($subkategori == null){
                    $param->subkategori_id = 0;
                }  else{
                    $param->subkategori_id = $subkategori['subkategori_id'];
                }

                $word="WIB";

                if(strpos($t->tanggal_publish, $word) !== false){
                    $cek=strpos($t->tanggal_publish,"WIB",0)-1;
                    $tgl=date('Y-m-d');
                    $wib=substr($t->tanggal_publish,0,$cek);
                    $jam=substr($wib,-5);
                    $param->portal_publish=$tgl." ".$jam.":00";
                } else{
                    $cek=strlen($t->tanggal_publish);
                    $tgl=date('Y-m-d');
                    $jam=substr($t->tanggal_publish,-8);
                    $param->portal_publish=$tgl." ".$jam;
                }
                
                $simpanparam = $param->save();
                
                if($simpanparam){

                    $cek_kanal_parameter = \App\Models\Scrap\Kanalparameter::where('parameter_id', $param->id)
                        ->where('kanal_id', $param->kanal_id)
                        ->count();
                    
                    if($cek_kanal_parameter == 0)
                    {
                        $p = new \App\Models\Scrap\Kanalparameter;
                        $p->parameter_id = $param->id;

                        if(stripos($link_artikel, 'https://www.idntimes.com/travel') !== FALSE){
                            $p->kanal_id = 210;
                        }else if(stripos($link_artikel, 'https://www.idntimes.com/health') !== FALSE){
                            $p->kanal_id = 209;
                        }else if(stripos($link_artikel, 'https://www.idntimes.com/life') !== FALSE){
                            $p->kanal_id = 208;
                        }else if(stripos($link_artikel, 'https://www.idntimes.com/hype') !== FALSE){
                            $p->kanal_id = 207;
                        }else if(stripos($link_artikel, 'https://www.idntimes.com/tech') !== FALSE){
                            $p->kanal_id = 206;
                        }else if(stripos($link_artikel, 'https://www.idntimes.com/sport') !== FALSE){
                            $p->kanal_id = 205;
                        }else if(stripos($link_artikel, 'https://www.idntimes.com/business') !== FALSE){
                            $p->kanal_id = 204;
                        }else if(stripos($link_artikel, 'https://www.idntimes.com/news') !== FALSE){
                            $p->kanal_id = 203;
                        }else if(stripos($t->link_artikel, 'https://www.idntimes.com/trending') !== FALSE){
                            $p->kanal_id = 218;
                        }else if(stripos($t->link_artikel, 'https://jogja.idntimes.com/') !== FALSE){
                            $p->kanal_id = 220;
                        }else if(stripos($t->link_artikel, 'https://sulsel.idntimes.com/') !== FALSE){
                            $p->kanal_id = 221;
                        }else if(stripos($t->link_artikel, 'https://ntb.idntimes.com/') !== FALSE){
                            $p->kanal_id = 222;
                        }else if(stripos($t->link_artikel, 'https://jabar.idntimes.com/') !== FALSE){
                            $p->kanal_id = 223;
                        }else if(stripos($t->link_artikel, 'https://jatim.idntimes.com/') !== FALSE){
                            $p->kanal_id = 224;
                        }else if(stripos($t->link_artikel, 'https://kaltim.idntimes.com/') !== FALSE){
                            $p->kanal_id = 225;
                        }else if(stripos($t->link_artikel, 'https://banten.idntimes.com/') !== FALSE){
                            $p->kanal_id = 226;
                        }else if(stripos($t->link_artikel, 'https://bali.idntimes.com/') !== FALSE){
                            $p->kanal_id = 227;
                        }else if(stripos($t->link_artikel, 'https://sumsel.idntimes.com/') !== FALSE){
                            $p->kanal_id = 228;
                        }else if(stripos($t->link_artikel, 'https://jateng.idntimes.com/') !== FALSE){
                            $p->kanal_id = 229;
                        }else if(stripos($t->link_artikel, 'https://sumut.idntimes.com/') !== FALSE){
                            $p->kanal_id = 230;
                        }else if(stripos($t->link_artikel, 'https://lampung.idntimes.com/') !== FALSE){
                            $p->kanal_id = 231;
                        }else if(stripos($t->link_artikel, 'https://www.idntimes.com/quiz') !== FALSE){
                            $p->kanal_id = 232;
                        }else if(stripos($t->link_artikel, 'https://www.idntimes.com/science') !== FALSE){
                            $p->kanal_id = 233;
                        }else if(stripos($t->link_artikel, 'https://www.idntimes.com/food') !== FALSE){
                            $p->kanal_id = 234;
                        }else if(stripos($t->link_artikel, 'https://www.idntimes.com/fiction') !== FALSE){
                            $p->kanal_id = 235;
                        }else if(stripos($t->link_artikel, 'https://www.idntimes.com/automotive') !== FALSE){
                            $p->kanal_id = 236;
                        }else if(stripos($t->link_artikel, 'https://www.idntimes.com/men') !== FALSE){
                            $p->kanal_id = 237;
                        }else if(stripos($t->link_artikel, 'https://www.idntimes.com/opinion') !== FALSE){
                            $p->kanal_id = 238;
                        }else{
                            $p->kanal_id = 0;
                        }

                        $p->portal_id = 9;
                        $p->kategori_id = $param->kategori_id;
                        $p->subkategori_id = $param->subkategori_id;
                        $p->save();
                    }
                }
            }else{
                if(stripos($cek->link_artikel, 'https://www.idntimes.com/travel') !== FALSE){
                    $kanal_id = 210;
                }else if(stripos($cek->link_artikel, 'https://www.idntimes.com/health') !== FALSE){
                    $kanal_id = 209;
                }else if(stripos($cek->link_artikel, 'https://www.idntimes.com/life') !== FALSE){
                    $kanal_id = 208;
                }else if(stripos($cek->link_artikel, 'https://www.idntimes.com/hype') !== FALSE){
                    $kanal_id = 207;
                }else if(stripos($cek->link_artikel, 'https://www.idntimes.com/tech') !== FALSE){
                    $kanal_id = 206;
                }else if(stripos($cek->link_artikel, 'https://www.idntimes.com/sport') !== FALSE){
                    $kanal_id = 205;
                }else if(stripos($cek->link_artikel, 'https://www.idntimes.com/business') !== FALSE){
                    $kanal_id = 204;
                }else if(stripos($cek->link_artikel, 'https://www.idntimes.com/news') !== FALSE){
                        $kanal_id = 203;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/trending') !== FALSE){
                    $kanal_id = 218;
                }else if(stripos($t->link_artikel, 'https://jogja.idntimes.com/') !== FALSE){
                    $kanal_id = 220;
                }else if(stripos($t->link_artikel, 'https://sulsel.idntimes.com/') !== FALSE){
                    $kanal_id = 221;
                }else if(stripos($t->link_artikel, 'https://ntb.idntimes.com/') !== FALSE){
                    $kanal_id = 222;
                }else if(stripos($t->link_artikel, 'https://jabar.idntimes.com/') !== FALSE){
                    $kanal_id = 223;
                }else if(stripos($t->link_artikel, 'https://jatim.idntimes.com/') !== FALSE){
                    $kanal_id = 224;
                }else if(stripos($t->link_artikel, 'https://kaltim.idntimes.com/') !== FALSE){
                    $kanal_id = 225;
                }else if(stripos($t->link_artikel, 'https://banten.idntimes.com/') !== FALSE){
                    $kanal_id = 226;
                }else if(stripos($t->link_artikel, 'https://bali.idntimes.com/') !== FALSE){
                    $kanal_id = 227;
                }else if(stripos($t->link_artikel, 'https://sumsel.idntimes.com/') !== FALSE){
                    $kanal_id = 228;
                }else if(stripos($t->link_artikel, 'https://jateng.idntimes.com/') !== FALSE){
                    $kanal_id = 229;
                }else if(stripos($t->link_artikel, 'https://sumut.idntimes.com/') !== FALSE){
                    $kanal_id = 230;
                }else if(stripos($t->link_artikel, 'https://lampung.idntimes.com/') !== FALSE){
                    $kanal_id = 231;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/quiz') !== FALSE){
                    $kanal_id = 232;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/science') !== FALSE){
                    $kanal_id = 233;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/food') !== FALSE){
                    $kanal_id = 234;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/fiction') !== FALSE){
                    $kanal_id = 235;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/automotive') !== FALSE){
                    $kanal_id = 236;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/men') !== FALSE){
                    $kanal_id = 237;
                }else if(stripos($t->link_artikel, 'https://www.idntimes.com/opinion') !== FALSE){
                    $kanal_id = 238;
                }else{
                    $kanal_id = 0;
                }

                $cek_kanal_parameter = \App\Models\Scrap\Kanalparameter::where('parameter_id', $cek->id)
                    ->where('kanal_id', $kanal_id)
                    ->count();
                
                if($cek_kanal_parameter == 0)
                {
                    $p = new \App\Models\Scrap\Kanalparameter;
                    $p->parameter_id = $cek->id;
                    $p->kanal_id = $kanal_id;
                    $p->portal_id = 9;
                    $p->kategori_id = $param->kategori_id;
                    $p->subkategori_id = $param->subkategori_id;
                    $p->save();

                    
                }
            }

            $this->info('Update == '.$t->id);
            
            \App\Models\Scrap\Parameter_dummy::where('id', $t->id)
                    ->update(
                        [
                            'dibaca'=>'Y'
                        ]
                    );
        }

        $this->info('Selesai');
    }
}
