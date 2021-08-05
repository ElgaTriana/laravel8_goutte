<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateTampilBrand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmvtampil:brand';

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
     * @return mixed
     */
    public function handle()
    {
        $this->info("Please Wait....");

        $sql=\App\Models\Dashboard\Cmv\Variabel::where('totals_thousand','<',15)
            ->where('quartal',4)
            ->where('tahun',2020)
            ->where('subdemo_id', "DD0")
            ->WhereNull('type')
            ->select('brand_id')
            ->distinct('brand_id')
            ->get();

        $bar=$this->output->createProgressBar(count($sql));

        $ra=array();

        foreach($sql as $row){
            $this->info("Update brand - ".$row->brand_id);

            $ra[]=$row->brand_id;
                
            $bar->advance();
        }

        \DB::transaction(function() use($ra){
            \App\Models\Dashboard\Cmv\Brand::whereIn('brand_id',$ra)
                ->update(
                    [
                        'tampil'=>'N'
                    ]
                );
        });

        $this->info("Tampil brand berdasarkan Total Thousand Berhasil di setting");

        $sql2=\App\Models\Dashboard\Cmv\Brand::Where('brand_name','like','%OTHERS%')
        ->OrWhere(\DB::raw("RIGHT(brand_name, 4)"),'=','KNOW')
        ->OrWhere(\DB::raw("LEFT(brand_name, 4)"),'=','NONE')
        ->get();

        $bar2=$this->output->createProgressBar(count($sql2));

        $ra2=array();

        foreach($sql2 as $row2){
            $this->info("Update brand Tidak Di Masukan - ".$row2->brand_name);

            $ra2[]=$row2->brand_id;
                
            $bar2->advance();
        }

        \DB::transaction(function() use($ra2){
            \App\Models\Dashboard\Cmv\Brand::whereIn('brand_id',$ra2)
                ->update(
                    [
                        'tampil'=>'N'
                    ]
                );
        });

        $bar->finish();

        $this->info("Tampil brand Berdasarkan Nama Brand berhasil di setting");
    }
}
